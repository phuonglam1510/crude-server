<?php

namespace App\Http\Controllers\API\Bussiness;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\Bussiness\TransactionService;
use App\Services\Bussiness\TransactionSuccessService;
use App\Services\Bussiness\CancelBillService;
use App\Services\Bussiness\CouponsService;
use App\Service\Customer\CustomerService;
use App\Services\Customer\CustomerService as CustomerCustomerService;
use Illuminate\Http\Request;
use App\Imports\ImportXLS;
use App\Imports\ImportReceipt;
use Maatwebsite\Excel\Excel;
class TransactionController extends Controller
{
    use CustomRequest;
    private $authService;
    private $transactionService;
    private $transactionSuccessService;
    private $userService;
    private $customerService;
    private $houseService;
    private $cancelBillService;
    private $couponsService;
    private $excel;
    public function __construct(
        AuthService $authService,
        UserService $userService,
        HouseService $houseService,
        TransactionService $transactionService,
        CustomerCustomerService $customerService,
        TransactionSuccessService $transactionSuccessService,
        CancelBillService $cancelBillService,
        CouponsService $couponsService,
        Excel $excel
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->houseService = $houseService;
        $this->transactionService = $transactionService;
        $this->transactionSuccessService = $transactionSuccessService;
        $this->customerService = $customerService;
        $this->couponsService = $couponsService;
        $this->cancelBillService = $cancelBillService;
        $this->excel = $excel;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

    public function addTransaction(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $house_id = $data['house_id'];
        $data['createBy'] = $user->id;
        $data['date'] = date_create(
            $data['date'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['brokerageReceiveDate'] = date_create(
            $data['brokerageReceiveDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['notarizedDate'] = date_create(
            $data['notarizedDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['reservationDate'] = date_create(
            $data['reservationDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        if (!isset($data['brokerageFeeReceived'])) {
            $data['brokerageFeeReceived'] = 0;
        }
        $data['brokerageFeeReceivable'] =
            $data['brokerageFee'] - $data['brokerageFeeReceived'];
        //$status = $this->houseService->getHouseDetail($data['house_id']);
        //purpose 0 = bán, 1 = thuê
        //status:
        //1.Giữ chổ
        //2Đặt cọc
        //3.Công chứng hợp đồng mua bán
        //4.Đăng bộ sang tên
        // $this->success($data);
        foreach ($data['users_group'] as $key => $value) {
            $data['staff_id'] = $value['user_id'];
            $data['customer_id'] = $value['customer_id'];
            $data['transaction_type'] = (int) $value['transactionType'];
            
            $data['transactionCode'] = $value['transactionCode'];
            $data['brokerage_rate'] = (int) $value['brokerageRate'];
            $data['status'] = $value['status'];
            $data['brokerageFeeUser'] = $value['brokerageFeeUser'];
            if (!isset($value['note'])) {
                $data['note'] = null;
            } else {
                $data['note'] = $value['note'];
            }
            $transaction = $this->transactionService->createTransaction($data);
            if (!$transaction) {
                $this->error(config('API.Message.ServerError'));
            }
        }
        
        $this->success($transaction, null, 201);
    }

    public function getTransaction(Request $request)
    {
        // $data = $this->data($request);
        $this->getUser($user);
        $data = $request->query();
        $userId = [$user->id];
        $job_position = $user->job_position;
        if (
            ($user->role == 1 || $job_position == 8) &&
            $data['staff'][0] == 0
        ) {
            $transaction = $this->transactionService->getAllTransaction();
            foreach ($transaction as $key => $value) {
                $value['brokerageFeeReceivable'] =
                    $value['brokerageFeeUser'] - $value['brokerageFeeReceived'];
                $bill = $this->cancelBillService->getCancelBillByTransactionId($value['id']);
                $coupons = $this->couponsService->getCouponsByTransactionId($value['id']);
                $value['bill'] = $bill ? $bill->id : null;
                $value['coupons'] = $coupons ? $coupons->reducedFee : null;
                if($bill) {
                    $value['brokerageFeeReceivable'] = 0;
                } else {
                    $value['brokerageFeeReceivable'] = $coupons ? $value['brokerageFeeUser'] - $value['brokerageFeeReceived'] - $coupons->reducedFee : $value['brokerageFeeUser'] - $value['brokerageFeeReceived'];
                }
                $lstUserGroup = [];
                unset($value['users_group']);
            }
            $this->success($transaction, null, 201);
        }
        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $key => $value) {
                if (intval($value) == 0) {
                    $data['staff'][$key] = $user->id;
                }
            }
            $userId = $data['staff'];
        }
        if (isset($data['team']) && $data['team'] == 'true') {
            $staffList = $this->userService->getAllUserByManagerId(
                $userId,
                null
            );
            foreach ($staffList as $staff) {
                array_push($userId, $staff['id']);
            }
        }

        if ($data && $data['startAt']) {
            $start_at = $data['startAt'];
            $end_at = $data['endAt'];
            $transaction = $this->transactionService->transactionRepo->getMyseflTransactionSuccess(
                $userId,
                $start_at,
                $end_at
            );
        } else {
            $transaction = $this->transactionService->transactionRepo->getMyseflTransactionSuccess(
                $userId
            );
        }
        foreach ($transaction as $key => $value) {
            $value['brokerageFeeReceivable'] =
                $value['brokerageFee'] - $value['brokerageFeeReceived'];

            $lstUserGroup = [];
            unset($value['users_group']);
            // if ($value['users_group']) {
            //     foreach ($value['users_group'] as $index => $user) {
            //         // $response[$key]['users_group'][$index] = [];
            //         $userInfo = $this->userService->userRepo->model->where('id', $user['user_id'])->select('id', 'name')->first();
            //         $customerInfo = $this->customerService->customerRepo->model()->where('id', $user['customer_id'])->select('id', 'name')->first();
            //         $user['name'] = $userInfo->name ?? '';
            //         $user['customer_name'] = $customerInfo->name ?? '';
            //         $lstUserGroup[] = $user;
            //         //$response[$key]->users_group[$index]['name'] = $name->name;
            //         //dd($response[$key]->users_group[$index]['name']);
            //         //$value['users_group'][$key]['name'] = $name->name;

            //     }
            //     if (!empty($lstUserGroup)) {
            //         $response[$key]['users_group'] = $lstUserGroup;
            //     }
            // }
        }
        $this->success($transaction, null, 201);
    }

    public function getTransactionByConditions(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);
        $startAt = isset($data['startAt']) ? strtotime($data['startAt']) : null;
        $endAt = isset($data['startAt']) ? strtotime($data['endAt']) : null;
        $staffId = $data['user'] ?? [];
        $customerID = $data['customer'] ?? [];
        $userId = $user->id;
        $response = $this->transactionService->getTransactionByCondition(
            $userId,
            $startAt,
            $endAt,
            $staffId,
            $customerID
        );

        foreach ($response as $key => $value) {
            $value['brokerageFeeReceivable'] =
                $value['brokerageFee'] - $value['brokerageFeeReceived'];
            $lstUserGroup = [];
            unset($value['users_group']);
            // if ($value['users_group']) {
            //     foreach ($value['users_group'] as $index => $user) {
            //         // $response[$key]['users_group'][$index] = [];
            //         $userInfo = $this->userService->userRepo->model->where('id', $user['user_id'])->select('id', 'name')->first();
            //         $customerInfo = $this->customerService->customerRepo->model()->where('id', $user['customer_id'])->select('id', 'name')->first();
            //         $user['name'] = $userInfo->name ?? '';
            //         $user['customer_name'] = $customerInfo->name ?? '';
            //         $lstUserGroup[] = $user;
            //         //$response[$key]->users_group[$index]['name'] = $name->name;
            //         //dd($response[$key]->users_group[$index]['name']);
            //         //$value['users_group'][$key]['name'] = $name->name;

            //     }
            //     if (!empty($lstUserGroup)) {
            //         $response[$key]['users_group'] = $lstUserGroup;
            //     }
            // }
        }
        $this->success($response, null, 201);
    }

    /**
     * Update receipt
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateTransaction(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $transaction = $this->transactionService->getTransactionById(
            $data['id']
        );
        // $data['user_id'] = $user->id;
        $data['date'] = date_create(
            $data['date'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['brokerageReceiveDate'] = date_create(
            $data['brokerageReceiveDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['notarizedDate'] = date_create(
            $data['notarizedDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        $data['reservationDate'] = date_create(
            $data['reservationDate'],
            timezone_open('Asia/Ho_Chi_Minh')
        );
        if (!isset($data['brokerageFeeReceived'])) {
            $data['brokerageFeeReceived'] = $transaction->brokerageFeeReceived;
        }

        // $data['users_group'] = $data['users_group'];
        $data['brokerageFeeReceivable'] =
            $data['brokerageFee'] - $data['brokerageFeeReceived'];

        $data['staff_id'] = $data['staff_id'];
        $data['customer_id'] = $data['customer_id'];
        $data['transaction_type'] = $data['transactionType'];
        $data['transactionCode'] = $data['transactionCode'];
        //$status = $this->houseService->getHouseDetail($data['house_id']);
        //purpose 0 = bán, 1 = thuê
        //status:
        //1.Giữ chổ
        //2Đặt cọc
        //3.Công chứng hợp đồng mua bán
        //4.Đăng bộ sang tên
        $transaction = $this->transactionService->updateTransaction($data);
        if (!$transaction) {
            $this->error(config('API.Message.ServerError'));
        }
        $this->success($transaction, null, 201);
    }

    public function getTransactionByHouseId($houseId)
    {
        $response = $this->transactionService->getTransactionByHouseId(
            $houseId
        );
        $this->success($response, null, 201);
    }

    public function import(Request $request)
    {   
        $this->getUser($user);
        if ($user->role == 1 || $user->job_position == 8) {
            if($request->type == 'transaction') {
               $this->excel->import(new ImportXLS($this->excel), $request->file('file'), $request->type);
               $link = '';
               if(file_exists(public_path().'/storage/house.xlsx')) {
                    $link = asset('storage/house.xlsx');
               }
               $this->success(['link' => $link], null, 201);
            } else {
               $receipt = $this->excel->import(new ImportReceipt(), $request->file('file'), $request->type);
            }
        } else {
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
    
    }
}


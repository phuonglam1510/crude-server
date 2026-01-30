<?php

namespace App\Http\Controllers\API\Bussiness;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Bussiness\ReceiptService;
use App\Services\Bussiness\TransactionService;
use App\Services\Bussiness\CouponsService;
use App\Services\Bussiness\CancelBillService;
use App\Services\Customer\CustomerService;
use App\Services\House\HouseService;
use App\Services\User\AuthService;
use App\Services\User\UserService;

class CancelBillController extends Controller
{
    use CustomRequest;
    private $authService;
    private $receiptService;
    private $userService;
    private $customerService;
    private $transactionService;
    private $couponsService;
    private $cancelBillService;



    public function __construct(
        AuthService $authService,
        UserService $userService,
        HouseService $houseService,
        ReceiptService $receiptService,
        CustomerService $customerService,
        CouponsService $couponsService,
        TransactionService $transactionService,
        CancelBillService $cancelBillService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->houseService = $houseService;
        $this->receiptService = $receiptService;
        $this->customerService = $customerService;
        $this->transactionService = $transactionService;
        $this->couponsService = $couponsService;
        $this->cancelBillService = $cancelBillService;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

    public function addCancelBill(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if (!empty($data)) {
            $data['createBy'] = $user->id;
            // $findReceipt = $this->receiptService->getReceiptByTransactionId(
            //     $data['transaction_id']
            // );

            // if (count($findReceipt) == 0) {
            //     $uncollectedFees = $data['price'] - $data['priceReceived'];
            //     $dataUpdate = [
            //         'id' => $data['transaction_id'],
            //         'brokerageFeeReceived' => $data['priceReceived'],
            //     ];
            //     $this->transactionService->updateTransaction($dataUpdate);
            // } else {
            //     $uncollectedFees = $findReceipt[0]->feePayable;

            //     foreach ($findReceipt as $key => $value) {
            //         $uncollectedFees = $uncollectedFees - $value->feeCollect;
            //     }
            //     $uncollectedFees = $uncollectedFees - $data['priceReceived'];
            //     $transaction = $this->transactionService->getTransactionById(
            //         $data['transaction_id']
            //     );
            //     $dataUpdate = [
            //         'id' => $data['transaction_id'],
            //         'brokerageFeeReceived' =>
            //             $data['priceReceived'] +
            //             $transaction->brokerageFeeReceived,
            //     ];

            //     $this->transactionService->updateTransaction($dataUpdate);
            // }
            // $dataNeedInsert['uncollectedFees'] = $uncollectedFees;
            $find = $this->cancelBillService->getCancelBillByTransactionId($data['transaction_id']);
            if ($find) {
                $this->error(config('API.Message.Bussiness.Duplicate'));
            }

            $bill = $this->cancelBillService->createCancelBill($data);
            if (!$bill) {
                $this->error(config('API.Message.ServerError'));
            }
        }

        $this->success($bill, null, 201);
    }

    public function getCancelBill(Request $request)
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
            $coupons = $this->cancelBillService->getAllCancelBill(); 
             foreach($coupons as $key => $value) {
            if($value['staff_id'] && $value['customer_id']) {
                $userInfo = $this->userService->userRepo->model
                ->where('id', $value['staff_id'])
                ->select('name')
                ->first();

                $customerInfo = $this->customerService->customerRepo
                ->model()
                ->where('id', $value['customer_id'])
                ->select('name')
                ->first();

                $value['customer_name'] = $customerInfo->name;
                $value['staff_name'] = $userInfo->name;
            }
            $transaction = $this->transactionService->getTransactionById($value['transaction_id']);
           $value['feesPayable'] = $transaction->brokerageFeeUser;
        }
        $this->success($coupons, null, 200);          
        } else {
            $coupons = $this->cancelBillService->getCancelBillByUserId($user->id);           
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
            $coupons = $this->cancelBillService->cancelBillRepo->getCancelBillWithCondition(
                $userId,
                $start_at,
                $end_at
            );

        } else {
            $coupons = $this->cancelBillService->cancelBillRepo->getCancelBillWithCondition(
                $userId
            );
        }
        foreach($coupons as $key => $value) {
            if($value['staff_id'] && $value['customer_id']) {
                $userInfo = $this->userService->userRepo->model
                ->where('id', $value['staff_id'])
                ->select('name')
                ->first();

                $customerInfo = $this->customerService->customerRepo
                ->model()
                ->where('id', $value['customer_id'])
                ->select('name')
                ->first();

                $value['customer_name'] = $customerInfo->name;
                $value['staff_name'] = $userInfo->name;
            }
           $transaction = $this->transactionService->getTransactionById($value['transaction_id']);
           $value['feeCollected'] = $transaction->brokerageFeeReceived;
        }
        $this->success($coupons, null, 200);
    }

    /**
     * Update receipt
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateCancelBill(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if (!$data['id']) {
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
        // if($userId != $data['user_id']){
        //     $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        // }
        if ($user->role != 1 && $user->role != 2 && $user->job_position != 8) {
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
        unset($data['house_id']);
        unset($data['customer_id']);
        unset($data['staff_id']);
       $billUpdate =  $this->cancelBillService->updateBill($data);

        // $findReceipt = $this->receiptService->getReceiptByTransactionId(
        //     $data['transaction_id']
        // );
        // if (count($findReceipt) == 1) {
        //     $dataNeedInsert['uncollectedFees'] =
        //         $data['price'] - $data['priceReceived'];
        //     $dataUpdate = [
        //         'id' => $data['transaction_id'],
        //         'brokerageFeeReceived' => $data['priceReceived'],
        //     ];
        //     $this->receiptService->updateReceipt($dataNeedInsert);
        //     $this->transactionService->updateTransaction($dataUpdate);
        // } else {
        //     $uncollectedFees = $findReceipt[0]->feePayable;
        //     $id = [$data['id']];
        //     $result = $findReceipt->filter(function ($value, $key) use ($id) {
        //         return in_array($value->id, $id);
        //     });
        //     $index = key($result->toArray());
        //     $receiptOld;

        //     for ($i = $index; $i < count($findReceipt); $i++) {
        //         if ($i == 0) {
        //             $findReceipt[$i]->uncollectedFees =
        //                 $uncollectedFees - $data['priceReceived'];
        //         }
        //         if ($i == $index) {
        //             $receiptOld = $findReceipt[$index]->feeCollect;
        //             $findReceipt[$index]->feeCollect = $data['priceReceived'];
        //         }
        //         if ($i > 0) {
        //             $findReceipt[$i]->uncollectedFees =
        //                 $findReceipt[$i - 1]->uncollectedFees -
        //                 $findReceipt[$i]->feeCollect;
        //         }
        //         $dataNeedUpdate = $findReceipt[$i]->toArray();
        //         $this->receiptService->updateReceipt($dataNeedUpdate);
        //     }
        //     $transaction = $this->transactionService->getTransactionById(
        //         $data['transaction_id']
        //     );

        //     $dataUpdate = [
        //         'id' => $data['transaction_id'],
        //         'brokerageFeeReceived' =>
        //             $data['priceReceived'] +
        //             $transaction->brokerageFeeReceived -
        //             $receiptOld,
        //     ];

        //     $this->transactionService->updateTransaction($dataUpdate);
        // }
        // $receiptUpdated = $this->receiptService->getReceipt($data['id']);
        // $dataUpdate = [
        //     'id' => $data['transaction_id'],
        //     'brokerageFeeReceived' => $data['priceReceived'],
        // ];
        // $this->transactionService->updateTransaction($dataUpdate);
        // if (!$receiptUpdated) {
        //     $this->error(config('API.Message.Bussiness.SomethingWrong'));
        // }
        $this->success($billUpdate, null, 200);
    }

  
}


<?php

namespace App\Http\Controllers\API\Bussiness;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Bussiness\ReceiptService;
use App\Services\Bussiness\TransactionService;
use App\Services\Customer\CustomerService;
use App\Services\House\HouseService;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Models\Bussiness\Receipt;
class ReceiptController extends Controller
{
    use CustomRequest;
    private $authService;
    private $receiptService;
    private $userService;
    private $customerService;
    private $transactionService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        HouseService $houseService,
        ReceiptService $receiptService,
        CustomerService $customerService,
        TransactionService $transactionService
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
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

    public function addReceipt(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $recepit = [];
        if (!empty($data)) {
            $dataNeedInsert = [
                'dateCollect' => strtotime($data['basic_date']),
                'feeCode' => $data['basic_code'] ?? '',
                'feePayable' => $data['price'] ?? 0,
                'feeCollect' => $data['priceReceived'] ?? 0,
                'staff_id' => $data['staff_id'] ?? [],
                'customer_id' => $data['customer'],
                'house_id' => $data['address'],
                'user_id' => $data['user_id'],
                'transaction_id' => $data['transaction_id'],
                'note' => $data['note'] ?? null,
            ];
            $findReceipt = $this->receiptService->getReceiptByTransactionId(
                $data['transaction_id']
            );

            if (count($findReceipt) == 0) {
                $uncollectedFees = $data['price'] - $data['priceReceived'];
                $dataUpdate = [
                    'id' => $data['transaction_id'],
                    'brokerageFeeReceived' => $data['priceReceived'],
                ];
                $this->transactionService->updateTransaction($dataUpdate);
            } else {
                $uncollectedFees = $findReceipt[0]->feePayable;

                foreach ($findReceipt as $key => $value) {
                    $uncollectedFees = $uncollectedFees - $value->feeCollect;
                }
                $uncollectedFees = $uncollectedFees - $data['priceReceived'];
                $transaction = $this->transactionService->getTransactionById(
                    $data['transaction_id']
                );
                $dataUpdate = [
                    'id' => $data['transaction_id'],
                    'brokerageFeeReceived' =>
                        $data['priceReceived'] +
                        $transaction->brokerageFeeReceived,
                ];

                $this->transactionService->updateTransaction($dataUpdate);
            }
            $dataNeedInsert['uncollectedFees'] = $uncollectedFees;
            $receipt = $this->receiptService->createReceipt($dataNeedInsert);

            if (!$receipt) {
                $this->error(config('API.Message.ServerError'));
            }
        }

        $this->success($receipt, null, 201);
    }

    public function getReceipt(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);
        $job_position = $user->job_position;
        if ($user->role == 1 || $job_position == 8) {
            $response = $this->receiptService->getAllReceipt();
        } else {
            $response = $this->receiptService->getReceiptByUserId($user->id);
        }
        foreach ($response as $key => $value) {
            $lstUserGroup = [];
            $user_group = [];
            if (!empty($value['dateCollect'])) {
                $value['dateCollect'] = date('Y/m/d', $value['dateCollect']);
            }
            if ($value['staff_id']) {
                // foreach ($value['users_group'] as $index => $user) {
                // $response[$key]['users_group'][$index] = [];
                $userInfo = $this->userService->userRepo->model
                    ->where('id', $value['staff_id'])
                    ->select('id', 'name')
                    ->first();
                $customerInfo = $this->customerService->customerRepo
                    ->model()
                    ->where('id', $value['customer_id'])
                    ->select('id', 'name')
                    ->first();
                $user_group['name'] = $userInfo->name ?? '';
                $user_group['customer_name'] = $customerInfo->name ?? '';
                $lstUserGroup[] = $user_group;

                //$response[$key]->users_group[$index]['name'] = $name->name;
                //dd($response[$key]->users_group[$index]['name']);
                //$value['users_group'][$key]['name'] = $name->name;

                // }
                if (!empty($lstUserGroup)) {
                    $response[$key]['users_group'] = $lstUserGroup;
                }
            }
        }
        $this->success($response, null, 201);
    }

    public function getReceiptByConditions(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);
        $startAt = isset($data['startAt']) ? strtotime($data['startAt']) : null;
        $endAt = isset($data['startAt']) ? strtotime($data['endAt']) : null;
        $staffId = $data['user'] ?? [];
        $customerID = $data['customer'] ?? [];
        $userId = $user->id;
        $response = $this->receiptService->getReceiptByCondition(
            $userId,
            $startAt,
            $endAt,
            $staffId,
            $customerID
        );

        foreach ($response as $key => $value) {
            $lstUserGroup = [];
            if (!empty($value['dateCollect'])) {
                $value['dateCollect'] = date('Y/m/d', $value['dateCollect']);
            }
            if ($value['staff_id']) {
                // foreach ($value['users_group'] as $index => $user) {
                // $response[$key]['users_group'][$index] = [];
                $userInfo = $this->userService->userRepo->model
                    ->where('id', $value['staff_id'])
                    ->select('id', 'name')
                    ->first();
                $customerInfo = $this->customerService->customerRepo
                    ->model()
                    ->where('id', $value['customer_id'])
                    ->select('id', 'name')
                    ->first();
                $user['name'] = $userInfo->name ?? '';
                $user['customer_name'] = $customerInfo->name ?? '';
                $lstUserGroup[] = $user;
                //$response[$key]->users_group[$index]['name'] = $name->name;
                //dd($response[$key]->users_group[$index]['name']);
                //$value['users_group'][$key]['name'] = $name->name;

                // }
                if (!empty($lstUserGroup)) {
                    $response[$key]['users_group'] = $lstUserGroup;
                }
            }
        }
        $this->success($response, null, 201);
    }

    /**
     * Update receipt
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateReceipt(Request $request)
    {
        $this->getUser($user);
        $userId = $user->id;
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
        $receipt = $this->receiptService->getReceipt($data['id']);
        if (!$receipt) {
            $this->error(config('API.Message.Bussiness.NotFound'));
        }

        $dataNeedInsert = [
            'id' => $data['id'],
            'dateCollect' => strtotime($data['basic_date']),
            'feeCode' => $data['basic_code'] ?? '',
            'feePayable' => $data['price'] ?? 0,
            'feeCollect' => $data['priceReceived'] ?? 0,
            'staff_id' => $data['staff_id'] ?? $receipt->staff_id,
            'customer_id' => $data['customer'],
            'house_id' => $data['address'],
            'user_id' => $data['user_id'],
            'note' => $data['note'],
        ];

        $findReceipt = $this->receiptService->getReceiptByTransactionId(
            $data['transaction_id']
        );
        if (count($findReceipt) == 1) {
            $dataNeedInsert['uncollectedFees'] =
                $data['price'] - $data['priceReceived'];
            // $uncollectedFees = $data['price'] - $data['priceReceived'];
            $dataUpdate = [
                'id' => $data['transaction_id'],
                'brokerageFeeReceived' => $data['priceReceived'],
            ];
            $this->receiptService->updateReceipt($dataNeedInsert);
            $this->transactionService->updateTransaction($dataUpdate);
        } else {
            $uncollectedFees = $findReceipt[0]->feePayable;
            $id = [$data['id']];
            $result = $findReceipt->filter(function ($value, $key) use ($id) {
                return in_array($value->id, $id);
            });
            $index = key($result->toArray());
            $receiptOld;

            for ($i = $index; $i < count($findReceipt); $i++) {
                if ($i == 0) {
                    $findReceipt[$i]->uncollectedFees =
                        $uncollectedFees - $data['priceReceived'];
                }
                if ($i == $index) {
                    $receiptOld = $findReceipt[$index]->feeCollect;
                    $findReceipt[$index]->feeCollect = $data['priceReceived'];
                }
                if ($i > 0) {
                    $findReceipt[$i]->uncollectedFees =
                        $findReceipt[$i - 1]->uncollectedFees -
                        $findReceipt[$i]->feeCollect;
                }
                $dataNeedUpdate = $findReceipt[$i]->toArray();
                $this->receiptService->updateReceipt($dataNeedUpdate);
            }
            $transaction = $this->transactionService->getTransactionById(
                $data['transaction_id']
            );

            $dataUpdate = [
                'id' => $data['transaction_id'],
                'brokerageFeeReceived' =>
                    $data['priceReceived'] +
                    $transaction->brokerageFeeReceived -
                    $receiptOld,
            ];

            $this->transactionService->updateTransaction($dataUpdate);
        }
        $receiptUpdated = $this->receiptService->getReceipt($data['id']);
        // $dataUpdate = [
        //     'id' => $data['transaction_id'],
        //     'brokerageFeeReceived' => $data['priceReceived'],
        // ];
        // $this->transactionService->updateTransaction($dataUpdate);
        if (!$receiptUpdated) {
            $this->error(config('API.Message.Bussiness.SomethingWrong'));
        }
        $this->success($receiptUpdated, null, 200);
    }
}


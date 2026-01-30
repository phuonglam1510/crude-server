<?php

namespace App\Http\Controllers\API\Bussiness;

use App\Http\Controllers\Controller;
use App\Http\Models\Bussiness\TransactionSuccess;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\TrackLocationRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\Bussiness\TransactionSuccessService;
use App\Services\House\HouseService;
use Illuminate\Http\Request;

class TransactionSuccessController extends Controller
{
    use CustomRequest;
    private $authService;
    private $transactionSuccessService;
    private $userService;

    public function __construct(AuthService $authService, UserService $userService, TransactionSuccessService $transactionSuccessService, HouseService $houseService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->transactionSuccessService = $transactionSuccessService;
        $this->houseService = $houseService;
    }


    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getAllTransactionSuccessByUserId(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = array($user->id);
        $job_position = $user->job_position;
        if(!empty($data['staff'])){
             foreach ($data['staff'] as $key => $value){
                 if(intval($value)==0){
                     $data['staff'][$key] = $user->id;
                 }
             }
            $userId =$data['staff'];
            
        }
        // $this->error($job_position);
        if(isset($data['team'])&&$data['team']=='true'){

            $staffList = $this->userService->getAllUserByManagerId($userId,null);
            foreach ($staffList as $staff){
                array_push($userId,$staff['id']);
            };
        }
        if ($data && $data['startAt']) {
            $start_at = $data['startAt'];
            $end_at = $data['endAt'];

            $transactionSuccess = $this->transactionSuccessService->transactionSuccessRepo->getMyseflTransactionSuccess($userId, $start_at, $end_at);
        } else {

            $transactionSuccess = $this->transactionSuccessService->transactionSuccessRepo->getMyseflTransactionSuccess($userId);

        }
         $this->success($transactionSuccess);

    }

    public function addTransactionSuccess(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        $data['date'] = date_create($data['date'], timezone_open('Asia/Ho_Chi_Minh'));
        $data['brokerageReceiveDay'] = date_create($data['brokerageReceiveDay'], timezone_open('Asia/Ho_Chi_Minh'));
        $data['notarizedDay'] = date_create($data['notarizedDay'], timezone_open('Asia/Ho_Chi_Minh'));
        if(!isset( $data['brokerageFeeReceived'])){
             $data['brokerageFeeReceived'] = 0;
        }
	$data['brokerageFeeReceivable'] = $data['brokerageFee'] - $data['brokerageFeeReceived'];
        //$status = $this->houseService->getHouseDetail($data['house_id']);
        //purpose 0 = bán, 1 = thuê
        //status:
        //1.Giữ chổ
        //2Đặt cọc
        //3.Công chứng hợp đồng mua bán
        //4.Đăng bộ sang tên
        $transactionSuccess = $this->transactionSuccessService->createTransactionSuccess($data);
        // if ($transactionSuccess == config('API.Message.TransactionSuccess.AddressNoAccess')) {
        //     $this->error(config('API.Message.TransactionSuccess.AddressNoAccess'));
        // }

        // if ($transactionSuccess == config('API.Message.TransactionSuccess.NotExisted')) {
        //     $this->error(config('API.Message.TransactionSuccess.NotExisted'));
        // }
        // if ($transactionSuccess == config('API.Message.TransactionSuccess.Selling')) {
        //     $this->error(config('API.Message.TransactionSuccess.Selling'));
        // }
        if (!$transactionSuccess) {
            $this->error(config('API.Message.ServerError'));
        }
        $this->success($transactionSuccess, null, 201);
    }

    public function updateTransactionSuccess(Request $request){
        $this->getUser($user);
        $userId = $user->id;
        $data = $this->data($request);
        if(!$data['id']){
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
        if($userId != $data['user_id']){
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
        $transaction = $this->transactionSuccessService->getTransactionSuccess($data['id'],$userId);
        if(!$transaction){
            $this->error(config('API.Message.Bussiness.NotFound'));
        }
        $transactionUpdated = $this->transactionSuccessService->updateTransactionSuccess($data);
        if(!$transactionUpdated){
            $this->error(config('API.Message.Bussiness.SomethingWrong'));
        }
        $this->success($transactionUpdated, null, 200);
    }



    // /**
    //  * @param UserRequest $request
    //  * @throws \App\Exceptions\JsonResponse
    //  */
    // public function updateUserProfile(UserRequest $request)
    // {
    //     $this->getUser($user);
    //     $data = $this->data($request);
    //     $userId = $user->id;

    //     if (isset($data['user_id'])) {
    //         if ($data['user_id'] != $user->id && $user->role != config('API.Constant.Role.SuperAdmin') &&  $user->role != config('API.Constant.Role.Admin')) {
    //             $this->error(config('API.Message.NotEnoughPermission'));
    //         }
    //         $userId = $data['user_id'];
    //     }

    //     if (isset($data['phone_number']) && !$this->userService->checkDuplicate($userId, 'phone_number', $data['phone_number'])) {
    //         $this->error(config('API.Message.User.PhoneNumerTaken'));
    //     }

    //     $user = $this->userService->updateUser($userId, $data);
    //     $this->success($user);
    // }

    // public function updateAvatar(UserRequest $request)
    // {
    //     $this->getUser($user);
    //     $data = $this->data($request);
    //     $userId = $user->id;

    //     $user = $this->userService->updateUser($userId, $data);
    //     $this->success($user);
    // }


    // /**
    //  * Remove user
    //  * @param UserRequest $request
    //  * @throws \App\Exceptions\JsonResponse
    //  */
    // public function removeUserProfile(UserRequest $request)
    // {
    //     $this->getUser($user);
    //     $data = $this->data($request);

    //     $user = $this->userService->removeUser($data['user_id'], $user);

    //     if (!$user) {
    //         $this->error(config('API.Message.User.ForbiddenRemove'), 403);
    //     }

    //     $this->success($user);
    // }

    // public function adminResetPassword(UserRequest $request)
    // {
    //     $this->getUser($user);
    //     $data = $this->data($request);

    //     $user = $this->authService->adminResetPassword($data, $user);

    //     if (!$user) {
    //         $this->error(config('API.Message.User.ForbiddenReset'), 403);
    //     }

    //     $this->success($user);
    // }

    // /**
    //  * Send reset code & check code & change password
    //  * @param PasswordRequest $request
    //  * @param $type
    //  * @throws \App\Exceptions\JsonResponse
    //  */
    // public function changePassword(PasswordRequest $request, $type)
    // {
    //     $data = $this->data($request);
    //     $method = $type . 'Password';
    //     $user = $this->authService->$method($data);

    //     if ($user) {
    //         $this->success($user);
    //     } else {
    //         $this->error(config('API.Message.ServerError'));
    //     }
    // }

    // public function getTrackList(Request $request)
    // {
    //     $this->getUser($user);

    //     if ($user->role != config('API.Constant.Role.SuperAdmin')) {
    //         $this->error(config('API.Message.Forbidden'));
    //     }

    //     $locations = $this->userService->getAllLocationList($user->id);

    //     $this->success($locations);
    // }

    // public function getLocationByUser($userId)
    // {
    //     $this->getUser($user);

    //     if ($user->role != config('API.Constant.Role.SuperAdmin')) {
    //         $this->error(config('API.Message.Forbidden'));
    //     }

    //     $locations = $this->userService->getLocationByUser($userId);

    //     $this->success($locations);
    // }

    // public function getLastLocation(Request $request)
    // {
    //     $this->getUser($user);

    //     $lastLocation = $this->userService->getUserLastLocation($user->id);

    //     $this->success($lastLocation);
    // }

    // public function trackLocation(TrackLocationRequest $request)
    // {
    //     $this->getUser($user);
    //     $data = $this->data($request);
    //     $data['user_id'] = $user->id;
    //     $data['ip_address'] = $request->getClientIp();

    //     $track = $this->userService->createTrackLocation($data);

    //     if (!$track) {
    //         $this->error(config('API.Message.ServerError'));
    //     }

    //     $this->success($track);
    // }
}

<?php

namespace App\Http\Controllers\API\Bussiness;

use App\Http\Controllers\Controller;
use App\Http\Models\Bussiness\Accumulation;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\TrackLocationRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\Bussiness\AccumulationService;
use Illuminate\Http\Request;

class AccumulationController extends Controller
{
    use CustomRequest;
    private $authService;
    private $accumulationService;
    private $userService;

    public function __construct(AuthService $authService, UserService $userService, AccumulationService $accumulationService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->accumulationService = $accumulationService;
    }


    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getAllAccumulationByUserId(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        $accumulation = $this->accumulationService->accumulationRepo->getMyseflAccumulation($userId);


        $this->success($accumulation);
    }

    public function getAllAccumulationByCondition(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $job_position = $user->job_position;
        $paginate = $request->paginate;
        $userId = array($user->id);
        if(!empty($data['staff'])){
            foreach ($data['staff'] as $key => $value){
                if(intval($value)=='0'){
                    $data['staff'][$key] = $user->id;

                }
            }
            $userId =$data['staff'];
        }

        if(isset($data['team'])&&$data['team']=='true'){
            $staffList = $this->userService->getAllUserByManagerId($userId,null);
            foreach ($staffList as $staff){
                array_push($userId,$staff['id']);
            };
            //$this->error($typeGet);
        }
        if ($data && $data['startAt']) {
            $start_at = $data['startAt'];
            $end_at = $data['endAt'];

            $accumulation = $this->accumulationService->accumulationRepo->getAllAccumulationByCondition($userId, $start_at, $end_at,$paginate);
        } else {
            $accumulation = $this->accumulationService->accumulationRepo->getAllAccumulationByCondition($userId, $paginate);
        }
        $this->success($accumulation);

    }

    public function addAccumulation(Request $request)
    {

        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        $data['date']= explode('T',$data['date'])[0];
        $data['date'] = date_create($data['date'], timezone_open('Asia/Ho_Chi_Minh'));

        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        $data['status'] = 1;
        $checkExisted = $this->accumulationService->accumulationRepo->getMyseflAccumulation($userId,$data['date']);
        if(count($checkExisted)>0){
            $this->error(config('API.Message.Bussiness.Duplicate'));
        }
        $accumulation = $this->accumulationService->createAccumulation($data);

        if (!$accumulation) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($accumulation, null, 201);
    }

    public function updateAccumulation(Request $request){
        $this->getUser($user);
        $userId = $user->id;
        $data = $this->data($request);
        if($userId != $data['user_id']){
            $this->error(config('API.Message.Bussiness.ForbiddenUpdate'));
        }
        $accumulation = $this->accumulationService->getAccumulation($userId,$data['date']);
        //dd(count($accumulation));
        if(count($accumulation) == 1 && $data['id']!=$accumulation[0]['id']){
            $this->error(config('API.Message.Bussiness.DuplicateUpdate'));
        }
        if(!$accumulation){
            $this->error(config('API.Message.Bussiness.NotFound'));
        }
        $accumulationUpdate = $this->accumulationService->updateAccumulation($data);
        if(!$accumulationUpdate){
            $this->error(config('API.Message.Bussiness.SomethingWrong'));
        }
       $this->success($accumulationUpdate, null, 200);
    }


    /**
     * @param UserRequest $request
     * @throws \App\Exceptions\JsonResponse
     */


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


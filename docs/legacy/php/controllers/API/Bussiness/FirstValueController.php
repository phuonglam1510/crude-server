<?php

namespace App\Http\Controllers\API\Bussiness;

use App\Http\Controllers\Controller;
use App\Http\Models\Bussiness\FirstValue;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\TrackLocationRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\Bussiness\FirstValueService;
use Illuminate\Http\Request;

class FirstValueController extends Controller
{
    use CustomRequest;
    private $authService;
    private $firstValueService;
    private $userService;

    public function __construct(AuthService $authService, UserService $userService, FirstValueService $firstValueService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->firstValueService = $firstValueService;

    }


    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getAllFirstValue(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        $firstValue = $this->firstValueService->firstValueRepo->getFirstValue($userId);
        $this->success($firstValue);
    }


    public function addFirstValue(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        $firstValue = $this->firstValueService->createFirstValue($data);

        if (!$firstValue) {
            $this->error(config('API.Message.ServerError'));
        }



        $this->success($firstValue, null, 201);
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


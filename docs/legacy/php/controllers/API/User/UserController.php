<?php

namespace App\Http\Controllers\API\User;


use App\Http\Controllers\Controller;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\TrackLocationRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\User\PostAddressService;
use App\Services\User\PostManagerService;
use App\Services\User\PostAddressStatusService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $houseService;
    private $postManagerService;
    private $postAddressStatusService;
    private $postAddressService;
    public function __construct(
        AuthService $authService,
        UserService $userService,
        PostManagerService $postManagerService,
        PostAddressStatusService $postAddressStatusService,
        PostAddressService $postAddressService,
        HouseService $houseService
    ) {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->postManagerService = $postManagerService;
        $this->postAddressStatusService = $postAddressStatusService;
        $this->postAddressService = $postAddressService;
        $this->houseService = $houseService;
    }

    /**
     * Sign in with social or email credentials
     * @param SignInRequest $request
     * @param $type
     * @throws \App\Exceptions\JsonResponse
     */
    public function signin(SignInRequest $request, $type)
    {
        $input = $this->data($request);
        //Check for sign in method
        $method = 'signInWith' . ucfirst($request->type);
        $input['user_ip'] = $request->ip();

        $user = $this->authService->$method($input, $type);
        if ($user) {
            $this->success($user);
        }

        if ($type == 'email') {
            $this->error(config('API.Message.Auth.WrongCredentials'));
        }

        $this->error(config('API.Message.Auth.WrongCredentials'));
    }

    /**
     * Get user by role
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getUserList(Request $request)
    {

        $this->getUser($user);
        $data = $this->data($request);

        // if ($user->role == config('API.Constant.Role.Staff') || $user->role == config('API.Constant.Role.User')) {
        //     $this->error(config('API.Message.NotEnoughPermission'));
        // }
        $users = $this->userService->getAllUser($user, $data);
        $this->success($users);
    }

    /**
     * Get user statistic
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getUserStatistic(Request $request)
    {

        $this->getUser($user);

        if ($user->role != config('API.Constant.Role.SuperAdmin') && $user->role != config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }

        $data = $this->userService->getUserStatistic();
        $this->success($data);
    }

    public function getUserListById(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $job_position = $user->job_position;
        $userId = array($user->id);
        //dd($job_position);
        if ($data['team'] == true) {
            if (is_array($data['staff']) && $data['staff'][0] == 0) {
                $users = $this->userService->getAllUserByManagerId($userId, $job_position);
                // dd($job_position);
                $this->success($users);
            } else {
                $users = $this->userService->getAllUserByManagerId($data['staff'][0], null);
                $this->success($users);
            }
        } else {
            if (is_array($data['staff'])) {
                foreach ($data['staff'] as $key => $value) {
                    if (intval($value) == 0) {
                        $data['staff'][$key] = $user->id;
                    }
                }
                //dd($data['staff']);
                $users =  $this->userService->userRepo->model->whereIn('id', $data['staff'])->get();
                $this->success($users);
            } else {
                $users = $this->userService->userRepo->model->where('id', $data['staff'])->get();
                $this->success($users);
            }
        }
    }
    public function getIntroductionUserList(Request $request)
    {
        $users = $this->userService->getAllIntroductionUser();
        $this->success($users);
    }

    public function getUserListByManagerId(Request $request)
    {
        $this->getUser($user);
        $job_position = $user->job_position;
        $userId = $user->id;
        $data = $this->data($request);
        $managerId = $userId;
        $role = $user->role;
        $users = $this->userService->getAllUserByManagerId($managerId, $job_position);
        // if(count($users) == 0){
        //     $this->success(array($user));
        // }
        $this->success($users);
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getUserProfile(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;

        if (isset($data['user_id'])) {
            if ($data['user_id'] != $user->id && $user->role != config('API.Constant.Role.SuperAdmin') &&  $user->role != config('API.Constant.Role.Admin')) {
                $this->error(config('API.Message.NotEnoughPermission'));
            }
            $userId = $data['user_id'];
        }

        $user = $this->userService->userRepo->model->where('id', $userId)->with(['CertificateFile', 'BackId', 'FrontId'])->first();

        $this->userService->getUserImage($user);


        $this->success($user);
    }

    public function getUserById($userId)
    {
        $user = $this->userService->userRepo->model->where('id', $userId)->with(['CertificateFile', 'BackId', 'FrontId'])->first();

        $this->userService->getUserImage($user);


        $this->success($user);
    }
    public function getAll()
    {
        $users = $this->userService->userRepo->model->where('status', 1)->get();
        $this->success($users);
    }

    /**
     * Create new user
     * @param UserRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addNewUser(UserRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $status = $this->userService->checkRole($user->role, $data['role']);
        if (!isset($data['manager']) && $data['job_position'] != 1 && $data['job_position'] != 7) {
            $this->error(config('API.Message.User.ManagerIsRequire'));
        }
        if (isset($data['manager'])) {
            $getUser =  $this->userService->userRepo->getModelById($data['manager']);
        }
        if ($data['manager'] == 1 && $data['job_position'] == 8) {
            $a = 'nothing';
        } else if (
            isset($getUser) && $getUser['job_position'] == 1 && $data['job_position'] != 2 ||
            isset($getUser) && $getUser['job_position'] == 2 && $data['job_position'] != 3 ||
            isset($getUser) && $getUser['job_position'] == 3 && $data['job_position'] != 4 ||
            isset($getUser) && $getUser['job_position'] == 4 && $data['job_position'] != 5 ||
            isset($getUser) && $getUser['job_position'] == 5 && $data['job_position'] != 6 ||
            isset($getUser) && $getUser['job_position'] == 6 && $data['job_position'] != 7
        ) {  // nếu cấp trên trực tiếp và cấp dưới phải liền cấp
            $this->error(config('API.Message.User.JopPositionInvalid'));
        }
        if (isset($getUser) && $getUser['job_position'] == 1) {  // nếu cấp trên trực tiếp là Super Admin
            $data['super_admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
        }
        if (isset($getUser) && $getUser['job_position'] == 2) {  // nếu cấp trên trực tiếp là Admin
            $data['super_admin_id'] = $getUser['super_admin_id'];
            $data['admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
        }
        if (isset($getUser) && $getUser['job_position'] == 3) {  // nếu cấp trên trực tiếp là Sale Admin
            $data['super_admin_id'] = $getUser['super_admin_id'];
            $data['admin_id'] = $getUser['admin_id'];
            $data['sale_admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
        }
        if (isset($getUser) && $getUser['job_position'] == 4) {  // nếu cấp trên trực tiếp là Senior Manager
            $data['super_admin_id'] = $getUser['super_admin_id'];
            $data['admin_id'] = $getUser['admin_id'];
            $data['sale_admin_id'] = $getUser['sale_admin_id'];
            $data['senior_manager_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
        }
        if (isset($getUser) && $getUser['job_position'] == 5) {  // nếu cấp trên trực tiếp là Manager
            $data['super_admin_id'] = $getUser['super_admin_id'];
            $data['admin_id'] = $getUser['admin_id'];
            $data['sale_admin_id'] = $getUser['sale_admin_id'];
            $data['senior_manager_id'] = $getUser['senior_manager_id']; // senior_manager_id' = $getUser['senior_manager_id']; (id của senior manager)
            $data['manager_id'] = $data['manager']; // manager_id = $data['manager'] (id của cấp trên)
        }
        if (isset($getUser) && $getUser['job_position'] == 6) {  // nếu cấp trên trực tiếp là Team Leader
            $data['super_admin_id'] = $getUser['super_admin_id'];
            $data['admin_id'] = $getUser['admin_id'];
            $data['sale_admin_id'] = $getUser['sale_admin_id'];
            $data['senior_manager_id'] = $getUser['senior_manager_id']; // senior_manager_id' = $getUser['senior_manager_id']; (id của senior manager)
            $data['manager_id'] =  $getUser['manager_id']; // manager_id = $data['manager'] (id của manager)
            $data['team_leader_id'] =  $data['manager'];
        }
        if (isset($data['project']) && count($data['project']) == 0) {
            $data['project'] = null;
        }
        if (isset($data['province']) && count($data['province']) == 0) {
            $data['province'] = null;
        }
        if (isset($data['maximum_price']) && isset($data['minimum_price']) && $data['maximum_price'] < $data['minimum_price']) {
            $this->error(config('API.Message.User.LimitPriceNotAllowed'));
        }
        $user = $this->userService->createUser($data);


        if (!$user) {
            $this->error(config('API.Message.ServerError'));
        }
        $channels = $this->postAddressService->postAddressRepo->model->get();
        foreach ($channels as $channel) {
            $this->postAddressStatusService->postAddressStatusRepo->model()->create(['user_id' => $user->id, 'channel' => $channel['channel']]);
        }
        $this->success($user, null, 201);
    }


    /**
     * @param UserRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateUserProfile(UserRequest $request)
    {
        // 1: 'Super Admin',
        // 2: 'Admin',
        // 3: 'Sale Admin',
        // 4: 'Senior Manager', // trưởng phòng kinh doanh  senior_manager_id
        // 5: 'Manager', // phó phòng kinh doanh  manager_id
        // 6: 'Team Leader', // tổ trưởng  team_leader_id
        // 7: 'Staff', // nhân viên kinh doanh
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        if (isset($data['province'])) {
            $data['province'] = serialize($data['province']);
        }
        if (isset($data['project'])) {
            $data['project'] = serialize($data['project']);
        }
        if (!isset($data['maximum_price'])) {
            $data['maximum_price'] = null;
        }
        if (!isset($data['minimum_price'])) {
            $data['minimum_price'] = null;
        }
        if (isset($data['maximum_price']) && isset($data['minimum_price']) && $data['maximum_price'] < $data['minimum_price']) {
            $this->error(config('API.Message.User.LimitPriceNotAllowed'));
        }

        if (isset($data['user_id'])) {
            if ($data['user_id'] != $user->id && $user->role != config('API.Constant.Role.SuperAdmin') &&  $user->role != config('API.Constant.Role.Admin')) {
                $this->error(config('API.Message.NotEnoughPermission'));
            }
            $userId = $data['user_id'];
            $subordinates = $this->userService->getAllUserByManagerId($data['user_id'], null);
        }
        if (isset($data['user_id']) && $data['user_id'] != $user->id) {
            $updateUser =  $this->userService->userRepo->getModelById($data['user_id']);
            if ($user->role > $updateUser['role'] || $updateUser['role'] == config('API.Constant.Role.SuperAdmin') && $user->role == config('API.Constant.Role.Admin')) {
                $this->error(config('API.Message.User.ForbiddenRemove'));
            }
        }

        if (isset($data['manager'])) {
            $getUser =  $this->userService->userRepo->getModelById($data['manager']);
            if ($data['manager'] == 1 && $data['job_position'] == 8) {
                $a = 'nothing';
            } else if (
                isset($getUser) && $getUser['job_position'] == 1 && $data['job_position'] != 2 ||
                isset($getUser) && $getUser['job_position'] == 2 && $data['job_position'] != 3 ||
                isset($getUser) && $getUser['job_position'] == 3 && $data['job_position'] != 4 ||
                isset($getUser) && $getUser['job_position'] == 4 && $data['job_position'] != 5 ||
                isset($getUser) && $getUser['job_position'] == 5 && $data['job_position'] != 6 ||
                isset($getUser) && $getUser['job_position'] == 6 && $data['job_position'] != 7
            ) {  // nếu cấp trên trực tiếp và cấp dưới phải liền cấp
                $this->error(config('API.Message.User.JopPositionInvalid'));
            }
            if (isset($getUser) && $getUser['job_position'] == 1) {  // nếu cấp trên trực tiếp là Super Admin
                $data['super_admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
                $data['admin_id'] = 0;
                $data['sale_admin_id'] = 0;
                $data['senior_manager_id'] = 0;
                $data['manager_id'] = 0;
                $data['team_leader_id'] = 0;

                if (count($subordinates) > 0) {
                    $subordinates_info['super_admin_id'] = $data['manager'];
                    $subordinates_info['admin_id'] = $data['user_id'];
                    //$subordinates_info['manager'] = $data['user_id'];

                    foreach ($subordinates as $sub) {
                        $this->userService->updateUser($sub->id, $subordinates_info);
                    }
                }
            }
            if (isset($getUser) && $getUser['job_position'] == 2) {  // nếu cấp trên trực tiếp là Admin
                $data['super_admin_id'] = $getUser['super_admin_id'];
                $data['admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
                $data['sale_admin_id'] = 0;
                $data['senior_manager_id'] = 0;
                $data['manager_id'] = 0;
                $data['team_leader_id'] = 0;

                if (count($subordinates) > 0) {
                    $subordinates_info['super_admin_id'] = $getUser['super_admin_id'];
                    $subordinates_info['admin_id'] = $data['manager'];
                    $subordinates_info['sale_admin_id'] = $data['user_id'];
                    //$subordinates_info['manager'] = $data['user_id'];

                    foreach ($subordinates as $sub) {
                        //dd($sub->id);
                        $this->userService->updateUser($sub->id, $subordinates_info);
                    }
                }
            }
            if (isset($getUser) && $getUser['job_position'] == 3) {  // nếu cấp trên trực tiếp là Sale Admin
                $data['super_admin_id'] = $getUser['super_admin_id'];
                $data['admin_id'] = $getUser['admin_id'];
                $data['sale_admin_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
                $data['senior_manager_id'] = 0;
                $data['manager_id'] = 0;
                $data['team_leader_id'] = 0;

                if (count($subordinates) > 0) {
                    $subordinates_info['super_admin_id'] = $getUser['super_admin_id'];
                    $subordinates_info['admin_id'] = $getUser['admin_id'];
                    $subordinates_info['sale_admin_id'] = $data['manager'];
                    $subordinates_info['senior_manager_id'] = $data['user_id'];
                    //$subordinates_info['manager'] = $data['user_id'];

                    foreach ($subordinates as $sub) {
                        $this->userService->updateUser($sub->id, $subordinates_info);
                    }
                }
            }
            if (isset($getUser) && $getUser['job_position'] == 4) {  // nếu cấp trên trực tiếp là Senior Manager
                $data['super_admin_id'] = $getUser['super_admin_id'];
                $data['admin_id'] = $getUser['admin_id'];
                $data['sale_admin_id'] = $getUser['sale_admin_id'];
                $data['senior_manager_id'] = $data['manager']; // senior_manager_id' = $data['manager'] (id của cấp trên)
                $data['manager_id'] = 0;
                $data['team_leader_id'] = 0;

                if (count($subordinates) > 0) {
                    $subordinates_info['super_admin_id'] = $getUser['super_admin_id'];
                    $subordinates_info['admin_id'] = $getUser['admin_id'];
                    $subordinates_info['sale_admin_id'] = $getUser['sale_admin_id'];
                    $subordinates_info['senior_manager_id'] = $data['manager'];
                    $subordinates_info['manager_id'] = $data['user_id'];
                    //$subordinates_info['manager'] = $data['user_id'];

                    foreach ($subordinates as $sub) {
                        $this->userService->updateUser($sub->id, $subordinates_info);
                    }
                }
            }
            if (isset($getUser) && $getUser['job_position'] == 5) {  // nếu cấp trên trực tiếp là Manager
                $data['super_admin_id'] = $getUser['super_admin_id'];
                $data['admin_id'] = $getUser['admin_id'];
                $data['sale_admin_id'] = $getUser['sale_admin_id'];
                $data['senior_manager_id'] = $getUser['senior_manager_id']; // senior_manager_id' = $getUser['senior_manager_id']; (id của senior manager)
                $data['manager_id'] = $data['manager']; // manager_id = $data['manager'] (id của cấp trên)
                $data['team_leader_id'] = 0;

                if (count($subordinates) > 0) {
                    $subordinates_info['super_admin_id'] = $getUser['super_admin_id'];
                    $subordinates_info['admin_id'] = $getUser['admin_id'];
                    $subordinates_info['sale_admin_id'] = $getUser['sale_admin_id'];
                    $subordinates_info['senior_manager_id'] = $getUser['senior_manager_id'];
                    $subordinates_info['manager_id'] = $data['manager'];
                    $subordinates_info['team_leader_id'] = $data['user_id'];
                    //$subordinates_info['manager'] = $data['user_id'];

                    foreach ($subordinates as $sub) {
                        $this->userService->updateUser($sub->id, $subordinates_info);
                    }
                }
            }
            if (isset($getUser) && $getUser['job_position'] == 6) {  // nếu cấp trên trực tiếp là Team Leader
                $data['super_admin_id'] = $getUser['super_admin_id'];
                $data['admin_id'] = $getUser['admin_id'];
                $data['sale_admin_id'] = $getUser['sale_admin_id'];
                $data['senior_manager_id'] = $getUser['senior_manager_id']; // senior_manager_id' = $getUser['senior_manager_id']; (id của senior manager)
                $data['manager_id'] =  $getUser['manager_id']; // manager_id = $data['manager'] (id của manager)
                $data['team_leader_id'] =  $data['manager'];
            }
        } else {
            if (isset($data['job_position']) && $data['job_position'] == 1) { // nếu cập nhật vị trí là super admin
                $data['super_admin_id'] = 0;
                $data['admin_id'] = 0;
                $data['sale_admin_id'] = 0;
                $data['senior_manager_id'] = 0;
                $data['manager_id'] = 0;
                $data['team_leader_id'] = 0;
            }
            if (isset($data['job_position']) && $data['job_position'] == 7) {
                $data['super_admin_id'] = null;
                $data['admin_id'] = null;
                $data['sale_admin_id'] = null;
                $data['senior_manager_id'] = null;
                $data['manager_id'] = null;
                $data['team_leader_id'] = null;
                $data['manager'] = null;
            }
        }

        if (isset($data['phone_number']) && !$this->userService->checkDuplicate($userId, 'phone_number', $data['phone_number'])) {
            $this->error(config('API.Message.User.PhoneNumerTaken'));
        }

        $user = $this->userService->updateUser($userId, $data);
        if (isset($data['status']) && $data['status'] === 0) {
            $houses = $this->houseService->getHouseByUserId($data['user_id']);
            foreach ($houses as $house) {
                $dataUpdate = [
                    'status' => 1,
                    'public_approval' => 0,
                    'public' => 0,
                    'web' => 0,
                    'web_approval' => 0
                ];
                $this->houseService->houseRepo->update($house->id, $dataUpdate);
            }
        }

        $this->success($user);
    }

    public function updateAvatar(UserRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;

        $user = $this->userService->updateUser($userId, $data);
        $this->success($user);
    }


    public function alonhadatUpdate(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = $user->id;
        $user = $this->userService->updateUser($userId, $data);
        $this->success($user);
    }

    /**
     * Remove user
     * @param UserRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function removeUserProfile(UserRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $user = $this->userService->removeUser($data['user_id'], $user);

        if (!$user) {
            $this->error(config('API.Message.User.ForbiddenRemove'), 403);
        }

        $this->success($user);
    }

    public function adminResetPassword(UserRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $user = $this->authService->adminResetPassword($data, $user);

        if (!$user) {
            $this->error(config('API.Message.User.ForbiddenReset'), 403);
        }

        $this->success($user);
    }

    /**
     * Send reset code & check code & change password
     * @param PasswordRequest $request
     * @param $type
     * @throws \App\Exceptions\JsonResponse
     */
    public function changePassword(PasswordRequest $request, $type)
    {
        $data = $this->data($request);
        $method = $type . 'Password';
        $user = $this->authService->$method($data);

        if ($user) {
            $this->success($user);
        } else {
            $this->error(config('API.Message.ServerError'));
        }
    }

    public function getTrackList(Request $request)
    {
        $this->getUser($user);

        if ($user->role != config('API.Constant.Role.SuperAdmin')) {
            $this->error(config('API.Message.Forbidden'));
        }

        $locations = $this->userService->getAllLocationList($user->id);

        $this->success($locations);
    }

    public function getLoginDevices()
    {
        $this->getUser($user);

        $locations = $this->userService->getLoginDevices($user->id);

        $this->success($locations);
    }

    public function getLocationByUser($userId)
    {
        $this->getUser($user);

        if ($user->role != config('API.Constant.Role.SuperAdmin')) {
            $userId = $user->id;
        }

        $locations = $this->userService->getLocationByUser($userId);

        $this->success($locations);
    }

    public function getLastLocation(Request $request)
    {
        $this->getUser($user);

        $lastLocation = $this->userService->getUserLastLocation($user->id);

        $this->success($lastLocation);
    }

    public function trackLocation(TrackLocationRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $data['ip_address'] = $request->getClientIp();

        $track = $this->userService->createTrackLocation($data);

        if (!$track) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($track);
    }

    public function getAllName()
    {
        $users = $this->userService->userRepo->model->select('name')->where('status', 1)->get();
        $this->success($users);
    }

    /**
     * Get house view activity
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getHouseViewActivity(Request $request)
    {
        $data = $this->data($request);

        $this->getUser($user);

        if ($user->role != config('API.Constant.Role.SuperAdmin') && $user->role != config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }

        $data = $this->userService->getHouseViewActivity($data["house_id"]);
        $this->success($data);
    }

    /**
     * Add house view activity
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addHouseViewActivity(Request $request)
    {
        $data = $this->data($request);

        $this->getUser($user);

        $data = $this->userService->addHouseViewActivity($user, $data["house_id"]);
        $this->success($data);
    }
}

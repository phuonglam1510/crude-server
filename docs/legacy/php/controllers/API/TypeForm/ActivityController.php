<?php

namespace App\Http\Controllers\API\TypeForm;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\TypeForm\ActivityService;
use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class ActivityController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $activityService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        ActivityService $activityService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->activityService = $activityService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   
    public function addActivity(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $activity = $this->ActivityService->addActivity($data);
        $this->success($activity);

    }
    
    public function getActivityList(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $activity = $this->activityService->getActivityList();
        $this->success($activity);

    }
}


<?php

namespace App\Http\Controllers\API\TypeForm;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\TypeForm\ReasonLeaveService;
use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class ReasonLeaveController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $reasonLeaveService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        ReasonLeaveService $reasonLeaveService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->reasonLeaveService = $reasonLeaveService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   
    public function addReason(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $reason = $this->reasonLeaveService->addReason($data);
        $this->success($reason);

    }
    
    public function getReasonList(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $list = $this->reasonLeaveService->getReasonList();
        $this->success($list);

    }



   

  

  

}


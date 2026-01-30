<?php

namespace App\Http\Controllers\API\TypeForm;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\TypeForm\ReasonExplanationService;
use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class ReasonExplanationController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $reasonExplanationService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        ReasonExplanationService $reasonExplanationService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->reasonExplanationService = $reasonExplanationService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   
    public function addReasonExplanation(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $reasonExplanation = $this->reasonExplanationService->addReasonExplanation($data);
        $this->success($reasonExplanation);

    }
    
    public function getReasonExplanationList(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $reasonExplanation = $this->reasonExplanationService->getReasonExplanationList();
        $this->success($reasonExplanation);

    }



   

  

  

}

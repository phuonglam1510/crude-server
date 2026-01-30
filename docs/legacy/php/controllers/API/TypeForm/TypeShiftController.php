<?php

namespace App\Http\Controllers\API\TypeForm;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\TypeForm\TypeShiftService;
use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class TypeShiftController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $typeShiftService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        TypeShiftService $typeShiftService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->typeShiftService = $typeShiftService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   
    public function addTypeShift(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $typeShift = $this->typeShiftService->addTypeShift($data);
        $this->success($typeShift);

    }
    
    public function getTypeShiftList(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $typeShift = $this->typeShiftService->getTypeShiftList();
        $this->success($typeShift);

    }



   

  

  

}


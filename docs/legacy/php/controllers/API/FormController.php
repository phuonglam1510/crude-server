<?php

namespace App\Http\Controllers\API\Form;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\FormRegime\FormRegimeService;
use App\Services\FormLeave\FormLeaveService;
use App\Services\FormBusiness\FormBusinessService;
use App\Services\FormAbsence\FormAbsenceService;
use App\Services\FormExplanation\FormExplanationService;

use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class FormController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $formRegimeService;
    private $formLeaveService;
    private $formBusinessService;
    private $formAbsenceService;
    private $formExplanationService;


    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        FormRegimeService $formRegimeService,
        FormLeaveService $formLeaveService,
        FormBusinessService $formBusinessService,
        FormAbsenceService $formAbsenceService,
        FormExplanationService $formExplanationService


    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->formRegimeService = $formRegimeService;
        $this->formLeaveService = $formLeaveService;
        $this->formBusinessService = $formBusinessService;
        $this->formAbsenceService = $formAbsenceService;
        $this->formExplanationService = $formExplanationService;

        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   


    public function geWaiting(Request $request, $type) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        switch($type) {
            case 'regime':
                $list = $this->formRegimeService->getWaitingList($user);
                break;
            case 'leave':
                $list = $this->formLeaveService->getWaitingList($user);
                break;
            case 'absence':

                $list = $this->formAbsenceService->getWaitingList($user);
                
                break;
            case 'business':
                $list = $this->formBusinessService->getWaitingList($user);
                break;
            case 'explanation':
                $list = $this->formExplanationService->getWaitingList($user);
                break;

        }
        // $list = $this->formRegimeService->getList($user);
        $this->success($list);

    }

    public function approveForm(Request $request, $type) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);

        switch($type) {
            case 'regime':
                $list = $this->formRegimeService->approve($data['id']);
                break;
            case 'leave':
                $list = $this->formLeaveService->approve($data['id']);
                break;
            case 'absence':
                $list = $this->formAbsenceService->approve($data['id']);
                break;
            case 'business':
                $list = $this->formBusinessService->approve($data['id']);
                break;
            case 'explanation':
                $list = $this->formExplanationService->approve($data['id']);
                break;

        }
        // $list = $this->formRegimeService->getList($user);
        $this->success($list);

    }

    public function rejectForm(Request $request, $type) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);

        switch($type) {
            case 'regime':
                $list = $this->formRegimeService->rejectForm($data['id']);
                break;
            case 'leave':
                $list = $this->formLeaveService->rejectForm($data['id']);
                break;
            case 'absence':
                $list = $this->formAbsenceService->rejectForm($data['id']);
                break;
            case 'business':
                $list = $this->formBusinessService->rejectForm($data['id']);
                break;
            case 'explanation':
                $list = $this->formExplanationService->rejectForm($data['id']);
                break;

        }
        // $list = $this->formRegimeService->getList($user);
        $this->success($list);

    }
}


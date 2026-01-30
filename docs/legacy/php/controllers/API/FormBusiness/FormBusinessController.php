<?php

namespace App\Http\Controllers\API\FormBusiness;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\FormBusiness\FormBusinessService;
use App\Repositories\User\UserRepository;
use DateTime;
use DateTimeZone;

class FormBusinessController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $userRepo;
    private $formBusinessService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        UserRepository $userRepository,
        FormBusinessService $formBusinessService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->formBusinessService = $formBusinessService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

   
    public function addForm(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $form = $this->formBusinessService->addForm($data);
        $this->success($form);

    }

    public function getList(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $userId = $user->id;
        if($user->role === 1 || $user->role === 2) {
            $list = $this->formBusinessService->getList($user);
            $this->success($list);
        }
        if($user->job_position === 7) {
            $list = $this->formBusinessService->getListByUserId($userId);
            $this->success($list);
        }
        $data = $this->data($request);
        $query = $this->userRepo->model->select('id');
        if($user->job_position === 6)  {
            $query = $query->where('team_leader_id', $userId);
        }
        if($user->job_position === 5)  {
            $query = $query->where('manager_id', $userId);
        }
        if($user->job_position === 4)  {
            $query = $query->where('senior_manager_id', $userId);
        }
        if($user->job_position === 3)  {
            $query = $query->where('sale_admin_id', $userId);
        }

        if($user->job_position === 2)  {
            $query = $query->where('admin_id', $userId);
        }
        if($user->job_position === 1)  {
            $query = $query->where('super_admin_id', $userId);
        }
       
        $userList = $this->userService->getUserByManager($query)->toArray();
        array_push($userList, ['id'=>(int) $userId]);
        $list = $this->formBusinessService->getListByManagerOrUser($userList);
        // dd($locations);
        $this->success($list);


    }

    public function updateForm(Request $request) {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        
        $data = $this->data($request);
        $form = $this->formBusinessService->updateForm($data);
        $this->success($form);

    }

    public function deleteForm(Request $request) {
        $this->getUser($user);
        $data = $this->data($request);
        $find = $this->formBusinessService->getFormById($data['id']);
        
        if($find) {
            
            if ($user->role != config('API.Constant.Role.SuperAdmin') || $find->user_id !== $user->id) {
                $this->error(config('API.Message.Forbidden'));
            }
        }
        
        $form = $this->formBusinessService->deleteForm($data['id']);
        $this->success($form);

    }
}


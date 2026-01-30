<?php

namespace App\Http\Controllers\API\Knowledge;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\House\ImageService;
use App\Services\Customer\CustomerService;
use App\Services\Knowledge\TypeService;

use App\Services\Customer\CustomerService as CustomerCustomerService;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $houseService;
    private $questionService;
    private $imageService;
    private $typeService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        HouseService $houseService,
        ImageService $imageService,
        TypeService $typeService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->houseService = $houseService;
        $this->imageService = $imageService;
        $this->typeService = $typeService;
    }
    public function getType(Request $request)
    {
        $this->getUser($user);
        $type = $this->typeService->getType();

        $this->success($type, null, 200);
    }

    public function addNewType(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if (
            $user->role != config('API.Constant.Role.Admin') &&
            $user->role != config('API.Constant.Role.SuperAdmin')
        ) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }
        $findType = $this->typeService->getType();
        foreach($findType as $type) {
            if(strtolower($type->type_name) === strtolower($data['type_name'])) {
                $this->error('Tên cột đã tồn tại');
            }
        }
        $type = $this->typeService->addNewType($data);
        $this->success($type, null, 201);
    }

   
}


<?php

namespace App\Http\Controllers\API\House;

use App\Http\Controllers\Controller;
use App\Http\Requests\House\HouseCommentRequest;
use App\Http\Requests\House\HouseRequest;
use App\Http\Traits\CustomRequest;
use App\Services\House\HouseService;
use App\Services\House\SubRecommendHouseService;
use App\Repositories\Customer\HouseRecommendStatusRepository;
use App\Repositories\Customer\CustomerRequestRepository;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class SubRecommendHouseController extends Controller
{
    use CustomRequest;
    private $houseService;
    private $userService;
    private $subRecommendHouseService;

    public function __construct(
        HouseService $houseService,
        UserService $userService,
        SubRecommendHouseService $subRecommendHouseService,
        HouseRecommendStatusRepository $houseRecommendRepository,
        CustomerRequestRepository $customerRequestRepository
    ) {
        $this->houseService = $houseService;
        $this->userService = $userService;
        $this->subRecommendHouseService = $subRecommendHouseService;
        $this->houseRecommendRepo = $houseRecommendRepository;
        $this->customerRequestRepo = $customerRequestRepository;
    }

    public function createSubRecommendHouse(Request $request){
        $this->getUser($user);
        $data = $this->data($request);
        $existed = $this->subRecommendHouseService->subRecommendHouseRepo->model->where('house_id',$data['house_id'])->where('request_id',$data['request_id'])->first();
        if($existed){
            $this->error(config('API.Message.House.DuplicateAddress'), 400);
        }
        $subList = $this->subRecommendHouseService->subRecommendHouseRepo->create($data);
        $this->success($subList, null, 201);
    }
    

  
    
}

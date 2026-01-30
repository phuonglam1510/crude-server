<?php

namespace App\Http\Controllers\API\User;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\User\PostManagerService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\ActionHistoryService;
use App\Services\User\PostAddressStatusService;

use Illuminate\Http\Request;

class PostAddressStatusController extends Controller
{
    use CustomRequest;
    private $userService;
    private $postManagerService;
    private $houseService;
    private $actionHistoryService;
    private $postAddressStatusService;

    public function __construct( UserService $userService, PostManagerService $postManagerService,
    HouseService $houseService, ActionHistoryService $actionHistoryService, PostAddressStatusService $postAddressStatusService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->userService = $userService;
        $this->postManagerService = $postManagerService;
        $this->houseService = $houseService;
        $this->actionHistoryService  = $actionHistoryService;
        $this->postAddressStatusService = $postAddressStatusService;
    }


    public function create(Request $request)
    {
        $this->getUser($user);
        $req = $this->data($request);
        $req['user_id'] = $user->id;
        $res = $this->postAddressStatusService->postAddressStatusRepo->create($req);
        if ($res) {
            $this->success($res);
        }

      
        $this->error(config('API.Message.PostManager.CreateFailed'));
    }

    public function get(){
      $this->getUser($user);
      $result = $this->postAddressStatusService->getPostAddress($user->id);
      //$action = $this->actionHistoryService->actionHistoryRepo->model->where
      if($result){
        $this->success($result);
      }
      $this->error(config('API.Message.PostManager.NotFound'));
    }
    
    public function getActive(){
      $this->getUser($user);
      $result = $this->postAddressStatusService->getPostAddressActive($user->id);
      //$action = $this->actionHistoryService->actionHistoryRepo->model->where
      if($result){
        $this->success($result);
      }
      $this->error(config('API.Message.PostManager.NotFound'));
    }
    public function getHouse(){
      $house = $this->houseService->houseRepo->model->where('status',0)->where('public',1)->select('id','house_address','house_number','village','district','province')->get();
      $this->success($house);
    }

    public function update(Request $request){
      $this->getUser($user);
      $req = $this->data($request);
      $res = $this->postAddressStatusService->update($user->id,$req['channel'], $req['status']);
      if($res){
        $this->success($res);
      }else{
        $this->error(config('API.Message.PostManager.UpdateFailed'));
      }
    }

  
   
}

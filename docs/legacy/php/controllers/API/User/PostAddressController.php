<?php

namespace App\Http\Controllers\API\User;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Models\User\PostAddressStatus;
use App\Services\User\PostManagerService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\ActionHistoryService;
use App\Services\User\PostAddressService;
use App\Services\User\PostAddressStatusService;

use Illuminate\Http\Request;

class PostAddressController extends Controller
{
    use CustomRequest;
    private $userService;
    private $postManagerService;
    private $houseService;
    private $actionHistoryService;
    private $postAddressService;

    public function __construct( UserService $userService, PostManagerService $postManagerService,
    HouseService $houseService, ActionHistoryService $actionHistoryService, PostAddressService $postAddressService, PostAddressStatusService $postAddressStatusService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->userService = $userService;
        $this->postManagerService = $postManagerService;
        $this->houseService = $houseService;
        $this->actionHistoryService  = $actionHistoryService;
        $this->postAddressService = $postAddressService;
        $this->postAddressStatusService = $postAddressStatusService;
    }


    public function create(Request $request)
    {
      $this->getUser($user);
      $req = $this->data($request);
      $req['user_id'] = $user->id;
      $existed = $this-> postAddressService->postAddressRepo->model->where('channel', $req['channel'])->first();
      if($existed){
        $this->error(config('API.Message.PostManager.Existed'));
      }
      $res = $this->postAddressService->create($req);
      if ($res) {
        $userLst = $this->userService->userRepo->model->where('status',1)->get();
        foreach ($userLst as $key => $user){
          $this->postAddressStatusService->postAddressStatusRepo->model()->create(['user_id'=>$user->id, 'channel'=>$req['channel']]);
        }
        $this->success($res);
      }

      
      $this->error(config('API.Message.PostManager.CreateFailed'));
    }

    public function get(){
      $this->getUser($user);
      $result = $this->postAddressService->getPostAddress($user->id);
      if($result){
        $this->success($result);
      }
      $this->error(config('API.Message.PostManager.NotFound'));
    }
    public function getActive(){
      $this->getUser($user);
      $result = $this->postAddressService->getPostAddressActive($user->id);
      if($result){
        $this->success($result);
      }
      $this->error(config('API.Message.PostManager.NotFound'));
    }

    // public function getHouse(){
    //   $house = $this->houseService->houseRepo->model->where('status',0)->where('public',1)->select('id','house_address','house_number','village','district','province')->get();
    //   $this->success($house);
    // }

    public function update(Request $request){
      $req = $this->data($request);
      //dd($req['channel']);
      $this->postAddressService->postAddressRepo->model->where('channel', $req['channel'])->update(['channel'=>$req['new_channel']]);
      $this->postAddressStatusService->postAddressStatusRepo->model->where('channel', $req['channel'])->update(['channel'=>$req['new_channel']]);
      $this->postManagerService->postManagerRepo->model->where('channel', $req['channel'])->update(['channel'=>$req['new_channel']]);
      $this->success(null);
    }

  
   
}

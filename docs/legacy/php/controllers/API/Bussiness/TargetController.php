<?php

namespace App\Http\Controllers\API\Bussiness;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\Bussiness\TargetService;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    use CustomRequest;
    private $authService;
    private $targetService;
    private $userService;

    public function __construct(AuthService $authService, UserService $userService, TargetService $targetService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->targetService = $targetService;
    }


    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function create(Request $request){
      $this->getUser($user);
      $data = $this->data($request);
      $data['user_id'] = $user->id;
      $existed = $this->targetService->targetRepo->model->where('user_id', $user->id)->where('quarter', $data['quarter'])->first();
      if($existed){
        $this->error(config('API.Message.Bussiness.Duplicate'));
      }
      return $this->targetService->targetRepo->create($data);
    }
    
    public function getTarget(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $startAt = strtotime($data['startAt']);
        $endAt = strtotime($data['endAt']);
        $userId = $user->id;
        if(in_array('target',$data)){
          $quarter = $data['quarter'];
        }
        if(isset($data['staff'])){
           foreach ($data['staff'] as $key => $value){
              if(intval($value)==0){
                $data['staff'][$key] = $user->id;
              }
            }
        }
        if(isset($data['team']) && $data['team'] == true){
          $staffList = $this->userService->getAllUserByManagerId($data['staff'], null);
          foreach ($staffList as $staff){
                array_push($data['staff'],$staff['id']);
            };
        }
        
        if( isset($data['staff']) && $quarter){
          $res = $this->targetService->targetRepo->getTarget($startAt, $endAt, $data['staff'], $quarter);
        }
        //dd($startAt);
        else{
          $res = $this->targetService->targetRepo->getTarget($startAt, $endAt, $userId);

        }

        $this->success($res);
    }

   

    public function updateTarget(Request $request){
      $this->getUser($user);
      $data = $this->data($request);
      $id = $data['id'];
      unset($data['id']);
       unset($data['quarter']);
      $res = $this->targetService->updateTarget($id, $data);
      $this->success($res, null, 200);
    }



    
}

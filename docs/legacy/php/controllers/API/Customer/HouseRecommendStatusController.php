<?php

namespace App\Http\Controllers\API\Customer;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Customer\HouseRecommendStatusService;
use App\Services\Customer\HouseRecommendHistoryService;
use App\Services\House\HouseService;
use Illuminate\Http\Request;

class HouseRecommendStatusController extends Controller
{
    use CustomRequest;
    private $recommendService;
    private $houseService;
    private $recommnedHistoryService;

    public function __construct(HouseRecommendStatusService $recommendService, HouseService $houseService, HouseRecommendHistoryService $recommnedHistoryService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);

        $this->recommendService = $recommendService;
        $this->houseService = $houseService;
        $this->recommnedHistoryService = $recommnedHistoryService;
    }

    /**
     * Get customer request detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    
     public function createHouseRecommendStatus(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $existed = $this->recommendService->getHouseRecommendStatus($data['house_id'],$data['customer_id']);
        if($existed){
          
          $houseRecommendStatus = $this->recommendService->houseRecommendRepo->update($existed->id,$data);
         
        }
        else{
          
          // not existed, create history
          $houseRecommendStatus = $this->recommendService->createHouseRecommendStatus($data);
          
        }
        $history = $this->recommnedHistoryService->createHouseRecommendHistory(['house_id'=>$data['house_id'], 
          'customer_id'=>$data['customer_id'],'target'=>$data['status'],'request_id'=>$data['request_id'],'user_id'=>$user->id]);
        // if($history){
        //   $houseRecommendStatus->history = $history;
        // }
        if($houseRecommendStatus && $data['status']==1){
          $house = $this->houseService->houseRepo->model->where('id', $houseRecommendStatus->house_id)->first();
          $quantity = $house->recommend_quantity +1 ;
          $this->houseService->houseRepo->model->where('id',$houseRecommendStatus->house_id)->update(['recommend_quantity' => $quantity]);
          
        }else if($houseRecommendStatus && $data['status']==2){
          $house = $this->houseService->houseRepo->model->where('id', $houseRecommendStatus->house_id)->first();
          $quantity = $house->seen_quantity +1 ;
          $this->houseService->houseRepo->model->where('id',$houseRecommendStatus->house_id)->update(['seen_quantity' => $quantity]);
        }
        $this->success($houseRecommendStatus);
    }
    
    public function updatePriority(Request $request){
      $data = $this->data($request);
      $existed = $this->recommendService->getHouseRecommendStatus($data['houseId'],$data['customerId']);
      if($existed){
        $this->recommendService->houseRecommendRepo->model->where('id',$existed->id)->update(['priority' => 0]);
        // $history = $this->recommnedHistoryService->createHouseRecommendHistory(['house_recommend_id'=>$existed->id, 
        //   'previous_target'=>$data['previousTarget'],'target'=>0]);
        // if($history){
        //   $existed->history = $history;
        // }
        $this->success($existed);
      }else{
        
        if($data['previousTarget']==0){
          $houseRecommendStatus = $this->recommendService->houseRecommendRepo->create(['customer_id'=>$data['customerId'], 'status'=>0,'priority'=>0,'house_id'=>$data['houseId']]);
          $this->success($houseRecommendStatus);
        }
        else{
          $this->error(config('API.Message.CustomerRequest.NotFound'), 404);
        }
      }
      
    }
}

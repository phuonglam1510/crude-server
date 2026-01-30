<?php

namespace App\Http\Controllers\API\Customer;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Customer\HouseRecommendStatusService;
use App\Services\Customer\HouseRecommendHistoryService;
use Illuminate\Http\Request;

class HouseRecommendHistoryController extends Controller
{
    use CustomRequest;
    private $recommendService;
    private $recommnedHistoryService;
    private $houseService;

    public function __construct(HouseRecommendStatusService $recommendService, HouseRecommendHistoryService $recommnedHistoryService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);

        $this->recommendService = $recommendService;
        $this->recommnedHistoryService = $recommnedHistoryService;
    }

    /**
     * Get customer request detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    
     public function getHistory(Request $request){
        $this->getUser($user);
        $data = $this->data($request);
        $history = $this->recommnedHistoryService->getHouseRecommendHistory($data['request_id']);
        //dd($history);
        //$history->house_address = $history->house->house_number.$history->house->house_address.$history->house->district.$history->house->province;
        $this->success($history);
        
        
     }
    
}

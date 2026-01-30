<?php

namespace App\Http\Controllers\API\Match;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Match\MatchService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    use CustomRequest;
    private $matchService;

    public function __construct(MatchService $matchService)
    {
        $this->matchService = $matchService;
    }

    public function getMatchRequest(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $houseId = isset($data['house_id']) ? $data['house_id'] : null;

        $houses = $this->matchService->matchHouseRequest($user, $houseId);
        $this->success($houses);
    }

    public function getMatchHouse(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $requestIds = isset($data['request_ids']) ? explode(',', $data['request_ids']) : null;
        $requests = $this->matchService->matchRequestHouse($user, $requestIds);
        $this->success($requests);
    }

    public function getSubRecommendHouse(Request $request){
        $data = $this->data($request);
        $subRecommendHouse = $this->matchService->subMatchRequestHouse($data);
        $this->success($subRecommendHouse, null, 200);
    }
}

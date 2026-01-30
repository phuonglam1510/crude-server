<?php

namespace App\Http\Controllers\API\Web;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Web\HouseService;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    use CustomRequest;
    public $houseService;

    public function __construct(HouseService $houseService)
    {
        $this->houseService = $houseService;
    }

    public function getHouseList(Request $request)
    {
        $data = $this->data($request);

        $houses = $this->houseService->getHouseList($data);

        $this->success($houses->items());
    }

    public function searchHouses(Request $request)
    {
        $data = $this->data($request);

        $houses = $this->houseService->getHouseList($data);

        $this->success(['total' => $houses->total(), 'data' => $houses->items()]);
    }

    public function getFeatureHouses()
    {

        $houses = $this->houseService->getFeatureHouses();

        $this->success($houses);
    }
    public function getFeatureHousesUpdate()
    {

        $houses = $this->houseService->getFeatureHousesUpdate();

        $this->success($houses);
    }


    public function getHouseDetail(Request $request)
    {
        $data = $this->data($request);

        $house = $this->houseService->getHouseDetail($data['house_id']);

        if (!$house) {
            $this->error(config('API.Message.House.NotExisted'), 500);
        }

        // Update view every time house is viewed
        $this->houseService->updateTotalView($house);
        $this->houseService->getHouseImage($house);

        $this->success($house);
    }

    public function getHouseBySlug($slug)
    {
        $house = $this->houseService->getHouseDetailBySlug($slug);

        if (!$house) {
            $this->error(config('API.Message.House.NotExisted'), 500);
        }

        // Update view every time house is viewed
        $this->houseService->updateTotalView($house);
        $this->houseService->getHouseImage($house);

        $this->success($house);
    }

    public function createHouseContactRequest(Request $request)
    {
        $data = $this->data($request);

        $house = $this->houseService->getHouseDetail($data['house_id']);

        if (!$house) {
            $this->error(config('API.Message.House.NotExisted'), 500);
        }

        $this->houseService->createHouseContactRequest($data);

        $this->success($data);
    }
}


<?php

namespace App\Services\House;


use App\Http\Traits\CustomResponse;
use App\Repositories\House\HouseRepository;
use App\Repositories\House\SubRecommendHouseRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class SubRecommendHouseService extends BaseService
{
    use CustomResponse;
    public $houseRepo;
    public $subRecommendHouseRepo;
    

    public function __construct(
        HouseRepository $houseRepository,
        SubRecommendHouseRepository $subRecommendHouseRepository
    ) {
        parent::__construct();
        $this->houseRepo = $houseRepository;
        $this->subRecommendHouseRepo = $subRecommendHouseRepository;
    }

    
}

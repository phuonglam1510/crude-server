<?php

namespace App\Services\House;


use App\Http\Traits\CustomResponse;
use App\Repositories\House\HouseTagRepository;
use App\Services\BaseService;

class HouseTagService extends BaseService
{
    use CustomResponse;
    public $tagRepo;

    public function __construct(
        HouseTagRepository $houseTagRepository
    ) {
        parent::__construct();
        $this->houseTagRepo = $houseTagRepository;
    }


   
    
}

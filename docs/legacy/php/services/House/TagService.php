<?php

namespace App\Services\House;


use App\Http\Traits\CustomResponse;
use App\Repositories\House\TagRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class TagService extends BaseService
{
    use CustomResponse;
    public $tagRepo;

    public function __construct(
        TagRepository $tagRepository
    ) {
        parent::__construct();
        $this->tagRepo = $tagRepository;
    }


   
    
}

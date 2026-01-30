<?php


namespace App\Services\House;

use App\Repositories\House\HouseSearchRepository;

class SearchService
{
    public $searchRepo;

    public function __construct(HouseSearchRepository $searchRepository)
    {
        $this->searchRepo = $searchRepository;
    }


    public function updateSearchForHouse($house)
    {
        $search = '';

        if (!empty($house->title)) {
            $search =  $search . ' ' . $house->title;
        }

        if (!empty($house->description)) {
            $search = $search . ' ' . $house->description;
        }

        if (!empty($house->house_address)) {
            $search = $search . ' Đường ' . $house->house_address;
        }

        \error_log($search);

        $searchModel =  $this->searchRepo->model->updateOrCreate(
            ['house_id' => $house->id],
            ['search' => $search]
        );

        \error_log('search updated for house id:' . $house->id);

        return $searchModel;
    }
}

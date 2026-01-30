<?php

namespace App\Services\House;


use App\Http\Traits\CustomResponse;
use App\Repositories\House\HouseBalconyDirectionRepository;
use App\Repositories\House\HouseLikeRepository;
use App\Repositories\House\HouseCommentRepository;
use App\Repositories\House\HouseDirectionRepository;
use App\Repositories\House\HousePreviewRepository;
use App\Repositories\House\HouseRepository;
use App\Repositories\House\BatdongsanRepository;
use App\Repositories\House\ProjectRepository;
use App\Repositories\House\ImageRepository;
use App\Repositories\House\StreetRepository;
use App\Repositories\House\HouseStatisticRepository;
use App\Repositories\House\HouseTagRepository;
use App\Repositories\User\PostManagerRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class BatdongsanService extends BaseService
{
    use CustomResponse;
    public $options;
    public $projectRepo;
    public $houseRepo;
    public $houseDirectionRepo;
    public $imageRepo;
    public $streetRepo;
    public $housePreviewRepo;
    public $houseCommentRepo;
    public $houseBalconyDirectionRepo;
    public $houseStatisticRepo;
    public $postManagerRepo;
    public $houseTagRepo;
    public $batdongsanRepo;

    public function __construct(

        HouseRepository $houseRepository,
        HouseDirectionRepository $houseDirectionRepository,
        ImageRepository $imageRepository,
        ProjectRepository $projectRepository,
        StreetRepository $streetRepository,
        HousePreviewRepository $housePreviewRepository,
        HouseCommentRepository $houseCommentRepository,
        HouseLikeRepository $houseLikeRepository,
        HouseBalconyDirectionRepository $houseBalconyDirectionRepository,
        HouseStatisticRepository $houseStatisticRepository,
        PostManagerRepository $postManagerRepository,
        HouseTagRepository $houseTagRepository,
        BatdongsanRepository $batdongsanRepo
    ) {
        parent::__construct();
        $this->houseRepo = $houseRepository;
        $this->houseDirectionRepo = $houseDirectionRepository;
        $this->projectRepo = $projectRepository;
        $this->imageRepo = $imageRepository;
        $this->streetRepo = $streetRepository;
        $this->housePreviewRepo = $housePreviewRepository;
        $this->houseCommentRepo = $houseCommentRepository;
        $this->houseLikeRepo = $houseLikeRepository;
        $this->houseBalconyDirectionRepo = $houseBalconyDirectionRepository;
        $this->houseStatisticRepo = $houseStatisticRepository;
        $this->postManagerRepo = $postManagerRepository;
        $this->houseTagRepo = $houseTagRepository;
        $this->batdongsanRepo = $batdongsanRepo;
    }

    public function getOptions()
    {
        $this->options = [
            'type' => config('API.Type'),
            'class' => config('API.Class'),
            'direction' => config('API.Direction'),
            'district' => config('API.District'),
            'province' => config('API.Province'),
            'customer' => config('API.Customer'),
            'ownership' => config('API.Ownership')
        ];
    }


    public function addAlonhadatHouse($data)
    {
        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'];
        }
        if (isset($data['house_address']) && isset($data['house_number'])) {
            $house = $this->batdongsanRepo->model
                ->where('purpose', $data['purpose'])
                ->where('house_number', $data['house_number']);

            if ($data && !empty($data['project_id'])) {
                $house = $house->where('project_id', $data['project_id']);
            } else {
                $house = $house->where('house_address', $data['house_address']);
            }
            $house = $house->first();

            if ($house) {
                $this->error(config('API.Message.House.DuplicateAddress'));
            }
        }
        $house = $this->batdongsanRepo->create($data);


        return $house;
    }

    public function getHouseDetail($houseId)
    {
        return $this->batdongsanRepo->model->where('house_id', $houseId)->with(['HouseDirection', 'HouseBalconyDirection'])->first();
    }

    public function getHouseImage(&$house)
    {
        $public = $house->public_image;

        $publicImage = new \stdClass();
        if ($public && count($public)) {
            foreach ($public as $index => $imageId) {
                $image = $this->imageRepo->getModelById($imageId);
                $publicImage->$index = $image;
            }
        }

        $image = [
            'public' => $publicImage
        ];

        $house->image = $image;
    }




}

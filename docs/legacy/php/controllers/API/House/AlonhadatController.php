<?php

namespace App\Http\Controllers\API\House;

use App\Http\Controllers\Controller;
use App\Http\Requests\House\HouseCommentRequest;
use App\Http\Requests\House\HouseRequest;
use App\Http\Traits\CustomRequest;
use App\Services\Dictionary\DictionaryService;
use App\Services\House\HouseService;
use App\Services\User\UserService;
use App\Services\House\ImageService;
use App\Services\User\NotificationService;
use App\Services\User\PostAddressService;
use App\Services\User\PostManagerService;
use App\Services\User\PostAddressStatusService;
use App\Services\House\AlonhadatService;
use Illuminate\Http\Request;

class AlonhadatController extends Controller
{
    use CustomRequest;
    private $houseService;
    private $userService;
    private $imageService;
    private $notificationService;
    private $dictionaryService;
    private $postManagerService;
    private $postAddressService;
    private $alonhadatService;

    public function __construct(
        HouseService $houseService,
        UserService $userService,
        ImageService $imageService,
        DictionaryService $dictionaryService,
        NotificationService $notificationService,
        PostManagerService $postManagerService,
        PostAddressStatusService $postAddressStatusService,
        PostAddressService $postAddressService,
        AlonhadatService $alonhadatService
    ) {
        $this->houseService = $houseService;
        $this->userService = $userService;
        $this->imageService = $imageService;
        $this->dictionaryService = $dictionaryService;
        $this->notificationService = $notificationService;
        $this->postManagerService = $postManagerService;
        $this->postAddressStatusService = $postAddressStatusService;
        $this->postAddressService = $postAddressService;
        $this->alonhadatService = $alonhadatService;
    }

    /**
     * Get options for house modification
     * @throws \App\Exceptions\JsonResponse
     */
    public function getHouseOption()
    {
        $this->dictionaryService->getOptions();
        $this->success($this->dictionaryService->options);
    }

  

    /**
     * Add new image file
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addHouseImage(Request $request)
    {
        $input = $this->data($request);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $rotate = isset($input['rotation']) ? $input['rotation'] : 0;
            $data = $this->imageService->addImage($file, $rotate,$input['newName']);

            if (!isset($data['image']) && !$data['image']) {
                $this->error(config('API.Message.MissingFile'));
            }

            $this->success($data['image']);
        }

        $this->error(config('API.Message.MissingFile'));
    }

    public function rotateImage(Request $request)
    {
        $input = $this->data($request);

        if (!isset($input['id']) || !isset($input['rotation'])) {
            $this->error(config('API.Message.InvalidJson'));
        }

        $image = $this->imageService->rotateImage($input['id'], $input['rotation']);

        if ($image) {
            $this->success($image);
        }

        $this->error(config('API.Message.ServerError'));
    }


    /**
     * Get add new house detail
     * @param HouseRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addNewHouse(HouseRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if(!isset($user->alonhadat_name) ||!isset($user->alonhadat_password)){
          $this->error(config('API.Message.Alonhadat.MissingAccount'));
        }
        $existed = $this->alonhadatService->alonhadatRepo->model->where('house_id', $data['id'])->first();
        if($existed){
          $this->error(config('API.Message.Alonhadat.Existed'));
        }
        $data['house_id'] = $data['id'];
        $data['user_id'] = $user->id;
        $data['post'] = 1;
        $initial_house = $this->houseService->houseRepo->model->where('id', $data['id'])->first();
        $data['public_image'] = $initial_house->public_image;
        $data['alonhadat_name'] = $user->alonhadat_name;
        $data['alonhadat_password'] = $user->alonhadat_password;
        $house = $this->alonhadatService->addAlonhadatHouse($data);
        $this->success($house, null, 201);
    }

    /**
     * Update house detail
     * @param HouseRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateHouse(HouseRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if($user->alonhadat_name && $user->alonhadat_password){
            $data['alonhadat_name'] = $user->alonhadat_name;
            $data['alonhadat_password'] = $user->alonhadat_password;
        }
        unset($data['addressPost']);
        $customer = $this->alonhadatService->alonhadatRepo->update($data['id'], $data);
    
        $this->success($customer);
    }

    public function deleteHouse($id){
      $deleteHouse = $this->alonhadatService->alonhadatRepo->model->where('id', $id)->delete();
      $this->success($deleteHouse);
    }
    public function approval(Request $request){
      $data = $this->data($request);
      $res = $this->alonhadatService->alonhadatRepo->model->where('id', $data)->update(['post_approval'=> 1]);
      $this->success($res);
    }
    public function convertData($houses){
      $waitingApproval = [];
      $waiting = [];
      $failed = [];
      $posted = [];
      foreach ($houses as $house) {
        $this->houseService->getHouseImage($house);
        $otherInfo = (object)[];
        $listImage = [];
        $account = (object)[];
        $project = (object)[];

        if(isset($house->projectInfo)){
          $project =  $house->projectInfo->name;
        }
        if(!isset($house->projectInfo)){
          
          $project = null;
        }
        if($house->user->name){
          $house->contactName = $house->user->name;
          $house->phone = $house->user->phone_number;
        }
        if($house->alonhadat_name && $house->alonhadat_password){
          $account->userName = $house->alonhadat_name;
          $account->password = $house->alonhadat_password;
        }
        if($house->initialDescription){
          $house->description = strip_tags($house->initialDescription);
        }
        if($house->street){
          if($house->street == 'Bàu Sen 4' || $house->street == 'Bàu Sen 6'){
            $house->houseType = 'Đường Phan Huy Chú';
          }elseif($house->street == 'Bến Đình 4' || $house->street == 'Bến Đình 6'){
             $house->houseType = 'Đường Lê Văn Lộc';
          }elseif($house->street == 'Chí Linh' || $house->street == 'Chí Linh 6' || $house->street == 'Chí Linh 12' || $house->street == 'Chí Linh 22'){
             $house->houseType = 'Đường Chí Linh 7';
          }elseif($house->street == 'Cống Hộp'){
             $house->houseType = 'Đường Nguyễn Thị Minh Khai';
          }elseif($house->street == 'Đồi Ngọc Tước'){
             $house->houseType = 'Đường Đào Duy Từ';
          }elseif($house->street == 'Kiến Tạo'){
             $house->houseType = 'Đường Mạc Thanh Đạm';
          }elseif($house->street == 'Kiều Thanh Quế'){
             $house->houseType = 'Đường Lê Hồng Phong';
          }elseif($house->street == 'Trùng Dương'){
             $house->houseType = 'Đường Phan Huy Ích';
          }elseif($house->street == 'Bàu Sen 3'){
             $house->houseType = 'Đường Thái Văn Lung';
          }
        }
        if($house->HouseType){
          if($house->HouseType->value == 'Nhà ở riêng lẻ'){
            $house->houseType = 'Nhà trong hẻm';
          }
          elseif($house->HouseType->value == 'Biệt thự - Villa'){
            $house->houseType = 'Biệt thự, nhà liền kề';
          }
          elseif($house->HouseType->value == 'Căn hộ - Chung Cư'){
            $house->houseType = 'Căn hộ chung cư';
          }
          elseif($house->HouseType->value == 'Nhà nghỉ - Khách Sạn'){
            $house->houseType = 'Nhà hàng, khách sạn';
          }
          elseif($house->HouseType->value == 'Nhà nghỉ - Khách Sạn'){
            $house->houseType = 'Nhà hàng, khách sạn';
          }else{
            $house->houseType = $house->HouseType->value;
          }

          
        }
        if($house->juridical){
          if($house->juridical == 1 || $house->juridical == 2 || $house->juridical == 3){
            $house->juridical = 1;
          }else{
             $house->juridical = 2;
          }
        }
        if($house->purpose){
          $house->purpose = $house->purpose + 1;
        }
        $otherInfo->carParking = $house->car_parking;
        $otherInfo->diningRoom = $house->diningRoom;
        //$otherInfo->houseDirection = $house->HouseDirection[0]->direction;
	if (count($house->HouseDirection) > 0 && isset($house->HouseDirection[0]->direction)) {
            $otherInfo->houseDirection = $house->HouseDirection[0]->direction;
      	}
        $otherInfo->floor = $house->floors;
        $otherInfo->width = $house->width;
        $otherInfo->juridical = $house->juridical;
        $otherInfo->kitchen = $house->kitchen;
        $otherInfo->roadInFrontOfHouse = $house->roadInFrontOfHouse;
        $otherInfo->room = $house->room;
        $house->project = $project;
        if(is_array($house->image)&&$house->image['public']){
          foreach ($house->image['public'] as $element ) {
            array_push($listImage, $element->main);
            
          }
          $house->listImage  = $listImage;
        }
        $otherInfo->length = $house->length;
        $house->otherInfo =  $otherInfo;
        $house->account = $account;
        if($house->post == 1 && $house->failed_quantity == 0 && $house->post_approval == 1){
          array_push($waiting, $house);
        }elseif($house->post == 0 && $house->failed_quantity == 0  && $house->post_approval == 1){
          array_push($posted, $house);
        }elseif($house->post == 0 && $house->failed_quantity == 1 && $house->post_approval == 1){
          array_push($failed, $house);
        }elseif($house->post == 1 && $house->failed_quantity == 0 && $house->post_approval == 0){
          array_push($waitingApproval, $house);
        }
      }
      $data = [
            'waitingApproval' =>$waitingApproval,
            'waiting' => $waiting,
            'posted' => $posted,
            'failed'=>$failed
        ];
      return $data;
    }
    public function get()
    {
      $this->getUser($user);
      $houses = $this->alonhadatService->alonhadatRepo->model->select('id','house_id','post','failed_quantity','project_id','user_id','title', 'initialDescription', 'purpose as newType','description','property_type',
        'province as district', 'house_number as houseAddress', 'house_address as street', 'district as ward', 'area', 'into_money as price', 'public_image', 'post_address',
        'type_news as typeNew', 'type_news_value as typeNewValue', 'type_news_day as typeNewDay', 'commission', 'width', 'length', 'alonhadat_name' , 'alonhadat_password',
        'ownership as juridical', 'wide_street as roadInFrontOfHouse', 'floors', 'number_bedroom as room', 'dining_room as diningRoom', 'kitchen', 'terrace', 'car_parking','post_approval')
        ->with(['User', 'ProjectInfo', 'HouseDirection','HouseType'])->get();
       $data = $this->convertData($houses);
      $this->success($data);
    }

    public function getDetail($id)
    {
      //$data = $this->data($request);
      $this->getUser($user);
      $house = $this->alonhadatService->alonhadatRepo->model->where('house_id', $id)->with(['User', 'ProjectInfo', 'HouseDirection','HouseType'])->first();
     
      $this->alonhadatService->getHouseImage($house);
      
      $this->success($house);
    }

    
}

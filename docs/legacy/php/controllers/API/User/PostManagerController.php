<?php

namespace App\Http\Controllers\API\User;


use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\User\PostManagerService;
use App\Services\User\PostAddressStatusService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\House\AlonhadatService;
use App\Services\House\BatdongsanService;
use App\Services\ActionHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostManagerController extends Controller
{
  use CustomRequest;
  private $userService;
  private $postManagerService;
  private $houseService;
  private $actionHistoryService;
  private $postAddressStatusService;
  private $alonhadatService;
  private $batdongsanService;

  public function __construct(
    UserService $userService,
    PostManagerService $postManagerService,
    HouseService $houseService,
    ActionHistoryService $actionHistoryService,
    PostAddressStatusService $postAddressStatusService,
    AlonhadatService $alonhadatService,
    BatdongsanService $batdongsanService
  ) {
    $this->middleware('json_request', [
      'except' => []
    ]);
    $this->userService = $userService;
    $this->postManagerService = $postManagerService;
    $this->houseService = $houseService;
    $this->actionHistoryService  = $actionHistoryService;
    $this->postAddressStatusService = $postAddressStatusService;
    $this->alonhadatService = $alonhadatService;
    $this->batdongsanService = $batdongsanService;
  }


  public function create(Request $request)
  {
    $this->getUser($user);
    $req = $this->data($request);
    $house_id = $req['house_id'];
    $data = $req;
    $existed = $this->postManagerService->postManagerRepo->model->where('user_id', $user->id)->where('house_id', $house_id)->first();
    if ($existed) {
      $this->error(config('API.Message.PostManager.Existed'));
    }
    unset($req['house_id']);
    if (count($req) == 0) {
      $this->error(config('API.Message.PostManager.MustHaveOne'), 400);
    }
    foreach ($data as $key => $value) {
      if ($value != $house_id) {
        $postAddressStatus = $this->postAddressStatusService->postAddressStatusRepo->model->where('user_id', $user->id)->where('channel', $key)->first();
        $this->postManagerService->postManagerRepo->model->create(['house_id' => $house_id, 'user_id' => $user->id, 'channel' => $key, 'link' => $value, 'status_id' => $postAddressStatus->id]);
      }
    }
    $this->success(null);
  }

  public function deletePost(Request $request)
  {
    $this->getUser($user);
    $req = $this->data($request);
    $deletePost = $this->postManagerService->deletePost($req['house_id'], $user->id);
    $this->success($deletePost);
  }

  public function get(Request $request)
  {
    $req = $this->data($request);
    $this->getUser($user);
    $user_id = $user->id;
    if (isset($req['staff']) && $req['staff'] != 0) {
      $user_id = $req['staff'];
    }

    $result = $this->postManagerService->getPostManager($user_id, $req);
    $postQuantity = $this->postManagerService->postManagerRepo->model->where('user_id', $user_id)->count();
    $count = 1;
    if ($result) {
      foreach ($result as $post) {
        if (isset($post)) {
          $action = $this->actionHistoryService->actionHistoryRepo->model->where('model', 'House')->where('model_id', $post['house_id'])
            ->where('column', 'into_money')->where('created_at', ">=", strtotime($post['created_at']))->get();
          $post->action = $action;
          if (isset($req['page'])) {
            $post->ordinal_number =  $result->currentPage() * $result->perPage() - $result->perPage() +  $count;
          } else {
            $post->ordinal_number = $count;
          }
          $count += 1;
        }
      }
      if (count($result) > 0) {
        $result[0]->postQuantity =  $postQuantity;
      }
      $this->success($result);
    }
    $this->error(config('API.Message.PostManager.NotFound'));
  }


  public function getBatdongsanHouseToPost(Request $request)
  {
    $this->getUser($user);
    //if ($user && $user->id === 72) {
    $data = $this->data($request);
    $client = new \GuzzleHttp\Client();
    $houses = $this->batdongsanService->batdongsanRepo->model->where([['post', 1], ['failed_quantity', 0], ['update_status', 0], ['post_approval', 1], ['into_money', '>', 0]])
      ->with(['User', 'ProjectInfo', 'HouseDirection', 'HouseType', 'Ownership'])
      ->select(
        'id',
        'project_id',
        'user_id',
        'title',
        'initialDescription',
        'purpose as newType',
        'description',
        'property_type',
        'province as district',
        'house_number as houseAddress',
        'house_address as street',
        'district as ward',
        'area',
        'into_money as price',
        'public_image',
        'type_news as typeNew',
        'type_news_day as typeNewDay',
        'commission',
        'width',
        'length',
        'batdongsan_name',
        'batdongsan_password',
        'ownership',
        'wide_street as roadInFrontOfHouse',
        'floors',
        'number_bedroom as room',
        'dining_room as diningRoom',
        'number_wc as bathRoom',
        'kitchen',
        'terrace as terraceDirection',
        'car_parking'
      )
      ->get();
    foreach ($houses as $house) {
      $this->houseService->getHouseImage($house);
      $otherInfo = (object)[];
      $listImage = [];
      $account = (object)[];
      $project = (object)[];

      //$account->userName = 'quocbaovungtau';
      //$account->password  = '36luongvancan';
      //$account->userName = 'Minhkha038';
      //$account->password  = 'Tieuholy381998';


      if ($house->user->name) {
        $house->contactName = $house->user->name;
        $house->phone = $house->user->phone_number;
      }
      if ($house->alonhadat_name && $house->alonhadat_password) {
        $account->userName = $house->alonhadat_name;
        $account->password = $house->alonhadat_password;
      }
      if ($house->initialDescription) {
        $house->description = strip_tags($house->initialDescription);
      }
      if (isset($house->projectInfo)) {
        $project =  $house->projectInfo->name;
      }
      if (!isset($house->projectInfo)) {
        $project = null;
      }
      unset($house->project);
      if ($house->street) {
        if ($house->street == 'Bàu Sen 4' || $house->street == 'Bàu Sen 6') {
          $house->houseType = 'Đường Phan Huy Chú';
        } elseif ($house->street == 'Bến Đình 4' || $house->street == 'Bến Đình 6') {
          $house->houseType = 'Đường Lê Văn Lộc';
        } elseif ($house->street == 'Chí Linh' || $house->street == 'Chí Linh 6' || $house->street == 'Chí Linh 12' || $house->street == 'Chí Linh 22') {
          $house->houseType = 'Đường Chí Linh 7';
        } elseif ($house->street == 'Cống Hộp') {
          $house->houseType = 'Đường Nguyễn Thị Minh Khai';
        } elseif ($house->street == 'Đồi Ngọc Tước') {
          $house->houseType = 'Đường Đào Duy Từ';
        } elseif ($house->street == 'Kiến Tạo') {
          $house->houseType = 'Đường Mạc Thanh Đạm';
        } elseif ($house->street == 'Kiều Thanh Quế') {
          $house->houseType = 'Đường Lê Hồng Phong';
        } elseif ($house->street == 'Trùng Dương') {
          $house->houseType = 'Đường Phan Huy Ích';
        } elseif ($house->street == 'Bàu Sen 3') {
          $house->houseType = 'Đường Thái Văn Lung';
        }
      }
      if ($house->HouseType) {
        if ($house->HouseType->value == 'Nhà ở riêng lẻ') {
          $house->houseType = 'Nhà trong hẻm';
        } elseif ($house->HouseType->value == 'Biệt thự - Villa') {
          $house->houseType = 'Biệt thự, nhà liền kề';
        } elseif ($house->HouseType->value == 'Căn hộ - Chung Cư') {
          $house->houseType = 'Căn hộ chung cư';
        } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
          $house->houseType = 'Nhà hàng, khách sạn';
        } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
          $house->houseType = 'Nhà hàng, khách sạn';
        } else {
          $house->houseType = $house->HouseType->value;
        }
      }
      if ($house->Ownership) {
        $house->juridical = $house->Ownership->value;
      }
      if ($house->purpose) {
        $house->purpose = $house->purpose + 1;
      }
      $otherInfo->carParking = $house->car_parking;
      $otherInfo->diningRoom = $house->diningRoom;
      $otherInfo->houseDirection = $house->HouseDirection[0]->direction;
      $otherInfo->floor = $house->floors;
      $otherInfo->width = $house->width;
      $otherInfo->juridical = $house->juridical;
      $otherInfo->kitchen = $house->kitchen;
      $otherInfo->roadInFrontOfHouse = $house->roadInFrontOfHouse;
      $otherInfo->room = $house->room;
      $house->project = $project;
      if (is_array($house->image) && $house->image['public']) {
        foreach ($house->image['public'] as $element) {
          array_push($listImage, $element->main);
        }
        $house->listImage  = $listImage;
      }
      $otherInfo->length = $house->length;
      $house->otherInfo =  $otherInfo;
      $house->account = $account;
    }
    // $response = $client->post('http://13.215.199.180:5000/BatDongSan/register-bybuy-batdongsan', [
    //   'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
    //   'json' => $houses
    // ]);
    // $posts = $response->getBody()->getContents();
    // Log::info(print_r($posts, true));
    // $data = json_decode($response->getBody(), true);
    // Log::info($data['successFull']);
    // if (isset($data['successFull']) && count($data['successFull']) > 0) {
    //   foreach ($data['successFull'] as $id) {
    //     //$alonhadat_response = $this->alonhadatService->alonhadatRepo->model->where('id', $id['id'])->update(['post'=> 0]);
    //     $alonhadat_response = $this->alonhadatService->alonhadatRepo->update($id['id'], ['post' => 0]);
    //     $postAddressStatus = $this->postAddressStatusService->postAddressStatusRepo->model->where('user_id', $alonhadat_response['user_id'])->first();
    //     $this->postManagerService->postManagerRepo->model->create([
    //       'user_id' => $alonhadat_response['user_id'], 'channel' => 'alonhadat.com.vn', 'house_id' => $alonhadat_response['house_id'], 'status_id' => $postAddressStatus['id'],
    //       'link' => $id['link']
    //     ]);
    //   }
    // }
    // if (isset($data['failure']) && count($data['failure']) > 0) {
    //   foreach ($data['failure'] as $id) {
    //     $this->alonhadatService->alonhadatRepo->model->where('id', $id)->update(['failed_quantity' => 1, 'post' => 0]);
    //   }
    // }
    //$res = '72';
    $this->success($houses);
    // } else {
    //   $res = 'No';
    //   $this->success($res);
    // }
  }
  public function getAlonhadatHouseToPost(Request $request)
  {
    $this->getUser($user);
    if ($user && $user->id === 72) {
      $data = $this->data($request);
      $client = new \GuzzleHttp\Client();
      $houses = $this->alonhadatService->alonhadatRepo->model->where([['post', 1], ['failed_quantity', 0], ['update_status', 0], ['into_money', '>', 0]])
        ->with(['User', 'ProjectInfo', 'HouseDirection', 'HouseType'])
        ->select(
          'id',
          'project_id',
          'user_id',
          'title',
          'initialDescription',
          'purpose as newType',
          'description',
          'property_type',
          'province as district',
          'house_number as houseAddress',
          'house_address as street',
          'district as ward',
          'area',
          'into_money as price',
          'public_image',
          'type_news as typeNew',
          'type_news_value as typeNewValue',
          'type_news_day as typeNewDay',
          'commission',
          'width',
          'length',
          'alonhadat_name',
          'alonhadat_password',
          'ownership as juridical',
          'wide_street as roadInFrontOfHouse',
          'floors',
          'number_bedroom as room',
          'dining_room as diningRoom',
          'kitchen',
          'terrace',
          'car_parking'
        )
        ->get();
      foreach ($houses as $house) {
        $this->houseService->getHouseImage($house);
        $otherInfo = (object)[];
        $listImage = [];
        $account = (object)[];
        $project = (object)[];

        //$account->userName = 'quocbaovungtau';
        //$account->password  = '36luongvancan';
        //$account->userName = 'Minhkha038';
        //$account->password  = 'Tieuholy381998';


        if ($house->user->name) {
          $house->contactName = $house->user->name;
          $house->phone = $house->user->phone_number;
        }
        if ($house->alonhadat_name && $house->alonhadat_password) {
          $account->userName = $house->alonhadat_name;
          $account->password = $house->alonhadat_password;
        }
        if ($house->initialDescription) {
          $house->description = strip_tags($house->initialDescription);
        }
        if (isset($house->projectInfo)) {
          $project =  $house->projectInfo->name;
        }
        if (!isset($house->projectInfo)) {
          $project = null;
        }
        unset($house->project);
        if ($house->street) {
          if ($house->street == 'Bàu Sen 4' || $house->street == 'Bàu Sen 6') {
            $house->houseType = 'Đường Phan Huy Chú';
          } elseif ($house->street == 'Bến Đình 4' || $house->street == 'Bến Đình 6') {
            $house->houseType = 'Đường Lê Văn Lộc';
          } elseif ($house->street == 'Chí Linh' || $house->street == 'Chí Linh 6' || $house->street == 'Chí Linh 12' || $house->street == 'Chí Linh 22') {
            $house->houseType = 'Đường Chí Linh 7';
          } elseif ($house->street == 'Cống Hộp') {
            $house->houseType = 'Đường Nguyễn Thị Minh Khai';
          } elseif ($house->street == 'Đồi Ngọc Tước') {
            $house->houseType = 'Đường Đào Duy Từ';
          } elseif ($house->street == 'Kiến Tạo') {
            $house->houseType = 'Đường Mạc Thanh Đạm';
          } elseif ($house->street == 'Kiều Thanh Quế') {
            $house->houseType = 'Đường Lê Hồng Phong';
          } elseif ($house->street == 'Trùng Dương') {
            $house->houseType = 'Đường Phan Huy Ích';
          } elseif ($house->street == 'Bàu Sen 3') {
            $house->houseType = 'Đường Thái Văn Lung';
          }
        }
        if ($house->HouseType) {
          if ($house->HouseType->value == 'Nhà ở riêng lẻ') {
            $house->houseType = 'Nhà trong hẻm';
          } elseif ($house->HouseType->value == 'Biệt thự - Villa') {
            $house->houseType = 'Biệt thự, nhà liền kề';
          } elseif ($house->HouseType->value == 'Căn hộ - Chung Cư') {
            $house->houseType = 'Căn hộ chung cư';
          } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
            $house->houseType = 'Nhà hàng, khách sạn';
          } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
            $house->houseType = 'Nhà hàng, khách sạn';
          } else {
            $house->houseType = $house->HouseType->value;
          }
        }
        if ($house->juridical) {
          if ($house->juridical == 1 || $house->juridical == 2 || $house->juridical == 3) {
            $house->juridical = 1;
          } else {
            $house->juridical = 2;
          }
        }
        if ($house->purpose) {
          $house->purpose = $house->purpose + 1;
        }
        $otherInfo->carParking = $house->car_parking;
	$otherInfo->diningRoom = $house->diningRoom;
	if (!empty($house->HouseDirection->toArray())) {
		$otherInfo->houseDirection = $house->HouseDirection[0]->direction;
	}
        $otherInfo->floor = $house->floors;
        $otherInfo->width = $house->width;
        $otherInfo->juridical = $house->juridical;
        $otherInfo->kitchen = $house->kitchen;
        $otherInfo->roadInFrontOfHouse = $house->roadInFrontOfHouse;
        $otherInfo->room = $house->room;
        $house->project = $project;
        if (is_array($house->image) && $house->image['public']) {
          foreach ($house->image['public'] as $element) {
            array_push($listImage, $element->main);
          }
          $house->listImage  = $listImage;
        }
        $otherInfo->length = $house->length;
        $house->otherInfo =  $otherInfo;
        $house->account = $account;
      }
      //$response = $client->post('http://13.215.199.180:5000/AloNhaDat/register-realestate-alonhadat', [
        //'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        //'json' => $houses
      //]);
      //$posts = $response->getBody()->getContents();
      //Log::info(print_r($posts, true));
      //$data = json_decode($response->getBody(), true);
      //Log::info($data['successFull']);
      //if (isset($data['successFull']) && count($data['successFull']) > 0) {
        //foreach ($data['successFull'] as $id) {
          ////$alonhadat_response = $this->alonhadatService->alonhadatRepo->model->where('id', $id['id'])->update(['post'=> 0]);
          //$alonhadat_response = $this->alonhadatService->alonhadatRepo->update($id['id'], ['post' => 0]);
          //$postAddressStatus = $this->postAddressStatusService->postAddressStatusRepo->model->where('user_id', $alonhadat_response['user_id'])->first();
          
          //$this->postManagerService->postManagerRepo->model->create([
            //'user_id' => $alonhadat_response['user_id'], 'channel' => 'alonhadat.com.vn', 'house_id' => $alonhadat_response['house_id'], 'status_id' => $postAddressStatus['id'],
            //'link' => $id['link']
          //]);
        //}
      //}
      //if (isset($data['failure']) && count($data['failure']) > 0) {
        //foreach ($data['failure'] as $id) {
          //$this->alonhadatService->alonhadatRepo->model->where('id', $id)->update(['failed_quantity' => 1, 'post' => 0]);
        //}
      //}
      $res = '72';
      $this->success($houses);
    } else {
      $res = 'No';
      $this->success($res);
    }
  }

  public function getAlonhadatHouseToUpdate(Request $request)
  {
    $this->getUser($user);
    //if ($user && $user->id === 72) {
    $data = $this->data($request);
    $client = new \GuzzleHttp\Client();
    $house = $this->alonhadatService->alonhadatRepo->model->where([['post', 1], ['failed_quantity', 0], ['update_status', 1], ['post_approval', 1]])
      ->with(['User', 'ProjectInfo', 'HouseDirection', 'HouseType'])
      ->with(['User', 'ProjectInfo', 'HouseDirection', 'HouseType'])
      ->select(
        'id',
        'house_id',
        'project_id',
        'user_id',
        'title',
        'initialDescription',
        'purpose as newType',
        'description',
        'property_type',
        'province as district',
        'house_number as houseAddress',
        'house_address as street',
        'district as ward',
        'area',
        'into_money as price',
        'public_image',
        'type_news as typeNew',
        'type_news_value as typeNewValue',
        'type_news_day as typeNewDay',
        'commission',
        'width',
        'length',
        'alonhadat_name',
        'alonhadat_password',
        'ownership as juridical',
        'wide_street as roadInFrontOfHouse',
        'floors',
        'number_bedroom as room',
        'dining_room as diningRoom',
        'kitchen',
        'terrace',
        'car_parking'
      )
      ->first();
    if (isset($house)) {
      $this->houseService->getPublicHouseImage($house);
      $otherInfo = (object)[];
      $listImage = [];
      $account = (object)[];
      $project = (object)[];
      $house->type = 1;
      //$account->userName = 'quocbaovungtau';
      //$account->password  = '36luongvancan';
      //$account->userName = 'Minhkha038';
      //$account->password  = 'Tieuholy381998';
      $post = $this->postManagerService->postManagerRepo->model->where([['house_id', $house->house_id], ['user_id', $house->user_id], ['channel', 'alonhadat.vn']])->first();
      if ($post) {
        $house->link = $post->link;
      }
      if ($house->user->name) {
        $house->contactName = $house->user->name;
        $house->phone = $house->user->phone_number;
      }
      if ($house->alonhadat_name && $house->alonhadat_password) {
        $account->userName = $house->alonhadat_name;
        $account->password = $house->alonhadat_password;
      }
      if ($house->initialDescription) {
        $house->description = strip_tags($house->initialDescription);
      }
      if (isset($house->projectInfo)) {
        $project =  $house->projectInfo->name;
      }
      if (!isset($house->projectInfo)) {
        $project = null;
      }
      unset($house->project);
      if ($house->street) {
        if ($house->street == 'Bàu Sen 4' || $house->street == 'Bàu Sen 6') {
          $house->houseType = 'Đường Phan Huy Chú';
        } elseif ($house->street == 'Bến Đình 4' || $house->street == 'Bến Đình 6') {
          $house->houseType = 'Đường Lê Văn Lộc';
        } elseif ($house->street == 'Chí Linh' || $house->street == 'Chí Linh 6' || $house->street == 'Chí Linh 12' || $house->street == 'Chí Linh 22') {
          $house->houseType = 'Đường Chí Linh 7';
        } elseif ($house->street == 'Cống Hộp') {
          $house->houseType = 'Đường Nguyễn Thị Minh Khai';
        } elseif ($house->street == 'Đồi Ngọc Tước') {
          $house->houseType = 'Đường Đào Duy Từ';
        } elseif ($house->street == 'Kiến Tạo') {
          $house->houseType = 'Đường Mạc Thanh Đạm';
        } elseif ($house->street == 'Kiều Thanh Quế') {
          $house->houseType = 'Đường Lê Hồng Phong';
        } elseif ($house->street == 'Trùng Dương') {
          $house->houseType = 'Đường Phan Huy Ích';
        } elseif ($house->street == 'Bàu Sen 3') {
          $house->houseType = 'Đường Thái Văn Lung';
        }
      }
      if ($house->HouseType) {
        if ($house->HouseType->value == 'Nhà ở riêng lẻ') {
          $house->houseType = 'Nhà trong hẻm';
        } elseif ($house->HouseType->value == 'Biệt thự - Villa') {
          $house->houseType = 'Biệt thự, nhà liền kề';
        } elseif ($house->HouseType->value == 'Căn hộ - Chung Cư') {
          $house->houseType = 'Căn hộ chung cư';
        } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
          $house->houseType = 'Nhà hàng, khách sạn';
        } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
          $house->houseType = 'Nhà hàng, khách sạn';
        } else {
          $house->houseType = $house->HouseType->value;
        }
      }
      if ($house->juridical) {
        if ($house->juridical == 1 || $house->juridical == 2 || $house->juridical == 3) {
          $house->juridical = 1;
        } else {
          $house->juridical = 2;
        }
      }
      if ($house->purpose) {
        $house->purpose = $house->purpose + 1;
      }
      $otherInfo->carParking = $house->car_parking;
      $otherInfo->diningRoom = $house->diningRoom;
      $otherInfo->houseDirection = $house->HouseDirection[0]->direction;
      $otherInfo->floor = $house->floors;
      $otherInfo->width = $house->width;
      $otherInfo->juridical = $house->juridical;
      $otherInfo->kitchen = $house->kitchen;
      $otherInfo->roadInFrontOfHouse = $house->roadInFrontOfHouse;
      $otherInfo->room = $house->room;
      $house->project = $project;
      if (is_array($house->image) && $house->image['public']) {
        foreach ($house->image['public'] as $element) {
          array_push($listImage, $element->main);
        }
        $house->listImage  = $listImage;
      }
      $otherInfo->length = $house->length;
      $house->otherInfo =  $otherInfo;
      $house->account = $account;

      Log::info(print_r($house->link, true));
      $response = $client->post('http://13.215.199.180:5000/AloNhaDat/edit-alonhadat', [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'json' => $house
      ]);
      $posts = $response->getBody()->getContents();
      Log::info(print_r($posts, true));
      $data = json_decode($response->getBody(), true);
      Log::info($data['successFull']);
      if (isset($data['successFull']) && count($data['successFull']) > 0) {
        foreach ($data['successFull'] as $id) {
          $this->alonhadatService->alonhadatRepo->update($id['id'], ['post' => 0, 'update_status' => 0]);
        }
      }
      if (isset($data['failure']) && count($data['failure']) > 0) {
        foreach ($data['failure'] as $id) {
          $this->alonhadatService->alonhadatRepo->model->where('id', $id)->update(['failed_quantity' => 1, 'post' => 0]);
        }
      }
      //$this->success($house);
    }
    // } else {
    //   $res = '72';
    //   $this->success($res);
    // }


    // }else{
    //   $res = 'No';
    //   $this->success($res);
    // }
  }

  public function deleteAlonhadatPosted(Request $request)
  {
    $client = new \GuzzleHttp\Client();
    $info = (object)[];
    $account = (object)[];
    $data =
      $this->data($request);
    $house_id = $data['houseId'];
    $type = $data['type'];
    $alonhadatHouse = $this->alonhadatService->alonhadatRepo->model->where('house_id', $house_id)->first();
    $post = $this->postManagerService->postManagerRepo->model->where(
      'house_id',
      $alonhadatHouse->house_id
    )->where('user_id', $alonhadatHouse->user_id)->where('channel', 'alonhadat.com.vn')->first();
    $account->userName = $alonhadatHouse->alonhadat_name;
    $account->password =
      $alonhadatHouse->alonhadat_password;
    $info->id = $alonhadatHouse->house_id;
    $info->link = $post->link;
    $info->account = $account;
    $info->type = $type;
    Log::info(print_r($info->type, true));
    $response = $client->post('http://13.215.199.180:5000/AloNhaDat/delete-alonhadat', [
      'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
      'json' => $info
    ]);
    $posts = $response->getBody()->getContents();
    Log::info(print_r($posts, true));
    $this->success($info);
  }


  public function getWaitingList()
  {
    $alonhadatList = $this->alonhadatService->alonhadatRepo->model->where('post', 1)->where('failed_quantity', 0)->with(['House'])->get()->all();
    $batdongsanList = $this->batdongsanService->batdongsanRepo->model->where('post', 1)->where('failed_quantity', 0)->with(['House'])->get()->all();
    $res = array_merge($alonhadatList, (array)$batdongsanList);
    $this->success($res);
  }

  public function getErrorList()
  {
    $alonhadatList = $this->alonhadatService->alonhadatRepo->model->where('post', 0)->where('failed_quantity', 1)->with(['House'])->get()->all();
    $batdongsanList = $this->batdongsanService->batdongsanRepo->model->where('post', 0)->where('failed_quantity', 1)->with(['House'])->get()->all();
    $res = array_merge($alonhadatList, (array)$batdongsanList);
    $this->success($res);
  }
  public function getHistory(Request $request)
  {
    $req = $this->data($request);
    $this->getUser($user);
    $user_id = $user->id;
    $action = $this->actionHistoryService->actionHistoryRepo->model->where('model', 'House')->where('model_id', $req['house_id'])
      ->where(function ($query) {
        $query->where('column', 'into_money');
        $query->orWhere('column', "status");
      })->where('created_at', ">=", $req['created_at'])->with(['User'])->orderBy("created_at", "DESC")->get();
    if (count($action) > 0) {
      foreach ($action as $history) {
        $history->user_name = $history->user->name;
        unset($history->user);
      }
    }
    $this->success($action);
  }

  public function getHouse()
  {
    $house = $this->houseService->houseRepo->model->where('status', 0)->where('public', 1)->where('public_approval', 1)->select('id', 'house_address', 'house_number', 'village', 'district', 'province', 'into_money','length', 'width')->get();
    $this->success($house);
  }

  public function getHouseTransaction()
  {
    $house = $this->houseService->houseRepo->model->where('status', 0)->whereNull('reject_public_condition')->select('id', 'house_address', 'house_number', 'village', 'district', 'province', 'into_money','length', 'width')->get();
    $this->success($house);
  }

  public function update(Request $request)
  {
    $req = $this->data($request);
    $this->getUser($user);
    foreach ($req as $key => $rq) {
      if ($key != 'house_id') {
        $post = $this->postManagerService->postManagerRepo->model->where('house_id', $req['house_id'])->where('user_id', $user->id)->where('channel', $key)->first();
        if ($post) {
          $this->postManagerService->postManagerRepo->model->where('id', $post->id)->update(['link' => $rq]);
        } else {
          $postAddressStatus = $this->postAddressStatusService->postAddressStatusRepo->model->where('user_id', $user->id)->where('channel', $key)->first();
          if ($postAddressStatus) {
            $this->postManagerService->postManagerRepo->model->create(['house_id' => $req['house_id'], 'user_id' => $user->id, 'channel' => $key, 'link' => $rq, 'status_id' => $postAddressStatus->id]);
          } else {
            $postAddressStatus = $this->postAddressStatusService->postAddressStatusRepo->model->create(['user_id' => $user->id, 'channel' => $key]);
            $this->postManagerService->postManagerRepo->model->create(['house_id' => $req['house_id'], 'user_id' => $user->id, 'channel' => $key, 'link' => $rq, 'status_id' => $postAddressStatus->id]);
          }
        }
      }
    }
    $this->success(null);





    //$this->error(config('API.Message.PostManager.UpdateFailed'));

  }
   public function getPostByHouseId(Request $request, $id)
  {
    $houses = $this->alonhadatService->alonhadatRepo->model->where('house_id', $id)
    ->with(['User', 'ProjectInfo', 'HouseDirection', 'HouseType'])
    ->select(
      'id',
      'project_id',
      'user_id',
      'title',
      'initialDescription',
      'purpose as newType',
      'description',
      'property_type',
      'province as district',
      'house_number as houseAddress',
      'house_address as street',
      'district as ward',
      'area',
      'into_money as price',
      'public_image',
      'type_news as typeNew',
      'type_news_value as typeNewValue',
      'type_news_day as typeNewDay',
      'commission',
      'width',
      'length',
      'alonhadat_name',
      'alonhadat_password',
      'ownership as juridical',
      'wide_street as roadInFrontOfHouse',
      'floors',
      'number_bedroom as room',
      'dining_room as diningRoom',
      'kitchen',
      'terrace',
      'car_parking'
    )
    ->get();
  foreach ($houses as $house) {
    $this->houseService->getHouseImage($house);
    $otherInfo = (object)[];
    $listImage = [];
    $account = (object)[];
    $project = (object)[];

    //$account->userName = 'quocbaovungtau';
    //$account->password  = '36luongvancan';
    //$account->userName = 'Minhkha038';
    //$account->password  = 'Tieuholy381998';


    if ($house->user->name) {
      $house->contactName = $house->user->name;
      $house->phone = $house->user->phone_number;
    }
    if ($house->alonhadat_name && $house->alonhadat_password) {
      $account->userName = $house->alonhadat_name;
      $account->password = $house->alonhadat_password;
    }
    if ($house->initialDescription) {
      $house->description = strip_tags($house->initialDescription);
    }
    if (isset($house->projectInfo)) {
      $project =  $house->projectInfo->name;
    }
    if (!isset($house->projectInfo)) {
      $project = null;
    }
    unset($house->project);
    if ($house->street) {
      if ($house->street == 'Bàu Sen 4' || $house->street == 'Bàu Sen 6') {
        $house->houseType = 'Đường Phan Huy Chú';
      } elseif ($house->street == 'Bến Đình 4' || $house->street == 'Bến Đình 6') {
        $house->houseType = 'Đường Lê Văn Lộc';
      } elseif ($house->street == 'Chí Linh' || $house->street == 'Chí Linh 6' || $house->street == 'Chí Linh 12' || $house->street == 'Chí Linh 22') {
        $house->houseType = 'Đường Chí Linh 7';
      } elseif ($house->street == 'Cống Hộp') {
        $house->houseType = 'Đường Nguyễn Thị Minh Khai';
      } elseif ($house->street == 'Đồi Ngọc Tước') {
        $house->houseType = 'Đường Đào Duy Từ';
      } elseif ($house->street == 'Kiến Tạo') {
        $house->houseType = 'Đường Mạc Thanh Đạm';
      } elseif ($house->street == 'Kiều Thanh Quế') {
        $house->houseType = 'Đường Lê Hồng Phong';
      } elseif ($house->street == 'Trùng Dương') {
        $house->houseType = 'Đường Phan Huy Ích';
      } elseif ($house->street == 'Bàu Sen 3') {
        $house->houseType = 'Đường Thái Văn Lung';
      }
    }
    if ($house->HouseType) {
      if ($house->HouseType->value == 'Nhà ở riêng lẻ') {
        $house->houseType = 'Nhà trong hẻm';
      } elseif ($house->HouseType->value == 'Biệt thự - Villa') {
        $house->houseType = 'Biệt thự, nhà liền kề';
      } elseif ($house->HouseType->value == 'Căn hộ - Chung Cư') {
        $house->houseType = 'Căn hộ chung cư';
      } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
        $house->houseType = 'Nhà hàng, khách sạn';
      } elseif ($house->HouseType->value == 'Nhà nghỉ - Khách Sạn') {
        $house->houseType = 'Nhà hàng, khách sạn';
      } else {
        $house->houseType = $house->HouseType->value;
      }
    }
    if ($house->juridical) {
      if ($house->juridical == 1 || $house->juridical == 2 || $house->juridical == 3) {
        $house->juridical = 1;
      } else {
        $house->juridical = 2;
      }
    }
    if ($house->purpose) {
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
    if (is_array($house->image) && $house->image['public']) {
      foreach ($house->image['public'] as $element) {
        array_push($listImage, $element->main);
      }
      $house->listImage  = $listImage;
    }
    $otherInfo->length = $house->length;
    $house->otherInfo =  $otherInfo;
    $house->account = $account;
  }

    $this->success($houses);
  }
}


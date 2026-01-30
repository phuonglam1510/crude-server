<?php

namespace App\Http\Controllers\API\House;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\House\HouseTagService;
use Illuminate\Http\Request;

class HouseTagController extends Controller
{
    use CustomRequest;
    private $tagService;

    public function __construct(
        HouseTagService $houseTagService
    ){
        $this->houseTagService = $houseTagService;

    }

    /**
     * Get options for house modification
     * @throws \App\Exceptions\JsonResponse
     */

    /**
     * Get house detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    
    public function getHouseTag(){
        $this->getUser($user);
        $user_id = $user->id;
        $tag = $this->tagService->tagRepo->model->where('user_id', $user_id)->get();
        $this->success($tag);
    }

    public function create(Request $request){
        $this->getUser($user);
        $user_id = $user->id;
        $req = $this->data($request);
        $houses = $req['houses'];
        foreach ($houses as $house){
            $existed = $this->houseTagService->houseTagRepo->model->where('house_id',$house['id'])->where('user_id', $user_id)->first();
            if($existed){
                $this->houseTagService->houseTagRepo->model->where('id', $existed->id)->update(['tag_id'=>$req['tag_id']]);
            }else{
                $this->houseTagService->houseTagRepo->model->create(['house_id'=>$house['id'], 'tag_id'=>$req['tag_id'], 'user_id'=>$user_id]);
            }
        }
        $this->success([]);
        
    }

    public function deleteTag(Request $request){
        $this->getUser($user);
        $user_id = $user->id;
        $houses = $this->data($request);
        foreach ($houses as $house){
            $this->houseTagService->houseTagRepo->model->where('house_id', $house['id'])->delete();
        }
        $this->success([]);
    }
         
}

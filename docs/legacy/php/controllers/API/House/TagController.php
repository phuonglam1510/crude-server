<?php

namespace App\Http\Controllers\API\House;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\House\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use CustomRequest;
    private $tagService;

    public function __construct(
        TagService $tagService
    ){
        $this->tagService = $tagService;

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
    
    public function getTag(){
        $this->getUser($user);
        $user_id = $user->id;
        $tag = $this->tagService->tagRepo->model->where('user_id', $user_id)->get();
        $this->success($tag);
    }

    public function createTag(Request $request){
        $this->getUser($user);
        $user_id = $user->id;
        $req = $this->data($request);
        $existed =  $this->tagService->tagRepo->model->where('user_id', $user_id)->where('color', $req['color'])->where('name', $req['name'])->first();
        if($existed){
             $this->error(config('API.Message.House.UpdateFailed'), 400);
        }
        $req['user_id'] = $user_id;
        $tag = $this->tagService->tagRepo->create($req);
        $this->success($tag);
        
    }
    public function updateTag(Request $request){
        $this->getUser($user);
        $user_id = $user->id;
        $req = $this->data($request);
        $tagName = $req['tagName'];
        $existed =  $this->tagService->tagRepo->model->where('user_id', $user_id)->where('color', $req['color'])->first();
        if($existed){
            $tag = $this->tagService->tagRepo->model->where('id', $existed->id)->update(['name'=>$tagName]);
        }else{
             $tag = $this->tagService->tagRepo->create(['name'=>$tagName, 'color'=>$req['color'], 'user_id'=>$user_id]);
        }
        if($tag){
            $this->success($tag);
        }else{
             $this->error(config('API.Message.House.UpdateFailed'), 400);
        }
    }
         
}

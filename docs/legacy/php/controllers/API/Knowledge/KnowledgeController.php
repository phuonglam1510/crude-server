<?php

namespace App\Http\Controllers\API\Knowledge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Knowledge\KnowledgeCommentRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Services\House\HouseService;
use App\Services\House\ImageService;
use App\Services\Customer\CustomerService;
use App\Services\Knowledge\KnowledgeService;
use App\Services\Knowledge\TypeService;

use App\Services\Customer\CustomerService as CustomerCustomerService;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $houseService;
    private $questionService;
    private $imageService;
    private $knowledgeService;
    private $typeService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        HouseService $houseService,
        ImageService $imageService,
        KnowledgeService $knowledgeService,
        TypeService $typeService
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->houseService = $houseService;
        $this->imageService = $imageService;
        $this->knowledgeService = $knowledgeService;
        $this->typeService = $typeService;
    }

    public function addKnowledgeImage(Request $request)
    {
        $input = $this->data($request);
        //$this->success($input);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $rotate = isset($input['rotation']) ? $input['rotation'] : 0;
            $data = $this->imageService->addImage(
                $file,
                $rotate,
                $input['newName']
            );

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

        $image = $this->imageService->rotateImage(
            $input['id'],
            $input['rotation']
        );

        if ($image) {
            $this->success($image);
        }

        $this->error(config('API.Message.ServerError'));
    }

    public function addNewKnowledge(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $knowledge = $this->knowledgeService->addKnowledge($data);
        $this->success($knowledge, null, 201);
    }

    public function getKnowledge(Request $request)
    {
        $this->getUser($user);
        // dd($request->query);
        $types = $this->typeService->getType();
        $res = (object) [];
        foreach ($types as $type) {
            $list = $this->knowledgeService->getKnowledgeById($type->id);
            $total = $this->knowledgeService->getTotalKnowledgeById($type->id);
            $this->knowledgeService->getKnowledgeImage($list);
            foreach ($list as $item) {
                $item->key = str_random(6);
            }
            $key = $type->type_name;
            $res->$key = (object) [
                'list' => $list,
                'page' => 1,
                'lastPage' =>
                    $total % 4 == 0 ? $total / 4 : floor($total / 4) + 1,
            ];
        }
        // $this->success($res);
        // $res->page = 0;
        $this->success($res, null, 200);
    }

    public function approve(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }
        $id = $data['id'];
        $data['approval'] = 0;
        $knowledge = $this->knowledgeService->updateKnowledge($id, $data);
        $this->success($knowledge, null, 200);
    }

    public function notApprove(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }
        $id = $data['id'];
        $data['approval'] = 0;
        $knowledge = $this->knowledgeService->updateKnowledge($id, $data);
        $this->success($knowledge, null, 200);
    }

    public function getKnowledgeByTypeId(Request $request, $id)
    {
        $this->getUser($user);
        $knowledge = $this->knowledgeService->getKnowledgeByIdWaiting($id);
        $this->knowledgeService->getKnowledgeImage($knowledge);
        $total = count($knowledge);
        $this->success(['data' => $knowledge, 'total' => $total]);
    }

    public function getKnowledgeWithPage(Request $request, $id)
    {
        // Nhung bai viet da duoc dang
        $this->getUser($user);
        if ((int) $request->query()['page'] === 1) {
            return;
        }
        $page = ((int) $request->query()['page'] - 1) * 4;
        $knowledge = $this->knowledgeService->getKnowledgeByIdWithPage(
            $id,
            $page
        );
        $this->knowledgeService->getKnowledgeImage($knowledge);
        foreach ($knowledge as $item) {
            $item->key = str_random(6);
        }
        $total = count($knowledge);
        // $this->success(['data' => $knowledge, 'total' => $total]);
        $this->success($knowledge, null, 200);
    }

    public function addKnowledgeComment(
        KnowledgeCommentRequest $request,
        $knowledgeId
    ) {
        $this->getUser($user);
        $data = $this->data($request);
        $knowledge = $this->knowledgeService->knowledgeRepo->getModelById(
            $knowledgeId
        );

        $data['knowledge_id'] = $knowledgeId;
        $data['user_id'] = $user->id;

        $comment = $this->knowledgeService->knowledgeCommentRepo->create($data);

        if (!$comment) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($comment);
    }

    public function getKnowledgeComment($knowledgeId)
    {
        $this->getUser($user);

        $knowledge = $this->knowledgeService->knowledgeRepo->getModelById(
            $knowledgeId
        );
        $comments = $this->knowledgeService->getAllComment($user, $knowledgeId);

        $this->success($comments);
    }
    public function updateKnowledge(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $id = $data['id'];
        $find = $this->knowledgeService->knowledgeRepo->getModelById($id);
        
        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $user->id != $find->user_id) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }
        if (isset($data['reset_reason']) && $data['reset_reason']) {
            $data['reason_reject'] = null;
            $data['approval'] = 1;
        }
        $knowledge = $this->knowledgeService->updateKnowledge($id, $data);
        $this->success($knowledge, null, 200);
    }

    public function getKnowledgeNotApproveByUserId(Request $request)
    {
        $this->getUser($user);
        $userId = $user->id;
        $knowledge = $this->knowledgeService->getKnowledgeNotApproveByUserId(
            $userId
        );
        $this->knowledgeService->getKnowledgeImage($knowledge);
        $total = count($knowledge);
        $this->success(['data' => $knowledge, 'total' => $total]);
    }

    public function deleteKnowledge(Request $request, $id)
    {
        $this->getUser($user);
        $knowledge = $this->knowledgeService->deleteKnowledge($id);
        $this->success($knowledge, null, 200);
    }

    public function updateKnowledgeChangeColumn(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        foreach ($data as $item) {

            $id = $item['itemId'];
            $typeId = $item['currentId'];
            $dataUpdate = [
                'type_id' => $typeId,
                'id' => $id,
            ];
            $knowledge = $this->knowledgeService->updateKnowledgeChangeColumn(
                $id, $dataUpdate
            );
        }
        // $id = $data['id'];
        // if (isset($data['reset_reason']) && $data['reset_reason']) {
        //     $data['reason_reject'] = null;
        //     $data['approval'] = 1;
        // }
        // $knowledge = $this->knowledgeService->updateKnowledgeChangeColumn($id, $data);
        $this->success('asd', null, 200);
    }
}


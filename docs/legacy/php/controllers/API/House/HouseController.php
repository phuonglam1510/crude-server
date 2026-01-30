<?php

namespace App\Http\Controllers\API\House;

use App\Http\Controllers\Controller;
use App\Http\Requests\House\HouseCommentRequest;
use App\Http\Requests\House\HouseRequest;
use App\Http\Traits\CustomRequest;
use App\Services\Dictionary\DictionaryService;
use App\Services\House\FileService;
use App\Services\House\HouseService;
use App\Services\User\UserService;
use App\Services\House\ImageService;
use App\Services\User\NotificationService;
use App\Services\User\PostAddressService;
use App\Services\User\PostManagerService;
use App\Services\User\PostAddressStatusService;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    use CustomRequest;
    private $houseService;
    private $userService;
    private $imageService;
    private $fileService;
    private $notificationService;
    private $dictionaryService;
    private $postManagerService;
    private $postAddressService;
    private $postAddressStatusService;

    public function __construct(
        HouseService $houseService,
        UserService $userService,
        ImageService $imageService,
        FileService $fileService,
        DictionaryService $dictionaryService,
        NotificationService $notificationService,
        PostManagerService $postManagerService,
        PostAddressStatusService $postAddressStatusService,
        PostAddressService $postAddressService
    ) {
        $this->houseService = $houseService;
        $this->userService = $userService;
        $this->imageService = $imageService;
        $this->fileService = $fileService;
        $this->dictionaryService = $dictionaryService;
        $this->notificationService = $notificationService;
        $this->postManagerService = $postManagerService;
        $this->postAddressStatusService = $postAddressStatusService;
        $this->postAddressService = $postAddressService;;
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
     * Get house detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getHouseDetail(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $house = $this->houseService->getHouseDetail($data['house_id']);

        if (!$house) {
            $this->error(config('API.Message.House.NotExisted'), 500);
        }

        $this->houseService->getHouseImage($house);
        $this->houseService->getHouseFiles($house);
        $this->houseService->getHouseDraft($house);

        $this->success($house);
    }

    /**
     * Get house list
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getHouse(Request $request)
    {
        $data = $request->all();
        $this->getUser($user);
        $provinceLimit = $user->province;
        $projectLimit = $user->project;
        $houses = $this->houseService->getHouseList($user, $provinceLimit, $projectLimit, $data);
        $total = $houses->total();
        $houses = $houses->items();

        foreach ($houses as $house) {
            $this->houseService->getHouseImage($house);
            $this->houseService->getHouseFiles($house);
            if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                $house->load(['Customer']);
            }

            if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                $customer = $house->customer;
                unset($customer->phone_number);
                $house->customer = $customer;
            }
        }

        $this->success(['data' => $houses, 'total' => $total]);
    }

    public function getHouseV2(Request $request)
    {
        $data = $request->all();
        $this->getUser($user);
        $provinceLimit = $user->province;
        $projectLimit = $user->project;
        $maxPrice = $user->maximum_price;
        $minPrice = $user->minimum_price;
        $houses = $this->houseService->getHouseListV2($user, $provinceLimit, $projectLimit, $maxPrice, $minPrice, $data);
        if (isset($houses["public"])) {
            foreach ($houses["public"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        } elseif (isset($houses["approved"])) {
            foreach ($houses["approved"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        } elseif (isset($houses["waiting"])) {
            foreach ($houses["waiting"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        } elseif (isset($houses["notApproved"])) {
            foreach ($houses["notApproved"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        } elseif (isset($houses["personal"])) {
            foreach ($houses["personal"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        } elseif (isset($houses["suspend"])) {
            foreach ($houses["suspend"]['data'] as $house) {
                $this->houseService->getHouseImage($house);
                $this->houseService->getHouseFiles($house);

                if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
                    $house->load(['Customer']);
                }

                if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
                    $customer = $house->customer;
                    unset($customer->phone_number);
                    $house->customer = $customer;
                }
            }
        }

        // foreach ($houses as $house) {
        //     $this->houseService->getHouseImage($house);
        //     if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id == $user->id) {
        //         $house->load(['Customer']);
        //     }

        //     if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $house->user_id) {
        //         $customer = $house->customer;
        //         unset($customer->phone_number);
        //         $house->customer = $customer;
        //     }
        // }

        $this->success($houses);
    }
    public function getHouseListByUserId(Request $request)
    {
        $this->getUser($user);
        $job_position = $user->job_position;
        $userId = array($user->id);
        $data = $this->data($request);
        if (!empty($data['staff'])) {
            $userId = $data['staff'];
        }
        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $key => $value) {
                if (intval($value) == '0') {
                    $data['staff'][$key] = $user->id;
                }
            }
            $userId = $data['staff'];
        }

        if (isset($data['team']) && $data['team'] == true) {
            $staffList = $this->userService->getAllUserByManagerId($userId, null);
            foreach ($staffList as $staff) {
                array_push($userId, $staff['id']);
            };
        }
        $house = $this->houseService->getHouseListByUserId($userId);
        $this->success(count($house));
    }

    public function countHouse(Request $request)
    {
        $this->getUser($user);
        $job_position = $user->job_position;
        $userId = array($user->id);
        $data = $this->data($request);
        if (!empty($data['staff'])) {
            $userId = $data['staff'];
        }
        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $key => $value) {
                if (intval($value) == '0') {
                    $data['staff'][$key] = $user->id;
                }
            }
            $userId = $data['staff'];
        }

        if (isset($data['team']) && $data['team'] == true) {
            $staffList = $this->userService->getAllUserByManagerId($userId, null);
            foreach ($staffList as $staff) {
                array_push($userId, $staff['id']);
            };
        }
        $house = $this->houseService->countHouse($userId);
        $this->success($house);
    }
    public function getHouseListInWeb(Request $request)
    {
        $data = $this->data($request);
        $house = $this->houseService->countHouse($data['staff']);
        $this->success($house);
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
            $data = $this->imageService->addImage($file, $rotate, $input['newName']);

            if (!isset($data['image']) && !$data['image']) {
                $this->error(config('API.Message.MissingFile'));
            }

            $this->success($data['image']);
        }

        $this->error(config('API.Message.MissingFile'));
    }

    /**
     * Add new file
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addFile(Request $request)
    {
        $input = $this->data($request);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data = $this->fileService->addFile($file);

            if (!isset($data['file']) && !$data['file']) {
                $this->error(config('API.Message.MissingFile'));
            }

            $this->success($data['file']);
        }

        $this->error(config('API.Message.MissingFile'));
    }

    /**
     * Update image file in S3
     * @param Request $request
     * @param $imageId
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateHouseImage(Request $request, $imageId)
    {
        $input = $this->data($request);

        if ($request->hasFile('image') && $imageId) {
            $file = $request->file('image');

            $data = $this->imageService->updateImage($imageId, $file);

            if ($data) {
                $this->success($data);
            } else {
                $this->error(config('API.Message.ServerError'));
            }
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
        $permision = $user->create_product_permission;
        if ($permision == 0) {
            $this->error(config('API.Message.House.ForbiddenAction'), 400);
        }
        $data = $this->data($request);
        // if($request->descriptions!=null&&strlen($data['descriptions'])>160){
        //     $this->error(config('API.Message.House.DescriptionsOverLong'), 400);
        // }
        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }

        $house = $this->houseService->addHouse($data);
        if ($house) {
            $houseQuantity = $this->houseService->getHouseListByUserId($user->id); //mỗi khi update sẽ lấy số lượng bđs hiện có của user đó
            $remain = count($houseQuantity);
            $houseStatistic = $this->houseService->createOrUpdateHouseStatistic($user->id, $remain, $type = 'update');
            if (!$houseStatistic) {
                $this->error(config('API.Message.House.SomethingWrong'), 403);
            }
        }
        $this->success($house, null, 201);
    }

    public function createOrUpdateHouseStatistic()
    {
        $data['size'] = 10000;
        $data['page'] = 1;
        $users = $this->userService->userRepo->model->where('status', 1)->get();
        $channels = $this->postAddressService->postAddressRepo->model->get();

        foreach ($users as $user) {
            $houseQuantity = $this->houseService->getHouseListByUserId(array($user->id));
            $remain = count($houseQuantity);
            // fix update post manager
            foreach ($channels as $channel) {
                $existed = $this->postAddressStatusService->postAddressStatusRepo->model()->where('user_id', $user->id)->where('channel', $channel['channel'])->first();

                if (!$existed) {
                    $this->postAddressStatusService->postAddressStatusRepo->model()->create(['user_id' => $user->id, 'channel' => $channel['channel']]);
                }
            }

            $result = $this->houseService->createOrUpdateHouseStatistic($user->id, $remain, $type = 'update');
        }
        $this->success(null, null, 201);
    }
    public function statisticPost()
    {
        $houses = $this->houseService->houseRepo->model->where('status', 0)->get();
        foreach ($houses as $house) {
            // dd($house->id);
            $post = $this->postManagerService->postManagerRepo->model->where('house_id', $house->id)->count();
            $this->houseService->houseRepo->model->where('id', $house->id)->update(['post_quantity' => $post]);
        }
        $this->success(null, null, 201);
    }
    public function getDetailHouseStatistic(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $userId = array($user->id);

        if (!empty($data['staff'])) {
            $userId = $data['staff'];
        }
        if (!empty($data['staff']) && $data['team'] == true) {
            $users = $this->userService->getAllUserByManagerId($userId, null);
            foreach ($users as $key => $value) {
                array_push($userId, $value->id);
            }
            //dd($user->id);
        }
        if ($data && $data['startAt']) {
            $start_at = $data['startAt'];
            $end_at = $data['endAt'];
            $houseStatistic = $this->houseService->houseStatisticRepo->getDetailHouseStatistic($userId, $start_at, $end_at);
        } else {
            $houseStatistic = $this->houseService->houseStatisticRepo->getDetailHouseStatistic($userId);
        }
        //dd($houseStatistic);
        $this->success($houseStatistic, null, 200);
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

        $customer = $this->houseService->updateHouse($data['house_id'], $data, $user);
        if (!$customer) {
            $this->error(config('API.Message.House.ForbiddenUpdate'), 400);
        }
        $userId =  $customer['user_id'];
        $house = $this->houseService->getHouseListByUserId($userId); //mỗi khi update sẽ lấy số lượng bđs hiện có của user đó
        $remain = count($house);
        if ($request->status != null && $request->status == 1 && $customer['status'] == 1) { //nếu có truyền vào status = 1 và status sau khi update bđs = 1(mở bán->ngưng bán)
            $this->houseService->createOrUpdateHouseStatistic($userId, $remain, $type = 'sale'); //type = sale -> chuyển bđs sang ngưng bán
        }
        if ($request->status == 0 && $customer['status'] == 0) { //nếu truyền status = 0 và bđs sau khi update vẫn ở trạng thái 0 (ngưng bán -> mở bán)
            $this->houseService->createOrUpdateHouseStatistic($userId, $remain, $type = 'update');
        }
        $this->success($customer);
    }

    /**
     * Remove a house
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function removeHouse(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $house = $this->houseService->removeHouse($data['house_id'], $user);

        if (!$house) {
            $this->error(config('API.Message.House.ForbiddenRemove'), 403);
        }

        $this->success($house);
    }

    public function getHousePreview()
    {
        $previews = $this->houseService->getPreviews();
        $this->success($previews->items());
    }

    /**
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function AddHousePreview(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $preview = $this->houseService->addHousePreview($data['house_id']);
        $this->success($preview);
    }

    public function approveHousePreview(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $house = $this->houseService->approveHouse($user, $data['preview_id']);

        if (!$house) {
            $this->error(config('API.Message.House.ForbiddenApprove'), 403);
        }

        $this->success($house);
    }

    public function rejectHousePreview(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if (isset($data['condition'])) {
            $house = $this->houseService->rejectHouse($user, $data['preview_id'], $data['condition']);
        } else {
            $house = $this->houseService->rejectHouse($user, $data['preview_id']);
        }


        if (!$house) {
            $this->error(config('API.Message.House.ForbiddenReject'), 403);
        }

        $this->success($house);
    }

    public function getHouseStreet()
    {
        $streets = $this->houseService->getStreet();
        $this->success($streets);
    }

    public function getListProject(Request $request)
    {
        $data = $request->all();
        $projects = $this->houseService->getProjectList($data);

        $this->success($projects);
    }
    public function getListProjectFromWeb(Request $request)
    {
        $data = $request->all();
        $projects = $this->houseService->getProjectListFromWeb($data);

        $this->success($projects);
    }

    public function searchProjectsByName(Request $request)
    {
        $data = $request->all();
        $projects = $this->houseService->searchProjectsByName($data);
        $this->success($projects);
    }

    public function AddProject(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $project = $this->houseService->addProject($data);
        $this->success($project);
    }

    public function UpdateProject(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $project = $this->houseService->updateProject($data['project_id'], $data, $user);

        if (!$project) {
            $this->error(config('API.Message.House.ForbiddenUpdate'), 403);
        }

        $this->success($project);
    }

    public function RemoveProject(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $house = $this->houseService->removeProject($data['project_id'], $user);

        $this->success($house);
    }

    /**
     * Get house detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getProjectDetail(Request $request)
    {
        $data = $this->data($request);

        $project = $this->houseService->getProjectDetail($data['project_id']);
        $this->houseService->getProjectImage($project);

        $this->success($project);
    }

    public function getProjectBySlug($slug)
    {
        $project = $this->houseService->getProjectDetailBySlug($slug);
        $this->houseService->getProjectImage($project);

        $this->success($project);
    }

    public function getHouseColumnValues(Request $request)
    {
        $data = $this->data($request);

        $values = $this->houseService->getHouseColumnValues($data['column']);

        $this->success($values);
    }

    public function getApprovedHouses(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);

        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }

        $houses = $this->houseService->getApprovedHouses();

        $this->success($houses);
    }

    public function getPendingUpdateHouses(Request $request)
    {
        $this->getUser($user);

        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }

        $houses = $this->houseService->getPendingUpdateHouses();

        $this->success($houses);
    }

    public function staffShareHouse(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);

        if (!isset($data['house_id'])) {
            $this->error(config('API.Message.MissingHouse'));
        }
    }

    public function getPendingPublic()
    {
        $this->getUser($user);
        $houses = $this->houseService->getPendingPublicHouse($user);

        if (is_array($houses)) {
            $this->success($houses);
        }

        $this->error(config('API.Message.House.ForbiddenUpdate'), 403);
    }

    /**
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function adminApprovePublicHouse(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);

        if (!isset($data['house_id'])) {
            $this->error(config('API.Message.MissingHouse'));
        }

        $house = $this->houseService->approvePublicHouse($user, $data['house_id']);

        if (!$house) {
            $this->error(config('API.Message.House.ForbiddenUpdate'), 403);
        }

        $this->success($house);
    }

    public function adminRejectPublicHouse(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);

        if (!isset($data['house_id'])) {
            $this->error(config('API.Message.MissingHouse'));
        }

        if (isset($data['condition'])) {
            $house = $this->houseService->rejectPublicHouse($user, $data['house_id'], $data['condition']);
        } else {
            $house = $this->houseService->rejectPublicHouse($user, $data['house_id']);
        }
        if (!$house) {
            $this->error(config('API.Message.House.ForbiddenUpdate'), 403);
        }

        $this->success($house);
    }

    public function getTopViewedHouses(Request $request)
    {
        $data = $this->data($request);
        $this->getUser($user);

        $houses = $this->houseService->getTopViewedHouses($user, $data['size']);


        $this->success($houses);
    }

    public function getHouseComment($houseId)
    {
        $this->getUser($user);

        $house = $this->houseService->houseRepo->getModelById($houseId);

        if ($house->public_approval == 0) {
            if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $house->user_id !== $user->id) {
                $this->error(config('API.Message.Forbidden'));
            }
        }

        $comments = $this->houseService->getAllComment($user, $houseId);

        $this->success($comments);
    }

    public function getAllComments()
    {
        $this->getUser($user);

        $houses = $this->houseService->getAllComments($user);

        $total = $houses->total();
        $houses = $houses->items();

        foreach ($houses as $house) {
            $this->houseService->getHouseImage($house);
        }

        $this->success(['data' => $houses, 'total' => $total]);
    }

    public function addHouseComment(HouseCommentRequest $request, $houseId)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $house = $this->houseService->houseRepo->getModelById($houseId);

        if ($house->public_approval == 0) {
            if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')  && $house->user_id !== $user->id) {
                $this->error(config('API.Message.Forbidden'));
            }
        }


        $data['house_id'] = $houseId;
        $data['user_id'] = $user->id;

        $comment = $this->houseService->houseCommentRepo->create($data);

        if (!$comment) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($comment);
    }

    public function commentStatus($commentId)
    {
        $this->getUser($user);
        $comment = $this->houseService->houseCommentRepo->getModelById($commentId);
        if (!$comment) {
            $this->error(config('API.Message.ServerError'));
        }

        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $comment->user_id !== $user->id) {
            $this->error(config('API.Message.Forbidden'));
        }

        $comment = $this->houseService->houseCommentRepo->modifyStatus($commentId);
        if (!$comment) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($comment);
    }

    public function getHouseImageZip($houseId)
    {
        $houseZipPath = $this->imageService->zipImageFolder($houseId);

        if (!$houseZipPath) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success([
            'public' => $houseZipPath . '/public.zip',
            'internal' => $houseZipPath . '/internal.zip'
        ]);
    }

    public function getMultipleHousesImageZip(Request $request)
    {
        $data = $this->data($request);
        $houseIds = explode(',', $data['houseIds']);

        $zipPath = $this->imageService->zipImageFolders($houseIds);

        if (!$zipPath) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($zipPath);
    }

    public function addHouseLike($houseId)
    {
        $this->getUser($user);
        $house = $this->houseService->houseRepo->getModelById($houseId);
        if (!$house) {
            $this->error(config('API.Message.ServerError'));
        }

        $like = $this->houseService->houseLikeRepo->modifyLike($houseId, $user->id);

        if (!$like) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success(true);
    }
    public function getHouseSoldByUseId(Request $request)
    {
        $this->getUser($user);
        $userId = $user->id;
        $data = $this->data($request);
        $startTime = $data['startTime'];
        $endTime = $data['endTime'];

        $startTime = strtotime($startTime, $baseTimestamp = null);
        $endTime = strtotime($endTime, $baseTimestamp = null);
        $sold = $this->houseService->getHouseSold($userId, $startTime, $endTime);
        $this->success($sold);
    }

    public function getCustomerContacts(Request $request)
    {

        $contacts = $this->houseService->getCustomerContacts();

        $this->success(['total' => $contacts->total(), 'data' => $contacts->items()]);
    }

    public function updateCustomerContactStatus(Request $request, $id)
    {
        $this->getUser($user);
        $userId = $user->id;
        $data = $this->data($request);

        if (!isset($data['status'])) {
            $this->error(config('API.Message.InvalidJson'));
        }
        $status = $data['status'];

        $contact = $this->houseService->updateCustomerContactStatus($id, $status, $userId);

        $this->success($contact);
    }

    public function updateHousePendingChanges(Request $request, $id)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if ($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin')) {
            $this->error(config('API.Message.Forbidden'));
        }

        if (!isset($data['status'])) {
            $this->error(config('API.Message.InvalidJson'));
        }
        $status = $data['status'];

        $house = $this->houseService->updateHousePendingChanges($id, $status, $user);

        $this->success($house);
    }
}

<?php

namespace App\Services\House;

use App\Http\Traits\CustomResponse;
use App\Repositories\House\HouseBalconyDirectionRepository;
use App\Repositories\House\HouseLikeRepository;
use App\Repositories\House\HouseCommentRepository;
use App\Repositories\House\HouseDirectionRepository;
use App\Repositories\House\HousePreviewRepository;
use App\Repositories\House\HouseRepository;
use App\Repositories\House\ProjectRepository;
use App\Repositories\House\ImageRepository;
use App\Repositories\House\StreetRepository;
use App\Repositories\House\HouseStatisticRepository;
use App\Repositories\House\HouseTagRepository;
use App\Repositories\House\CustomerContactRepository;
use App\Repositories\User\PostManagerRepository;
use App\Services\BaseService;
use App\Helpers\WebCacheHelper;
use App\Repositories\House\FileRepository;
use App\Repositories\House\HouseDraftRepository;

class HouseService extends BaseService
{
    use CustomResponse;
    public $options;
    public $projectRepo;
    public $houseRepo;
    public $houseDraftRepo;
    public $houseDirectionRepo;
    public $imageRepo;
    public $streetRepo;
    public $housePreviewRepo;
    public $houseCommentRepo;
    public $houseBalconyDirectionRepo;
    public $houseStatisticRepo;
    public $postManagerRepo;
    public $houseTagRepo;
    public $webCacheHelper;
    public $houseLikeRepo;
    public $customerContactRepo;
    public $fileRepo;
    private $houseListSelection;

    public function __construct(
        HouseRepository $houseRepository,
        HouseDraftRepository $houseDraftRepository,
        HouseDirectionRepository $houseDirectionRepository,
        ImageRepository $imageRepository,
        FileRepository $fileRepository,
        ProjectRepository $projectRepository,
        StreetRepository $streetRepository,
        HousePreviewRepository $housePreviewRepository,
        HouseCommentRepository $houseCommentRepository,
        HouseLikeRepository $houseLikeRepository,
        HouseBalconyDirectionRepository $houseBalconyDirectionRepository,
        HouseStatisticRepository $houseStatisticRepository,
        PostManagerRepository $postManagerRepository,
        HouseTagRepository $houseTagRepository,
        CustomerContactRepository $customerContactRepository
    ) {
        parent::__construct();
        $this->houseRepo = $houseRepository;
        $this->houseDraftRepo = $houseDraftRepository;
        $this->houseDirectionRepo = $houseDirectionRepository;
        $this->projectRepo = $projectRepository;
        $this->imageRepo = $imageRepository;
        $this->fileRepo = $fileRepository;
        $this->streetRepo = $streetRepository;
        $this->housePreviewRepo = $housePreviewRepository;
        $this->houseCommentRepo = $houseCommentRepository;
        $this->houseLikeRepo = $houseLikeRepository;
        $this->houseBalconyDirectionRepo = $houseBalconyDirectionRepository;
        $this->houseStatisticRepo = $houseStatisticRepository;
        $this->postManagerRepo = $postManagerRepository;
        $this->houseTagRepo = $houseTagRepository;
        $this->customerContactRepo = $customerContactRepository;
        $this->webCacheHelper = new WebCacheHelper();
        $this->houseListSelection = [
            'id',
            'purpose',
            'into_money',
            'project_id',
            'house_number',
            'public_approval',
            'web',
            'block_section',
            'floor_lot',
            'district',
            'property_type',
            'house_type',
            'area',
            'ownership',
            'floors',
            'balcony_direction',
            'slug',
            'postQuantity',
            'internalDescription',
            'reject_public_condition',
            'end_open',
            'total_view',
            'user_id',
            'customer_id',
            'brokerage_rate',
            'number_bedroom',
            'number_wc',
            'status',
            'recommend_quantity',
            'seen_quantity',
            'approve',
            'public',
            'reject_web_condition',
            'street_type',
            'wide_street',
            'floor_area',
            'created_at',
            'updated_at',
            'house_address',
            'public_image',
            'internal_image',
            'description',
            'title',
            'province',
            'width',
            'length'
        ];
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
            'ownership' => config('API.Ownership'),
        ];
    }

    private function addFilterToQuery($query, $input, $user)
    {
        if ($input && !empty($input['filters'])) {
            foreach ($input['filters'] as $filter) {
                if ($filter['column'] === 'address') {
                    $query->whereRaw(
                        'concat(house_number, \' \', house_address) like \'%' .
                            $filter['values'][0] .
                            '%\''
                    );
                } elseif ($filter['column'] === 'district_province') {
                    $query->whereRaw('province = ?', $filter['values'][0]);
                    if (!empty($filter['values'][1])) {
                        $query->whereRaw(
                            'district in  (\'' .
                                join('\',\'', $filter['values'][1]) .
                                '\')'
                        );
                    }
                } elseif ($filter['column'] === 'direction') {
                    $query->whereRaw(
                        'id in (select house_id from house_direction where direction in (\'' .
                            join('\',\'', $filter['values']) .
                            '\'))'
                    );
                } elseif ($filter['column'] === 'balcony_direction') {
                    $query->whereRaw(
                        'id in (select house_id from house_balcony_direction where balcony in (\'' .
                            join('\',\'', $filter['values']) .
                            '\'))'
                    );
                } elseif ($filter['column'] === 'user_name') {
                    $query->whereRaw(
                        'user_id in (select id from users where name like ?)',
                        '%' . $filter['values'][0] . '%'
                    );
                } elseif ($filter['column'] === 'project_name') {
                    //$query->whereRaw('project_id in (select id from projects where name like ?)', '%' . $filter['values'][0] . '%');
                    $query->whereIn('project_id', $filter['values']);
                } elseif ($filter['column'] === 'width') {
                    $query
                        ->where('width', '>=', $filter['values'][0])
                        ->where('width', '<=', $filter['values'][1]);
                } elseif ($filter['column'] === 'length') {
                    $query
                        ->where('length', '>=', $filter['values'][0])
                        ->where('length', '<=', $filter['values'][1]);
                } elseif ($filter['column'] === 'area') {
                    $query
                        ->where('area', '>=', $filter['values'][0])
                        ->where('area', '<=', $filter['values'][1]);
                } elseif ($filter['column'] === 'customer_name') {
                    $query->whereRaw(
                        'customer_id in (select id from customer where name like ?)',
                        '%' . $filter['values'][0] . '%'
                    );
                } elseif ($filter['column'] === 'house_tag') {
                    $values_filter = $filter['values'];
                    $house_id = $this->houseTagRepo->model
                        ->whereIn('tag_id', $values_filter)
                        ->where('user_id', $user->id)
                        ->select('house_id')
                        ->get();
                    $query = $this->houseRepo->model
                        ->whereIn('id', $house_id)
                        ->with([
                            'Project:id,name',
                            'User:id,name',
                            'HouseDirection:id,house_id,direction',
                            'HouseBalconyDirection:id,house_id,balcony',
                            'HouseTag.Tag',
                        ])
                        ->with([
                            'HouseTag' => function ($query) use ($user) {
                                $query->where('user_id', $user->id);
                            },
                        ]);
                } else {
                    if ($filter['operation'] == 'in') {
                        $query->whereIn($filter['column'], $filter['values']);
                    } elseif ($filter['operation'] == 'between') {
                        $query->whereBetween(
                            $filter['column'],
                            $filter['values']
                        );
                    } elseif ($filter['operation'] == 'like') {
                        $query->where(
                            $filter['column'],
                            'like',
                            '%' . $filter['values'][0] . '%'
                        );
                    } else {
                        $query->where(
                            $filter['column'],
                            $filter['operation'],
                            $filter['values']
                        );
                    }
                }
            }
        }
        return $query;
    }

    private function addSearchToQuery($query, $input)
    {
        if ($input && !empty($input['search'])) {
            // $query->leftJoin('house_search', 'house_search.house_id', '=', 'houses.id');
            $query->whereRaw(
                'id in (select house_id from house_search where search like \'%' .
                    $input['search'] .
                    '%\')'
            );
        }

        return $query;
    }

    private function addLabelFilterToQuery($query, $input)
    {
        if ($input && !empty($input['label_id'])) {
            $query->whereRaw(
                'id in (select house_id from house_label where label_id = ' .
                    $input['label_id'] .
                    ')'
            );
        }

        return $query;
    }

    private function addSortToQuery($query, $input)
    {
        if ($input && !empty($input['column'])) {
            $query->orderBy($input['column'], $input['direction']);
        }
        return $query;
    }

    private function addModeFilterToQuery($query, $input, $user)
    {
        $mode = $input['mode'];
        $isAdminUser = $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin');

        if (str_starts_with($mode, 'private')) {
            if ($isAdminUser) {
                // Admin can see all private houses that not public
                $query->where(function ($query) {
                    $query->where('public', 0)
                        ->orWhere('public_approval', 0);
                });
                return $query;
            }


            $query->where('user_id', $user->id);

            if ($mode === "private_approved") {
                $query
                    ->where('public', 1)
                    ->where('public_approval', 1);
            }
            if ($mode === "private_pending") {
                $query
                    ->where('public', 1)
                    ->where('public_approval', 0);
            }
            if ($mode === "private_not_submited") {
                $query
                    ->where('public', 0)
                    ->where('public_approval', 0);
            }
        }
        if ($mode == 'public') {
            $query->where('public', 1)->where('public_approval', 1);
        }

        if ($mode == 'public_pending') {
            if (!$isAdminUser) {
                $query->where('user_id', $user->id);
            }
            $query->where('public', 1)->where('public_approval', 0);
        }

        if ($mode == 'inactive') {
            if (!$isAdminUser) {
                $query->where('user_id', $user->id);
            }
            $query->where('status', 1);
        }

        if ($mode == 'web') {
            $query->where('web', 1)->where('web_approval', 1);
        }

        if ($mode == 'web_pending') {
            if (!$isAdminUser) {
                $query->where('user_id', $user->id);
            }
            $query->where('web', 1)->where('web_approval', 0);
        }

        return $query;
    }

    public function getHouseList(
        $user,
        $provinceLimit = [],
        $projectLimit = [],
        $sort = [],
        $paginate = true
    ) {
        $query = $this->houseRepo->model
            ->query()
            ->select(...$this->houseListSelection)
            ->with([
                'Project:id,name',
                'User:id,name,phone_number,role,profile_picture',
                'HouseDirection:id,house_id,direction',
                'HouseBalconyDirection:id,house_id,balcony',
                'HouseTag.Tag',
                'User.Avatar'
            ])
            ->with([
                'HouseTag' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ]);

        if (!empty($projectLimit)) {
            $query->whereIn('project_id', $projectLimit);
        }
        if (!empty($provinceLimit)) {
            $query->whereIn('province', $provinceLimit);
        }

        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            if ($sort && !empty($sort['project_id'])) {
                $query->where('project_id', $sort['project_id']);
            }

            $query = $this->addFilterToQuery($query, $sort, $user);
            $query = $this->addModeFilterToQuery($query, $sort, $user);
            $query = $this->addSearchToQuery($query, $sort);
            $query = $this->addSortToQuery($query, $sort);
            $query = $this->addLabelFilterToQuery($query, $sort);
            $query
                ->with(['Customer:id,user_id,name'])
                ->withCount(['Comments'])
                ->orderBy('updated_at', 'DESC');

            if ($paginate) {
                $houses = $query->paginate($this->size);
            } else {
                $houses = $query->get();
            }
            return $houses;
        }

        if ($sort && !empty($sort['project_id'])) {
            $query->where('project_id', $sort['project_id']);
        }

        $query = $this->addFilterToQuery($query, $sort, $user);
        $query = $this->addModeFilterToQuery($query, $sort, $user);
        $query = $this->addSearchToQuery($query, $sort);
        $query = $this->addSortToQuery($query, $sort);
        $houses = $query
            ->orderBy('updated_at', 'DESC')
            ->withCount([
                'Comments' => function ($query) use ($user) {
                    $query->where('status', '=', 0);
                    $query->orWhere('user_id', '=', $user->id);
                },
            ])
            ->paginate($this->size);

        foreach ($houses as $house) {
            $postQuantity = $this->postManagerRepo->model
                ->where('house_id', $house->id)
                ->groupBy('user_id')
                ->pluck('user_id')
                ->toArray();
            if (count($postQuantity) > 0) {
                $house->postQuantity = count($postQuantity);
            } else {
                $house->postQuantity = 0;
            }
        }
        return $houses;
    }

    public function getHouseListV2(
        $user,
        $provinceLimit = [],
        $projectLimit = [],
        $maxPrice = null,
        $minPrice = null,
        $sort = [],
        $paginate = true
    ) {
        $query = $this->houseRepo->model
            ->query()
            ->select(
                'id',
                'purpose',
                'into_money',
                'project_id',
                'house_number',
                'public_approval',
                'web',
                'block_section',
                'floor_lot',
                'district',
                'property_type',
                'house_type',
                'area',
                'ownership',
                'floors',
                'balcony_direction',
                'slug',
                'postQuantity',
                'internalDescription',
                'reject_public_condition',
                'end_open',
                'total_view',
                'user_id',
                'customer_id',
                'brokerage_rate',
                'number_bedroom',
                'status',
                'recommend_quantity',
                'seen_quantity',
                'approve',
                'public',
                'reject_web_condition',
                'street_type',
                'wide_street',
                'floor_area',
                'created_at',
                'updated_at',
                'house_address',
                'public_image',
                'internal_image',
                'description',
                'province',
                'width',
                'length',
                'reason_stop_selling',
                'descriptions',
                'key_word',
                'title',
                'number_wc',
                'city_id',
                'file_ids'
            )
            ->with([
                'Project:id,name',
                'User:id,name',
                'HouseDirection:id,house_id,direction',
                'HouseBalconyDirection:id,house_id,balcony',
                'HouseTag.Tag',
                'City'
            ])
            ->with([
                'HouseTag' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ]);

        if (!empty($projectLimit)) {
            $query->whereIn('project_id', $projectLimit);
        }
        if (!empty($provinceLimit)) {
            $query->whereIn('province', $provinceLimit);
        }
        if ($maxPrice != null) {
            $query->where('into_money', '<=', $maxPrice);
        }
        if ($minPrice != null) {
            $query->where('into_money', '>=', $minPrice);
        }

        //public_approve = 1 && public == 1 => đã được duyệt cộng đồng
        //public_approve = 0 && public == 1 => đang chờ duyệt cộng đồng
        //public == 0 ngưng chia sẻ cộng đồng
        //$query = $this->houseRepo->model->query()->where('public_approval','=',1)->with(['Project', 'User', 'HouseDirection', 'HouseBalconyDirection']);
        // if (isset($sort['status']) && ($sort['status'] == 0 || $sort['status'] == 1)) {
        //     $query->where('status', $sort['status']); // get data theo status (mở-ngưng bán)
        // }
        if ($sort && !empty($sort['project_id'])) {
            $query->where('project_id', $sort['project_id']);
        }
        // cộng đồng
        if ($sort['fetchType'] == 'publicList') {
            $public = clone $query;

            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $public = $public->where('public_approval', 1);
            }
            $public = $this->addFilterToQuery($public, $sort, $user);
            $public = $this->addSearchToQuery($public, $sort);
            $public = $this->addLabelFilterToQuery($public, $sort);
            $public = $this->addSortToQuery($public, $sort);
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $public = $public
                        ->where('public', 1)
                        ->whereIn('status', [0, 2])
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $public = $public
                        ->where('public', 1)
                        ->whereIn('status', [0, 2])
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $public = $public
                    ->where('public', 1)
                    ->whereIn('status', [0, 2])
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            //$query = $this->addSortToQuery($query, $sort);
            if ($paginate) {
                $public = $public->paginate($this->size);
            } else {
                $public = $public->get();
            }
            return [
                'public' => [
                    'data' => $public->items(),
                    'total' => $public->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'approved') {
            $approved = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $approved = $approved
                        ->where('public', 1)
                        ->where('public_approval', 1)
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $approved = $approved
                        ->where('public', 1)
                        ->where('public_approval', 1)
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $approved = $approved
                    ->where('public', 1)
                    ->where('public_approval', 1)
                    ->where('status', 0)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $approved = $approved->where('user_id', $user->id);
            }
            $approved = $this->addFilterToQuery($approved, $sort, $user);
            $approved = $this->addSearchToQuery($approved, $sort);
            $approved = $this->addSortToQuery($approved, $sort);
            $approved = $this->addLabelFilterToQuery($approved, $sort);

            if ($paginate) {
                $approved = $approved->paginate($this->size);
            } else {
                $approved = $approved->get();
            }
            return [
                'approved' => [
                    'data' => $approved->items(),
                    'total' => $approved->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'waitingApproval') {
            $waiting = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $waiting = $waiting
                        ->where('public', 1)
                        ->where('public_approval', 0)
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $waiting = $waiting
                        ->where('public', 1)
                        ->where('public_approval', 0)
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $waiting = $waiting
                    ->where('public', 1)
                    ->where('public_approval', 0)
                    ->where('status', 0)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            // $waiting = $waiting
            //     ->where('public', 1)
            //     ->where('public_approval', 0)
            //     ->where('status', 0)
            //     ->with(['Customer:id,user_id,name'])
            //     ->withCount(['Comments'])
            //     ->orderBy('updated_at', 'DESC');
            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $waiting = $waiting->where('user_id', $user->id);
            }
            $waiting = $this->addFilterToQuery($waiting, $sort, $user);
            $waiting = $this->addSearchToQuery($waiting, $sort);
            $waiting = $this->addSortToQuery($waiting, $sort);
            $waiting = $this->addLabelFilterToQuery($waiting, $sort);

            if ($paginate) {
                $waiting = $waiting->paginate($this->size);
            } else {
                $waiting = $waiting->get();
            }
            return [
                'waiting' => [
                    'data' => $waiting->items(),
                    'total' => $waiting->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'notApproved') {
            $notApproved = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $notApproved = $notApproved
                        ->where('public', 0)
                        ->where('public_approval', 0)
                        ->whereNotNull('reject_public_condition')
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $notApproved = $notApproved
                        ->where('public', 0)
                        ->where('public_approval', 0)
                        ->whereNotNull('reject_public_condition')
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $notApproved = $notApproved
                    ->where('public', 0)
                    ->where('public_approval', 0)
                    ->whereNotNull('reject_public_condition')
                    ->where('status', 0)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            // $notApproved = $notApproved
            //     ->where('public', 0)
            //     ->where('public_approval', 0)
            //     ->whereNotNull('reject_public_condition')
            //     ->where('status', 0)
            //     ->with(['Customer:id,user_id,name'])
            //     ->withCount(['Comments'])
            //     ->orderBy('updated_at', 'DESC');
            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $notApproved = $notApproved->where('user_id', $user->id);
            }
            $notApproved = $this->addFilterToQuery($notApproved, $sort, $user);
            $notApproved = $this->addSearchToQuery($notApproved, $sort);
            $notApproved = $this->addSortToQuery($notApproved, $sort);
            $notApproved = $this->addLabelFilterToQuery($notApproved, $sort);

            if ($paginate) {
                $notApproved = $notApproved->paginate($this->size);
            } else {
                $notApproved = $notApproved->get();
            }
            return [
                'notApproved' => [
                    'data' => $notApproved->items(),
                    'total' => $notApproved->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'personalList') {
            $personal = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $personal = $personal
                        ->where('public', 0)
                        ->where(function ($query) {
                            $query
                                ->where('public_approval', 0)
                                ->orWhereNull('public_approval');
                        })
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $personal = $personal
                        ->where('public', 0)
                        ->where(function ($query) {
                            $query
                                ->where('public_approval', 0)
                                ->orWhereNull('public_approval');
                        })
                        ->where('status', 0)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $personal = $personal
                    ->where('public', 0)
                    ->where(function ($query) {
                        $query
                            ->where('public_approval', 0)
                            ->orWhereNull('public_approval');
                    })
                    ->where('status', 0)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            // $personal = $personal
            //     ->where('public', 0)
            //     ->where(function ($query) {
            //         $query
            //             ->where('public_approval', 0)
            //             ->orWhereNull('public_approval');
            //     })
            //     ->where('status', 0)
            //     ->with(['Customer:id,user_id,name'])
            //     ->withCount(['Comments'])
            //     ->orderBy('updated_at', 'DESC');
            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $personal = $personal->where('user_id', $user->id);
            }
            $personal = $this->addFilterToQuery($personal, $sort, $user);
            $personal = $this->addSearchToQuery($personal, $sort);
            $personal = $this->addSortToQuery($personal, $sort);
            $personal = $this->addLabelFilterToQuery($personal, $sort);

            if ($paginate) {
                $personal = $personal->paginate($this->size);
            } else {
                $personal = $personal->get();
            }
            return [
                'personal' => [
                    'data' => $personal->items(),
                    'total' => $personal->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'suspend') {
            $suspendList = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $suspendList = $suspendList
                        ->where('status', 1)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $suspendList = $suspendList
                        ->where('status', 1)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $suspendList = $suspendList
                    ->where('status', 1)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            // $suspendList = $suspendList
            //     ->where('status', 1)
            //     ->with(['Customer:id,user_id,name'])
            //     ->withCount(['Comments'])
            //     ->orderBy('updated_at', 'DESC');
            if (
                $user->role !== config('API.Constant.Role.SuperAdmin') &&
                $user->role !== config('API.Constant.Role.Admin')
            ) {
                $suspendList = $suspendList->where('user_id', $user->id);
            }
            $suspendList = $this->addFilterToQuery($suspendList, $sort, $user);
            $suspendList = $this->addSearchToQuery($suspendList, $sort);
            $suspendList = $this->addSortToQuery($suspendList, $sort);
            $suspendList = $this->addLabelFilterToQuery($suspendList, $sort);

            if ($paginate) {
                $suspendList = $suspendList->paginate($this->size);
            } else {
                $suspendList = $suspendList->get();
            }
            return [
                'suspend' => [
                    'data' => $suspendList->items(),
                    'total' => $suspendList->total(),
                ],
            ];
        } elseif ($sort['fetchType'] == 'suspendPublic') {

            $suspendPublicList = clone $query;
            if (!empty($sort['column'])) {
                if (
                    $sort['column'] === 'into_money' &&
                    $sort['direction'] === 'asc'
                ) {
                    $suspendPublicList = $suspendPublicList
                        ->where('status', 1)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'ASC');
                } else {
                    $suspendPublicList = $suspendPublicList
                        ->where('status', 1)
                        ->with(['Customer:id,user_id,name'])
                        ->withCount(['Comments'])
                        ->orderBy('into_money', 'DESC');
                }
            } else {
                $suspendPublicList = $suspendPublicList
                    ->where('status', 1)
                    ->with(['Customer:id,user_id,name'])
                    ->withCount(['Comments'])
                    ->orderBy('updated_at', 'DESC');
            }
            // $suspendPublicList = $suspendPublicList
            //     ->where('status', 1)
            //     ->with(['Customer:id,user_id,name'])
            //     ->withCount(['Comments'])
            //     ->orderBy('updated_at', 'DESC');

            $suspendPublicList = $this->addFilterToQuery($suspendPublicList, $sort, $user);
            $suspendPublicList = $this->addSearchToQuery($suspendPublicList, $sort);
            $suspendPublicList = $this->addSortToQuery($suspendPublicList, $sort);
            $suspendPublicList = $this->addLabelFilterToQuery($suspendPublicList, $sort);
            if ($paginate) {
                $suspendPublicList = $suspendPublicList->paginate($this->size);
            } else {
                $suspendPublicList = $suspendPublicList->get();
            }
            return [
                'suspendPublic' => [
                    'data' => $suspendPublicList->items(),
                    'total' => $suspendPublicList->total(),
                ],
            ];
        }

        // foreach ($houses as $house) {
        //     $postQuantity = $this->postManagerRepo->model->where('house_id',$house->id)->groupBy('user_id')->pluck('user_id')->toArray();
        //     if(count($postQuantity)>0){
        //         $house->postQuantity = count($postQuantity);
        //     }else{
        //         $house->postQuantity = 0;
        //     }
        // }
        // return $houses;
    }
    public function getHouseToExport($user, $sort = [])
    {
        // $query = $this->houseRepo->model->query()->with(['Project:id,name', 'User:id,name', 'HouseDirection:id,house_id,direction', 'HouseBalconyDirection:id,house_id,balcony','HouseTag.Tag'])->with(['HouseTag' => function ($query) use ($user) {
        //     $query->where('user_id', $user->id);
        // }]);

        //public_approve = 1 && public == 1 => đã được duyệt cộng đồng
        //public_approve = 0 && public == 1 => đang chờ duyệt cộng đồng
        //public == 0 ngưng chia sẻ cộng đồng
        $query = $this->houseRepo->model
            ->query()
            ->where('public_approval', '=', 1)
            ->with([
                'Project',
                'User',
                'HouseDirection',
                'HouseBalconyDirection',
            ])
            ->select(
                'house_number as Số nhà',
                'house_address as Tên đường',
                'property_type as Loại BĐS',
                'house_type as Loại nhà',
                'floors as Số lầu',
                'width as Chiều ngang',
                'length as Chiều dài',
                'end_open as Nở hậu',
                'area as Diện tích',
                'into_money as Giá',
                'user_id',
                'internal_image as Ảnh nội bộ',
                'public_image as Ảnh công khai',
                'customer_id as ID khách hàng',
                'project_id',
                'district as Phường',
                'province as Tỉnh/Thành phố',
                'brokerage_rate as Phần trăm phí',
                'brokerage_fee as Hoa hồng',
                'number_bedroom as Số phòng ngủ',
                'status',
                'street_type as Loại đường',
                'wide_street as Tim đường',
                'title as Tiêu đề',
                'floor_area as Diện tích sàn',
                'description as Mô tả',
                'suitable_customer',
                'offered_customer',
                'seen_customer',
                'require_info_customer',
                'deposit_customer',
                'approve',
                'public as Cộng đồng',
                'web',
                'purpose as Loại',
                'ownership as Pháp lý',
                'total_view as Lượt xem',
                'public_approval as Đã duyệt',
                'slug as Link',
                'floor_lot as Tầng/Lô',
                'block_section as Block/Khu'
            );
        if (isset($sort['status'])) {
            $query->where('status', $sort['status']);
        }

        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            if ($sort && !empty($sort['project_id'])) {
                $query->where('project_id', $sort['project_id']);
            }
            if (isset($sort['mode']) && $sort['mode'] == 'private') {
                $query->where('public', 0);
            }
            if (isset($sort['mode']) && $sort['mode'] == 'public') {
                $query->Where('public', 1);
            }
            $query = $this->addFilterToQuery($query, $sort, $user);
            $query = $this->addSearchToQuery($query, $sort);
            $query = $this->addSortToQuery($query, $sort);
            $query = $this->addLabelFilterToQuery($query, $sort);
            $query
                ->with(['Customer:id,user_id,name'])
                ->withCount(['Comments'])
                ->orderBy('updated_at', 'DESC');
            $houses = $query->get();
            return $houses;
        }
    }
    public function getHouseSold($userId, $startTime, $endTime)
    {
        $result = $this->houseRepo->model
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where('updated_at', '>', $startTime)
            ->where('updated_at', '<=', $endTime)
            ->get();

        return $result;
    }

    public function getHouseListByUserId($userId)
    {
        if (is_array($userId) == false) {
            $userId = [$userId];
        }
        return $this->houseRepo->model
            ->whereIn('user_id', $userId)
            ->where('status', '=', 0)
            ->get();
    }

    public function countHouse($userId)
    {
        $result = [];
        if (is_array($userId) == false) {
            $userId = [$userId];
        }
        foreach ($userId as $user) {
            $house = $this->houseRepo->model
                ->where('user_id', $user)
                ->where('status', '=', 0)
                ->count();
            $resultObject = [];
            $resultObject['id'] = $user;
            $resultObject['house'] = $house;
            array_push($result, $resultObject);
        }
        return $result;
    }

    public function getApprovedHouses()
    {
        return $this->houseRepo->model->where('web', 1)->paginate($this->size);
    }

    public function getHouseDraft(&$house)
    {
        $draft = $this->houseDraftRepo->model
            ->where('house_id', $house->id)
            ->where('status', 0)
            ->first();

        if ($draft) {
            $house->draft = $draft;
        }
    }

    public function getHouseDetail($houseId)
    {
        return $this->houseRepo->model
            ->where('id', $houseId)
            ->with(['HouseDirection', 'HouseBalconyDirection', 'City'])
            ->first();
    }

    public function addHouse($data)
    {
        if (isset($data['register_date'])) {
            $data['register_date'] = strtotime($data['register_date']);
        }

        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'];
        }
        // nếu loại bđs là Căn hộ - Chung cư cần có dự án để check trùng
        // $property_type = empty($data['property_type']) ? $house->property_type : $data['property_type'];
        // if ($property_type ===1 && empty($data['project_id'])) {
        //     $this->error(config('API.Message.House.ProjectRequire'));
        // }
        // if (isset($data['house_type']) && $data['house_type'] === 1 && empty($data['project_id'])) {
        //     $this->error(config('API.Message.House.ProjectRequire'));
        // }
        if (
            array_key_exists('house_address', $data) &&
            isset($data['house_number'])
        ) {
            $house = $this->houseRepo->model
                ->where('purpose', $data['purpose'])
                ->where('house_number', $data['house_number'])
                ->where('house_address', $data['house_address']);

            if ($data && !empty($data['project_id'])) {
                $house = $house->where('project_id', $data['project_id']);
            } else {
                $house = $house->whereNull('project_id');
            }
            $house = $house->first();

            if ($house) {
                $this->error(config('API.Message.House.DuplicateAddress'));
            }
        }

        if (isset($data['public']) && $data['public'] == 1) {
            $data['public_approval'] = 0;
        }

        // Create house slug
        $title = isset($data['title']) ? $data['title'] : null;

        if (!$title || $title == '') {
            $purpose = null;

            if (isset($data['purpose'])) {
                $purpose = $data['purpose'];
            }

            $purpose = $purpose == 0 ? 'Ban nha' : 'Cho thue';
            $houseAddress = isset($data['house_address'])
                ? $data['house_address']
                : null;
            $title = implode(' ', [$purpose, 'duong', $houseAddress]);
        }

        $slug = str_slug($title);
        $slug = $this->modifyHouseSlug($slug, null);

        $house = $this->houseRepo->create($data);

        if ($house && isset($data['direction'])) {
            foreach ($data['direction'] as $direction) {
                $this->houseDirectionRepo->create([
                    'house_id' => $house->id,
                    'direction' => $direction,
                ]);
            }
        }

        if ($house && isset($data['balcony_direction'])) {
            foreach ($data['balcony_direction'] as $balcony) {
                $this->houseBalconyDirectionRepo->create([
                    'house_id' => $house->id,
                    'balcony' => $balcony,
                ]);
            }
        }

        // Update slug
        $slug .= '-' . $house->id;
        $house = $this->houseRepo->update($house->id, ['slug' => $slug]);

        return $house;
    }
    /**
     * Validate duplicated address when update house
     */
    public function validateDuplicatedAddress($house, $data)
    {
        $checkHouse = $this->houseRepo->model
            ->where(
                'purpose',
                isset($data['purpose']) ? $data['purpose'] : $house->purpose
            )
            ->where('id', '<>', $house->id)
            ->where(
                'province',
                isset($data['province']) ? $data['province'] : $house->province
            )
            ->where(
                'house_number',
                isset($data['house_number'])
                    ? $data['house_number']
                    : $house->house_number
            )
            ->where(
                'house_address',
                isset($data['house_address'])
                    ? $data['house_address']
                    : $house->house_address
            );
        if (($data && !empty($data['project_id'])) || $house->project_id) {
            $checkHouse = $checkHouse->where(
                'project_id',
                isset($data['project_id'])
                    ? $data['project_id']
                    : $house->project_id
            );
        } else {
            $checkHouse = $checkHouse->whereNull('project_id');
        }
        $checkHouse = $checkHouse->first();

        if ($checkHouse) {
            $this->error(config('API.Message.House.DuplicateAddress'));
        }
    }

    /**
     * Update house information
     * @param $houseId
     * @param $user
     * @param $data
     * @return bool|mixed
     */
    public function updateHouse($houseId, $data, $user)
    {
        if (isset($data['status']) && $data['status'] == 1) {
            $data['public_approval'] = 0;
            $data['public'] = 0;
            $data['web'] = 0;
        }

        if (isset($data['resetCondition']) && $data['resetCondition']) {
            $data['reject_web_condition'] = null;
            $data['reject_public_condition'] = null;
        }
        if (isset($data['resetReason']) && $data['resetReason']) {
            $data['reason_stop_selling'] = null;
        }

        $house = $this->houseRepo->getModelById($houseId);
        $isStaffRole = $user->role == config('API.Constant.Role.Staff');

        if (!isset($house)) {
            $this->error(config('API.Message.House.NotExisted'));
        }
        if (
            $user->update_product_permission == 1 &&
            isset($data['status']) &&
            $data['status'] == 1 &&
            $house['user_id'] != $user->id
        ) {
            $update['status'] = 1; // 1 => ngưng bán
            $update['public_approval'] = 0;
            $update['public'] = 0;
            $update['web'] = 0;
            $house = $this->houseRepo->update($houseId, $update);
            return $house;
        }

        if ($isStaffRole && $house->user_id != $user->id) {
            return false;
        }

        unset($data['house_id']);

        $this->oldValue = $house->toArray();
        $this->newValue = $data;

        $this->validateDuplicatedAddress($house, $data);

        if (
            isset($data['public']) &&
            $data['public'] == 1 &&
            $house->public_approval == null
        ) {
            $data['public_approval'] = 0;
        }

        if ($house->web == 1 && $isStaffRole) {
            $conditions = [['house_id', $house->id], ['status', 0]];
            $existingDraft = $this->houseDraftRepo->getModelByFields($conditions, true);
            if ($existingDraft) {
                $draft = $this->houseDraftRepo->updateByFields(['house_id' => $houseId, 'attributes' => json_encode($data), 'status' => 0], $conditions);
            } else {
                $draft = $this->houseDraftRepo->create(['house_id' => $houseId, 'attributes' => json_encode($data), 'status' => 0]);
            }
            $house->draft = $draft;
            return $house;
        }

        $house = $this->houseRepo->update($houseId, $data);

        if ($house && isset($data['direction'])) {
            $this->houseDirectionRepo->deleteModelByField(
                'house_id',
                $house->id
            );
            if (count($data['direction']) > 0) {
                foreach ($data['direction'] as $direction) {
                    $this->houseDirectionRepo->create([
                        'house_id' => $house->id,
                        'direction' => $direction,
                    ]);
                }
            }
        }

        if ($house && isset($data['balcony_direction'])) {
            $this->houseBalconyDirectionRepo->deleteModelByField(
                'house_id',
                $house->id
            );
            if (count($data['balcony_direction']) > 0) {
                foreach ($data['balcony_direction'] as $balcony) {
                    $this->houseBalconyDirectionRepo->create([
                        'house_id' => $house->id,
                        'balcony' => $balcony,
                    ]);
                }
            }
        }

        $title = $house->title;

        if (!$title || $title == '') {
            $purpose = $house->purpose == 0 ? 'Ban nha' : 'Cho thue';
            $title = implode(' ', [$purpose, 'duong', $house->house_address]);
        }

        $slug = str_slug($title);

        if ($slug != $house->slug) {
            $slug = $this->modifyHouseSlug($slug, $house->id);
            $slug .= '-' . $houseId;
            return $this->houseRepo->update($houseId, ['slug' => $slug]);
        } else {
            return $house;
        }
    }

    /**
     * remove house & check role
     * @param $houseId
     * @param $user
     * @return bool|mixed
     */
    public function removeHouse($houseId, $user)
    {
        $customer = null;
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $customer = $this->houseRepo->getModelById($houseId);
        }

        if (!$customer) {
            return false;
        }

        return $this->houseRepo->deleteModelByField('id', $houseId);
    }

    public function getHouseFiles(&$house)
    {
        $fileIds = $house->file_ids;

        $files = [];
        if ($fileIds && count($fileIds)) {
            foreach ($fileIds as $index => $fileId) {
                $file = $this->fileRepo->getModelById($fileId);
                array_push($files, $file);
            }
        }

        $house->files = $files;
    }

    public function getHouseImage(&$house)
    {
        $internal = $house->internal_image;
        $public = $house->public_image;

        $internalImage = new \stdClass();
        if ($internal && count($internal)) {
            foreach ($internal as $index => $imageId) {
                $image = $this->imageRepo->getModelById($imageId);
                $internalImage->$index = $image;
            }
        }

        $publicImage = new \stdClass();
        if ($public && count($public)) {
            foreach ($public as $index => $imageId) {
                $image = $this->imageRepo->getModelById($imageId);
                $publicImage->$index = $image;
            }
        }

        $image = [
            'internal' => $internalImage,
            'public' => $publicImage,
        ];

        $house->image = $image;
    }

    public function getPublicHouseImage($house)
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
            'public' => $publicImage,
        ];

        $house->image = $image;
    }

    public function getPreviews()
    {
        return $this->housePreviewRepo->model
            ->with(['User', 'House'])
            ->orderBy('created_at', 'DESC')
            ->paginate($this->size);
    }

    public function addHousePreview($houseId)
    {
        $house = $this->houseRepo->getModelById($houseId);

        $housePreview = $this->housePreviewRepo->getModelByFields(
            [['user_id', $house->user_id], ['house_id', $houseId]],
            true
        );

        if ($housePreview) {
            return $housePreview;
        }

        return $this->housePreviewRepo->create([
            'user_id' => $house->user_id,
            'house_id' => $houseId,
        ]);
    }

    public function approveHouse($user, $previewId)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $preview = $this->housePreviewRepo->getModelById($previewId);
            $house = $this->houseRepo->getModelById($preview->house_id);
            $house->web = 1;
            $house->save();

            if (!$house) {
                return false;
            }

            return $this->housePreviewRepo->deleteModelByField(
                'id',
                $preview->id
            );
        } else {
            return false;
        }
    }

    public function rejectHouse($user, $previewId, $condition = null)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $preview = $this->housePreviewRepo->getModelById($previewId);
            $house = $this->houseRepo->getModelById($preview->house_id);
            $house->web = 0;
            if ($condition) {
                $house->reject_web_condition = $condition;
            }
            $house->save();

            if (!$house) {
                return false;
            }

            return $this->housePreviewRepo->deleteModelByField(
                'id',
                $preview->id
            );
        } else {
            return false;
        }
    }

    public function getStreet()
    {
        $query = $this->streetRepo->model->query();

        if ($this->key) {
            $query->where('street_name', 'LIKE', '%' . $this->key . '%');
        }

        return $query->orderBy('street_name', 'ASC')->get();
    }

    public function getProjectImage(&$project)
    {
        $images = $project->images;

        $imageList = new \stdClass();
        if (count($images)) {
            foreach ($images as $index => $imageId) {
                $image = $this->imageRepo->getModelById($imageId);
                $imageList->$index = $image;
            }
        }

        $project->imageUrls = $imageList;
    }

    public function addProject($data)
    {
        if (isset($data['name'])) {
            $project = $this->projectRepo->model
                ->where('name', $data['name'])
                ->first();

            if ($project) {
                $this->error(config('API.Message.Project.DuplicateName'));
            }
        }

        $data['slug'] = str_slug($data['name']);

        return $this->projectRepo->create($data);
    }

    public function updateProject($projectId, $data, $user)
    {
        if (isset($data['name'])) {
            $project = $this->projectRepo->model
                ->where('name', $data['name'])
                ->where('id', '<>', $data['project_id'])
                ->first();

            if ($project) {
                $this->error(config('API.Message.Project.DuplicateName'));
            }
        }

        $data['slug'] = str_slug($data['name']);
        unset($data['project_id']);

        return $this->projectRepo->update($projectId, $data);
    }

    public function removeProject($projectId, $user)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin') ||
            $user->role == config('API.Constant.Role.Marketer')
        ) {
            $project = $this->projectRepo->getModelById($projectId);
        } else {
            $this->error(config('API.Message.Project.ForbiddenRemove'), 403);
            return false;
        }

        if (!$project) {
            $this->error(config('API.Message.Project.NotExisted'));
            return false;
        }

        return $this->projectRepo->deleteModelByField('id', $projectId);
    }

    public function getProjectList($data)
    {
        $query = $this->projectRepo->model
            ->query()
            ->select(
                'id',
                'content',
                'name',
                'images',
                'created_at',
                'updated_at',
                'address'
            );
        // $query = $query->with(['Houses.Customer'])->orderBy('updated_at', 'DESC'); //old
        $query = $query
            ->with([
                'Houses:project_id,house_number,house_address,district,province,customer_id',
                'Houses.Customer:id,name',
            ])
            ->orderBy('updated_at', 'DESC'); //old
        if (isset($data['search']) && $data['search']) {
            $query = $query->where('name', 'like', '%' . $data['search'] . '%');
        }

        $projects = $query->paginate($this->size);

        $total = $projects->total();
        $projects = $projects->items();

        foreach ($projects as $project) {
            $this->getProjectImage($project);
        }

        return ['data' => $projects, 'total' => $total];
    }
    public function getProjectListFromWeb($data)
    {
        $query = $this->projectRepo->model->query();
        //$query = $query->with(['Houses.Customer'])->orderBy('updated_at', 'DESC'); //old
        if ($this->size == 12) {
            $query = $query
                ->select('slug', 'name', 'images')
                ->orderBy('updated_at', 'DESC');
        } else {
            $query = $query
                ->select('content', 'slug', 'name', 'images')
                ->orderBy('updated_at', 'DESC');
        }

        if (isset($data['search']) && $data['search']) {
            $query = $query->where('name', 'like', '%' . $data['search'] . '%');
        }

        $projects = $query->paginate($this->size);

        $total = $projects->total();
        $projects = $projects->items();

        foreach ($projects as $project) {
            $this->getProjectImage($project);
        }

        return ['data' => $projects, 'total' => $total];
    }

    public function searchProjectsByName($data)
    {
        $query = $this->projectRepo->model->query();
        $query = $query->orderBy('name', 'ASC');

        if (isset($data['key']) && $data['key']) {
            $query = $query->where('name', 'like', '%' . $data['key'] . '%');
        }

        $projects = $query->take(20)->get();

        return $projects;
    }

    public function getProjectDetail($projectId)
    {
        return $this->projectRepo->getModelById($projectId);
    }

    public function getProjectDetailBySlug($slug)
    {
        return $this->projectRepo->getModelByField('slug', $slug, true);
    }

    public function getHouseColumnValues($columnName)
    {
        return $this->houseRepo->model
            ->where('status', 0)
            ->groupBy($columnName)
            ->pluck($columnName)
            ->toArray();
    }

    public function getPendingPublicHouse($user)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $houses = $this->houseRepo->model
                ->where('public_approval', 0)
                ->where('public', 1)
                ->orderBy('created_at', 'DESC')
                ->paginate($this->size);

            return $houses->items();
        } else {
            return false;
        }
    }

    public function getPendingUpdateHouses()
    {
        $houses = $this->houseDraftRepo->model
            ->where('status', 0)
            ->paginate($this->size);

        return ['data' => $houses->items(), 'total' => $houses->total()];
    }

    public function approvePublicHouse($user, $houseId)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $house = $this->houseRepo->getModelById($houseId);

            if ($house->public_approval == 0) {
                $house->public_approval = 1;
            }

            return $house->save();
        } else {
            return false;
        }
    }

    public function rejectPublicHouse($user, $houseId, $condition = null)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $house = $this->houseRepo->getModelById($houseId);
            $house->public = 0;
            if ($condition) {
                $house->reject_public_condition = $condition;
            }
            return $house->save();
        } else {
            return false;
        }
    }

    public function getTopViewedHouses($user, $size)
    {
        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            $houses = $this->houseRepo->model
                ->orderBy('total_view', 'DESC')
                ->limit($size);
        } else {
            $houses = $this->houseRepo->model
                ->where('public_approval', 1)
                ->orWhere('user_id', $user->id)
                ->orderBy('total_view', 'DESC')
                ->limit($size);
        }

        return $houses->get();
    }

    public function modifyHouseSlug($slug, $houseId = null)
    {
        $house = $this->houseRepo->getModelByFields(
            [['slug', $slug], ['id', '!=', $houseId]],
            true
        );

        if ($house) {
            $slug .= '-' . rand(1, 99);

            return $this->modifyHouseSlug($slug, $houseId);
        } else {
            return $slug;
        }
    }

    public function getAllComment($user, $houseId)
    {
        $house = $this->houseRepo->getModelById($houseId);

        $query = $this->houseCommentRepo->model->with(['User.Avatar', 'Image']);

        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin') ||
            $user->id == $house->user_id
        ) {
            $comments = $query
                ->where('house_id', $houseId)
                ->orderBy('created_at', 'DESC')
                ->paginate($this->size);
        } else {
            $comments = $query
                ->where('house_id', $houseId)
                ->where('status', 0)
                ->orderBy('created_at', 'DESC')
                ->paginate($this->size);
        }

        return $comments->items();
    }

    public function getAllComments($user)
    {
        $query = $this->houseRepo->model
            ->query()
            ->with(['User.Avatar', 'HouseDirection', 'HouseBalconyDirection']);
        $query->where('status', 0);

        if (
            $user->role == config('API.Constant.Role.SuperAdmin') ||
            $user->role == config('API.Constant.Role.Admin')
        ) {
            return $query
                ->with(['Customer'])
                ->withCount(['Comments', 'Likes'])
                ->orderBy('updated_at', 'DESC')
                ->paginate($this->size);
        }

        $query->where(function ($query) use ($user) {
            $query->where('user_id', $user->id);
            $query->orWhere('public_approval', 1);
        });

        return $query
            ->orderBy('updated_at', 'DESC')
            ->withCount([
                'Comments' => function ($query) use ($user) {
                    $query->where('status', '=', 0);
                    $query->orWhere('user_id', '=', $user->id);
                },
                'Likes',
            ])
            ->paginate($this->size);
    }

    public function createOrUpdateHouseStatistic($userId, $remain, $type)
    {
        return $this->houseStatisticRepo->createOrUpdateHouseStatistic(
            $userId,
            $remain,
            $type
        );
    }
    public function getDetailHouseStatistic($userId, $start_at, $end_at)
    {
        return $this->houseStatisticRepo->getDetailHouseStatistic(
            $userId,
            $start_at,
            $end_at
        );
    }
    public function exportData()
    {
    }

    public function getHouseByUserId($id)
    {
        $house = $this->houseRepo->model->where('user_id', $id)->get();
        return $house;
    }

    public function getCustomerContacts()
    {
        $contacts = $this->customerContactRepo->model->with(['House', 'User'])->paginate($this->size);
        return $contacts;
    }

    public function updateCustomerContactStatus($id, $status, $userId)
    {

        $contact = $this->customerContactRepo->getModelById($id);

        if (!isset($contact)) {
            $this->error(config('API.Message.NotFound'));
        }

        // Done
        if ($contact->status == 3) {
            $this->error(config('API.Message.ForbiddenUpdate'));
        }

        $this->customerContactRepo->update($id, ['status' => $status, 'processed_by' => $userId]);

        $contact->status = $status;


        return $contact;
    }
    public function updateHousePendingChanges($id, $status, $user)
    {

        $house = $this->houseRepo->getModelById($id);
        $draft = $this->houseDraftRepo->model
            ->where('house_id', $id)
            ->where('status', 0)
            ->first();

        if (!isset($house) || !isset($draft)) {
            $this->error(config('API.Message.NotFound'));
        }

        $draftData = ['status' => $status, 'approved_by' => $user->id, 'approved_at' => now()];
        // Rejected
        if ($status == 2) {
            $this->houseDraftRepo->update($draft->id, $draftData);
            return $house;
        }

        // Approved
        if ($status == 1) {
            $data = json_decode($draft->attributes, true);
            $this->updateHouse($id, $data, $user);
            $house = $this->houseDraftRepo->update($draft->id, $draftData);
            return $house;
        }

        return $house;
    }
}

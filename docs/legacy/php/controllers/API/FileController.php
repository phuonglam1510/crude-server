<?php

namespace App\Http\Controllers\API;

use App\Exports\ExportXLS;
use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Models\User\User;
use App\Services\Customer\CustomerService;
use App\Services\Dictionary\DictionaryService;
use App\Services\House\HouseService;
use App\Services\House\ImageService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;

class FileController extends Controller
{
    use CustomRequest;
    private $excel;
    private $houseService;
    private $customerService;
    private $userService;
    private $dictionaryService;
    private $imageService;

    public function __construct(
        Excel $excel,
        HouseService $houseService,
        CustomerService $customerService,
        UserService $userService,
        DictionaryService $dictionaryService,
        ImageService $imageService
    ) {
        $this->excel = $excel;
        $this->houseService = $houseService;
        $this->customerService = $customerService;
        $this->userService = $userService;
        $this->dictionaryService = $dictionaryService;
        $this->imageService = $imageService;
    }

    public function export(Request $request, $type)
    {
        $this->getUser($user);
        if ($user->role !== config('API.Constant.Role.SuperAdmin')) {
            $this->error(config('API.Message.NotEnoughPermission'));
        }

        $data = $this->data($request);
        switch ($type) {
            case 'house':
                $name = 'house';
                $collections = $this->houseService->getHouseToExport($user, $data, false);
                break;
            case 'customer_request':
                $name = 'customer_request';
                $collections = $this->customerService->getCustomerRequests($user, $data, false);
                break;
            case 'customer':
                $name = 'customer';
                $collections = $this->customerService->getCustomers($user, $data, false);
                break;
            case 'users':
                $name = 'users';
                $collections = $this->userService->userRepo->getUserList(null, $user);
                break;
        }

        if ($collections) {
            if ($type == 'house' || $type == 'customer_request') {
                //$this->dictionaryService->getData($collections);
                $collections = $this->imageService->getImageUrl($collections);
            }

            $downloadable = $this->excel->download(new ExportXLS($collections), $name . '.xlsx');

            return $downloadable;
        }

        return false;
    }
}

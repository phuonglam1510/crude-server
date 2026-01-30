<?php

namespace App\Http\Controllers\API\Customer;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerRequestController extends Controller
{
    use CustomRequest;
    private $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->middleware('json_request', [
            'except' => []
        ]);

        $this->customerService = $customerService;
    }

    /**
     * Get customer request detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getCustomerRequestDetail(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
      
        $customerRequest = $this->customerService->getCustomerRequest($data['request_id']);
        $this->success($customerRequest);
    }

    /**
     * Get customer request list
     * @throws \App\Exceptions\JsonResponse
     */
    public function getCustomerRequests(Request $req)
    {   
        $data = $req->all();
        
        $this->getUser($user);
        $request = $this->customerService->getCustomerRequests($user, $data);
        $requests = $request->items();

        foreach($requests as $item) {
            if($user->role !== config('API.Constant.Role.SuperAdmin') && $user->role !== config('API.Constant.Role.Admin') && $item->user_id == $user->id) {
                $item->load(['Customer']);
            }
        }

        $this->success($requests);
    }

    /**
     * Add new customer request
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addCustomerRequest(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        if(isset($data['require_column'])){
            foreach ($data['require_column'] as $column) {
                if(!isset($data[$column])){
                    $this->error(config('API.Message.CustomerRequest.NotMatchRequireColumn'), 405);
                }
            }
        }
        if($data['min_price']>$data['max_price']){
            $this->error(config('API.Message.CustomerRequest.PriceNotAllowed'), 405);
        }
        $data['user_id'] = $user->id;
        $customerRequest = $this->customerService->addCustomerRequest($data);
        $this->success($customerRequest, null, 201);
    }

    /**
     * Update customer request
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateCustomerRequest(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $customerRequest = $this->customerService->updateCustomerRequest($data['request_id'], $data, $user);
       if(isset($data['require_column'])){
            foreach ($data['require_column'] as $column) {
                if(!isset($data[$column])){
                    $this->error(config('API.Message.CustomerRequest.NotMatchRequireColumn'), 405);
                }
            }
        }
        if(isset($data['max_price'])&&isset($data['min_price'])&&$data['max_price']<$data['min_price']){
            $this->error(config('API.Message.CustomerRequest.PriceNotAllowed'), 405);
        }
        if (!$customerRequest) {
            $this->error(config('API.Message.CustomerRequest.ForbiddenUpdate'), 403);
        }

        $this->success($customerRequest);
    }

    /**
     * Update user
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function removeCustomerRequest(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $customerRequest = $this->customerService->removeCustomerRequest($data['request_id'], $user);

        if (!$customerRequest) {
            $this->error(config('API.Message.CustomerRequest.ForbiddenRemove'), 403);
        }

        $this->success($customerRequest);
    }
}

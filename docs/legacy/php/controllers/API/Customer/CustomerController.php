<?php

namespace App\Http\Controllers\API\Customer;


use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerRequest;
use App\Http\Traits\CustomRequest;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
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
     * Get all customers of sale or public customer
     * @throws \App\Exceptions\JsonResponse
     */
    public function getCustomers(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);


        $customers = $this->customerService->getCustomers($user, $data);
        $total = $customers->total();
        $customers = $customers->items();


        foreach ($customers as $customer) {
            $this->customerService->getCustomerImage($customer);
            if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $customer->user_id) {
                unset($customer->phone_number);
            }
        }

        $this->success(['data' => $customers, 'total' => $total]);
    }

    /**
     * Get customer detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function getCustomerDetail(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $customer = $this->customerService->getCustomer($data['customer_id']);
        $this->customerService->getCustomerImage($customer);

        if ($user->role == config('API.Constant.Role.Admin') && $user->id !== $customer->user_id) {
            unset($customer->phone_number);
        }


        $this->success($customer);
    }
     public function getCustomerByUserId($userId)
    {
        $customers = $this->customerService->getCustomerByUserId($userId);
        $this->success($customers);
    }

    /**
     * Add new customer
     * @param CustomerRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function addNewCustomer(CustomerRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        // Add new customer
        $customer = $this->customerService->addCustomer($data);
        $this->success($customer, null, 201);
    }

    /**
     * Update user
     * @param CustomerRequest $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function updateCustomer(CustomerRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $customer = $this->customerService->updateCustomer($data['customer_id'], $data, $user);

        if (!$customer) {
            $this->error(config('API.Message.Customer.ForbiddenUpdate'), 403);
        }

        $this->success($customer);
    }

    /**
     * Update user
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */
    public function removeCustomer(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $customer = $this->customerService->removeCustomer($data['customer_id'], $user);

        if (!$customer) {
            $this->error(config('API.Message.Customer.ForbiddenRemove'), 403);
        }

        $this->success($customer);
    }
   public function getAllCustomerName(Request $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $customer = $this->customerService->customerRepo->model->select('name')->get();

        if (!$customer) {
            $this->error(config('API.Message.Customer.ForbiddenRemove'), 403);
        }

        $this->success($customer);
    }
    
}

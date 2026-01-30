<?php

namespace App\Http\Controllers\API\Dashboard;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use CustomRequest;
    public $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard analytics
     * @throws \App\Exceptions\JsonResponse
     */
    public function getDashboardData()
    {
        $this->getUser($user);
        $data = $this->dashboardService->getData($user);
        $this->success($data);
    }

    public function generalSearch(Request $request)
    {
        $data = $this->dashboardService->dashboardSearch();

        $this->success($data);
    }
}

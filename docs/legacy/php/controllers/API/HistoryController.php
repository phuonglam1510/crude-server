<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\ActionHistoryService;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    use CustomRequest;
    public $actionHistoryService;

    public function __construct(ActionHistoryService $actionHistoryService)
    {
        $this->actionHistoryService = $actionHistoryService;
    }

    public function getHistory(Request $request)
    {
        $data = $this->data($request);
        $sort = isset($data['sort']) ? $data['sort'] : [];
        $filter = isset($data['filter']) ? $data['filter'] : [];

        if (count($filter) == 0) {
            $this->error('Error');
        }

        $histories = $this->actionHistoryService->getHistoryList($filter, $sort);

        $this->success($histories);
    }
}

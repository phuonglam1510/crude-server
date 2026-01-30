<?php


namespace App\Http\Controllers\API\House;


use App\Http\Controllers\Controller;
use App\Http\Requests\Label\HouseLabelRequest;
use App\Http\Requests\Label\LabelRequest;
use App\Http\Traits\CustomRequest;
use App\Services\Label\LabelService;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    use CustomRequest;
    private $labelService;

    public function __construct(LabelService $labelService)
    {
        $this->labelService = $labelService;
    }

    public function getLabelList()
    {
        $this->getUser($user);
        $labels = $this->labelService->getLabelList($user);

        $this->success($labels->items());
    }

    public function getLabelDetail(LabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $label = $this->labelService->labelRepo->getModelById($data['label_id']);

        if ($label->user_id != $user->id) {
            $this->error(config('API.Message.NotFound'));
        }

        if (!$label) {
            $this->error(config('API.Message.NotFound'));
        }

        $this->success($label);
    }

    public function addNewLabel(LabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $label = $this->labelService->createLabel($user, $data);

        if (!$label) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($label);
    }

    public function updateLabel(LabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $label = $this->labelService->updateLabel($user, $data);

        if (!$label) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($label);
    }

    public function assignHouseLabel(HouseLabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $houseLabel = $this->labelService->assignHouseLabel($user, $data);

        if (!$houseLabel) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($houseLabel);
    }

    public function assignLabelHouse(HouseLabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $labelHouse = $this->labelService->assignLabelHouse($user, $data);

        if (!$labelHouse) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($labelHouse);
    }

    public function deleteLabel(LabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
    }

    public function deleteHouseLabel(LabelRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        $deleted = $this->labelService->deleteHouseLabel($user, $data['label_id']);

        if (!$deleted) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($deleted);
    }
}

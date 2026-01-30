<?php

namespace App\Http\Controllers\API\Dictionary;


use App\Http\Controllers\Controller;
use App\Http\Requests\Dictionary\DictionaryRequest;
use App\Http\Traits\CustomRequest;
use App\Http\Traits\CustomResponse;
use App\Services\Dictionary\DictionaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DictionaryController extends Controller
{
    use CustomRequest;
    public $dictionaryService;
    public $name;
    public function __construct(DictionaryService $dictionaryService)
    {
        $this->dictionaryService = $dictionaryService;
    }

    public function getList(Request $request)
    {
        $data = $this->data($request);
        $this->name = Route::currentRouteName();

        $options = $this->dictionaryService->getOptionsByName($data, $this->name);

        $this->success($options);
    }

    public function getDetail(Request $request)
    {
        $data = $this->data($request);
        $this->name = Route::currentRouteName();

        if (!isset($data['id'])) {
            $this->error(config('API.Message.MissingID'));
        }

        $option = $this->dictionaryService->getOptionByName($data['id'], $this->name);

        $this->success($option);
    }

    public function addOption(DictionaryRequest $request)
    {
        $data = $this->data($request);
        $this->name = Route::currentRouteName();

        $option = $this->dictionaryService->addOptionByName($data, $this->name);

        if (!$option) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($option);
    }

    public function updateOption(DictionaryRequest $request)
    {
        $data = $this->data($request);
        $this->name = Route::currentRouteName();

        $option = $this->dictionaryService->updateOptionByName($data, $this->name);

        if (!$option) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($option);
    }

    public function deleteOption(Request $request)
    {
        $data = $this->data($request);
        $this->name = Route::currentRouteName();

        if (!isset($data['id'])) {
            $this->error(config('API.Message.MissingID'));
        }

        $status = $this->dictionaryService->deleteOptionByName($data['id'], $this->name);

        if (!$status) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($status);
    }
}

<?php

namespace App\Http\Controllers\API\Web;


use App\Http\Controllers\Controller;
use App\Http\Traits\CustomRequest;
use App\Services\Blog\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use CustomRequest;
    public $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function getBlogList()
    {
        $blogs = $this->blogService->getBlogList();

        $this->success($blogs);
    }

    public function getBlogDetail(Request $request)
    {
        $data = $this->data($request);

        if (!$data) {
            $this->error(config('API.Message.ServerError'));
        }

        $blog = $this->blogService->getBlogDetail($data['blog_id']);

        $this->success($blog);
    }
}
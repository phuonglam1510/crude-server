<?php

namespace App\Http\Controllers\API\Blog;


use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogRequest;
use App\Http\Traits\CustomRequest;
use App\Services\Blog\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use CustomRequest;
    private $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }


    public function getBlogList(Request $request)
    {
        $this->getUser($user);

        if (!$this->blogService->checkBlogRole($user->role)) {
            $this->error(config('API.Message.Forbidden'));
        }

        $blogs = $this->blogService->getBlogList();

        $this->success($blogs);
    }

    public function getBlogDetail(BlogRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if (!$this->blogService->checkBlogRole($user->role)) {
            $this->error(config('API.Message.Forbidden'));
        }

        $blog = $this->blogService->getBlogDetail($data['blog_id']);

        $this->success($blog);
    }

    public function addBlog(BlogRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if (!$this->blogService->checkBlogRole($user->role)) {
            $this->error(config('API.Message.Forbidden'));
        }

        $blog = $this->blogService->addBlog($data, $user->id);

        if (!$blog) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($blog);
    }

    public function updateBlog(BlogRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if (!$this->blogService->checkBlogRole($user->role)) {
            $this->error(config('API.Message.Forbidden'));
        }

        $blog = $this->blogService->updateBlog($data['blog_id'], $data, $user->id);

        if (!$blog) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($blog);
    }

    public function removeBlog(BlogRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);

        if (!$this->blogService->checkBlogRole($user->role)) {
            $this->error(config('API.Message.Forbidden'));
        }

        $remove = $this->blogService->blogRepo->deleteModelByField('id', $data['blog_id']);

        if (!$remove) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($remove);
    }
}

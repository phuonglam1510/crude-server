<?php

namespace App\Http\Controllers\API\Notification;


use App\Http\Controllers\Controller;
use App\Http\Requests\User\NotiCommentRequest;
use App\Http\Traits\CustomRequest;
use App\Services\User\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use CustomRequest;
    public $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get dashboard analytics
     * @throws \App\Exceptions\JsonResponse
     */
    public function getAll()
    {
        $this->getUser($user);

        $data = $this->notificationService->getAll($user);
        $this->success($data);
    }

    /**
     * Seen notification action
     * @param $notiId
     * @throws \App\Exceptions\JsonResponse
     */
    public function seenNotification($notiId)
    {
        $this->getUser($user);

        $status = $this->notificationService->seenNotification($notiId, $user->id);

        if (!$status) {
            $this->error($status, 500);
        }

        $this->success($status);
    }
}

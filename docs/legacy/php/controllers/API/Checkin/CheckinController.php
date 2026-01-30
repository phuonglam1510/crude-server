<?php

namespace App\Http\Controllers\API\Checkin;
use App\Http\Traits\CustomRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use App\Repositories\User\UserRepository;
use App\Services\Checkin\CheckinService;
use App\Http\Requests\User\TrackLocationRequest;
use DateTime;
use DateTimeZone;

class CheckinController extends Controller
{
    use CustomRequest;
    private $authService;
    private $userService;
    private $checkinService;
    private $userRepo;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        CheckinService $checkinService,
        UserRepository $userRepository
    ) {
        $this->middleware('json_request', [
            'except' => [],
        ]);
        $this->authService = $authService;
        $this->userService = $userService;
        $this->checkinService = $checkinService;
        $this->userRepo = $userRepository;
    }

    /**
     * Get user detail
     * @param Request $request
     * @throws \App\Exceptions\JsonResponse
     */

    public function trackLocation(TrackLocationRequest $request)
    {
        $this->getUser($user);
        $data = $this->data($request);
        $data['user_id'] = $user->id;
        $data['ip_address'] = $request->getClientIp();
        $timeEnd = new DateTime();
        // $defaultDate = date_create($timeEnd->format('Y-m-d') . ' ' .'00:00:00', timezone_open('Asia/Ho_Chi_Minh'));
        $defaultDate = strtotime($timeEnd->format('Y-m-d') . ' ' .'00:00:00');
        $time = date_create('now',timezone_open('Asia/Ho_Chi_Minh'));
        $shift1 = date_create($timeEnd->format('Y-m-d') . ' ' .'13:00:00', timezone_open('Asia/Ho_Chi_Minh'));
        $shift2 = date_create($timeEnd->format('Y-m-d') . ' ' .'17:00:00', timezone_open('Asia/Ho_Chi_Minh'));
        $shift3 = date_create($timeEnd->format('Y-m-d') . ' ' .'24:00:00', timezone_open('Asia/Ho_Chi_Minh'));
        $data['shift'] = 1;
        $data['time'] = substr($time->format('Y-m-d H:i:s'), -8);
        if($time < $shift1) {
            $exist = $this->checkinService->getCheckinByUserForShift($user->id, $defaultDate, $data['shift']);
            if(!$exist) {
                $data['status'] = 0;
                $track = $this->checkinService->createTrackLocation($data);
            } else {
                $data['status'] = 1;
                $track = $this->checkinService->createTrackLocation($data);
            }
        } elseif($time > $shift1 && $time < $shift2) {
            $data['shift'] = 2;
            $exist = $this->checkinService->getCheckinByUserForShift($user->id, $defaultDate, $data['shift']);
            if(!$exist) {
                $checkCheckoutPrevShift = $this->checkinService->getCheckoutByUserForShift($user->id, $defaultDate, 1);
                $checkCheckinPrevShift = $this->checkinService->getCheckinByUserForShift($user->id, $defaultDate, 1);
                if(!$checkCheckoutPrevShift && $checkCheckinPrevShift) {
                    $data['status'] = 1;
                    $data['shift'] = 1;
                    $track = $this->checkinService->createTrackLocation($data);
                } else if(!$checkCheckoutPrevShift && !$checkCheckinPrevShift) {
                    $data['status'] = 0;
                    $track = $this->checkinService->createTrackLocation($data);
                } 
            } else {
                $data['status'] = 1;
                $track = $this->checkinService->createTrackLocation($data);
            }
        } else if($time > $shift2 && $time < $shift3) {
            $data['shift'] = 3;
            $exist = $this->checkinService->getCheckinByUserForShift($user->id, $defaultDate, $data['shift']);
            if(!$exist) {
                $checkCheckoutPrevShift = $this->checkinService->getCheckoutByUserForShift($user->id, $defaultDate, 2);
                $checkCheckinPrevShift = $this->checkinService->getCheckinByUserForShift($user->id, $defaultDate, 2);
                if(!$checkCheckoutPrevShift && $checkCheckinPrevShift) {
                    $data['status'] = 1;
                    $data['shift'] = 2;
                    $track = $this->checkinService->createTrackLocation($data);
                } else if(!$checkCheckoutPrevShift && !$checkCheckinPrevShift) {
                    $data['status'] = 0;
                    $track = $this->checkinService->createTrackLocation($data);
                } 
            } else {
                $data['status'] = 1;
                $track = $this->checkinService->createTrackLocation($data);
            }
        }
        // $data['time'] = $time;
        // $track = $this->checkinService->createTrackLocation($data);

        if (!$track) {
            $this->error(config('API.Message.ServerError'));
        }

        $this->success($track);
    }

    public function getLocationByUser(Request $request, $userId)
    {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        
        $locations = $this->checkinService->getLocationByUser($userId, $data);
        // dd($locations);
        $this->success($locations);
    }

    public function getLocationByManager(Request $request, $userId)
    {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $query = $this->userRepo->model->select('id');
        if($user->job_position === 6)  {
            $query = $query->where('team_leader_id', $userId);
        }
        if($user->job_position === 5)  {
            $query = $query->where('manager_id', $userId);
        }
        if($user->job_position === 4)  {
            $query = $query->where('senior_manager_id', $userId);
        }
        if($user->job_position === 3)  {
            $query = $query->where('sale_admin_id', $userId);
        }

        if($user->job_position === 2)  {
            $query = $query->where('admin_id', $userId);
        }
        if($user->job_position === 1)  {
            $query = $query->where('super_admin_id', $userId);
        }
        $userList = $this->userService->getUserByManager($query)->toArray();
        array_push($userList, ['id'=>(int) $userId]);
        
        $locations = $this->checkinService->getLocationByManager($userList, $data);
        // dd($locations);
        $this->success($locations);
    }


    public function getLastLocationCheckin(Request $request)
    {
        $this->getUser($user);

        $lastLocation = $this->checkinService->getLastLocationCheckin($user->id);

        $this->success($lastLocation);
    }

    // public function getCheckinByUserWithDate(Request $request)
    // {
    //     $this->getUser($user);
    //     // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
    //     //     $this->error(config('API.Message.Forbidden'));
    //     // }
    //     $data = $this->data($request);
    //     $date = 'ac';
        
    //     $locations = $this->checkinService->getCheckinByUserWithDate($userId, $date);
    //     // dd($locations);
    //     $this->success($locations);
    // }

      public function getLocationMonitor(Request $request)
    {
        $this->getUser($user);
        // if ($user->role != config('API.Constant.Role.SuperAdmin')) {
        //     $this->error(config('API.Message.Forbidden'));
        // }
        $data = $this->data($request);
        $userId = $user->id;
        $query = $this->userRepo->model->select('id');
        if($user->job_position === 6)  {
            $query = $query->where('team_leader_id', $userId);
        }
        if($user->job_position === 5)  {
            $query = $query->where('manager_id', $userId);
        }
        if($user->job_position === 4)  {
            $query = $query->where('senior_manager_id', $userId);
        }
        if($user->job_position === 3)  {
            $query = $query->where('sale_admin_id', $userId);
        }

        if($user->job_position === 2)  {
            $query = $query->where('admin_id', $userId);
        }
        if($user->job_position === 1)  {
            $query = $query->where('super_admin_id', $userId);
        }
        $userList = $this->userService->getUserByManager($query)->toArray();
        array_push($userList, ['id'=>(int) $userId]);

        $locations = $this->checkinService->getLocationMonitor($userList, $data);
        
        // dd($locations);
        $this->success($locations);
    }

}


<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrdersDayResources;
use App\Http\Resources\Orders\OrdersSaveDayResources;
use App\Models\CanselOrderHoursDay;
use App\Models\Captain;
use App\Models\CaptainProfile;
use App\Models\CaptionActivity;
use App\Models\OrderDay;
use App\Models\SaveRentDay;
use App\Models\Traits\Api\ApiResponseTrait;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderDayController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_days,order_code',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {

            $order = OrderDay::where('order_code', $request->order_code)->firstOrFail();
            return $this->successResponse(new OrdersDayResources($order), 'data return successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'captain_id' => 'required|exists:captains,id',
            'total_price' => 'required|numeric',
            'payments' => 'required|in:cash,masterCard,wallet',
            'lat_user' => 'required',
            'long_user' => 'required',
            'address_now' => 'required',
            'start_day' => 'required',
            'end_day' => 'required',
            'number_day' => 'required',
            'start_time' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        if (OrderDay::where('user_id', $request->user_id)->where('status', 'pending')->exists()) {
            return $this->errorResponse('This client is already on a journey');
        }

        if (OrderDay::where('captain_id', $request->captain_id)->where('status', 'pending')->exists()) {
            return $this->errorResponse('This captain is already on a journey');
        }
        try {

        $latestOrderId = optional(OrderDay::latest()->first())->id;
        $orderCode = 'orderD_' . $latestOrderId . generateRandomString(5);
        $chatId = 'chatD_' . generateRandomString(4);
        $user = User::findorfail($request->user_id);
        $caption = Captain::findorfail($request->captain_id);
        $data = OrderDay::create([
            'address_now' => $request->address_now,
            'user_id' => $request->user_id,
            'captain_id' => $request->captain_id,
            'trip_type_id' => 3,
            'order_code' => $orderCode,
            'total_price' => $request->total_price,
            'chat_id' => $chatId,
            'status' => 'pending',
            'payments' => $request->payments,
            'lat_user' => $request->lat_user,
            'long_user' => $request->long_user,
            'start_day' => $request->start_day,
            'end_day' => $request->end_day,
            'number_day' => $request->number_day,
            'start_time' => $request->start_time,
            'commit' => $request->commit,
            'date_created' => Carbon::now()->format('Y-m-d'),


        ]);

        if ($data) {

            sendNotificationCaptain($request->captain_id, 'Trips Created Successfully User ' . $user->name, 'New Trips', true);
            sendNotificationUser($request->user_id, 'Trips Created Successfully Driver ' . $caption->name, 'New Trips', true);

            createInFirebaseDay($request->user_id, $request->captain_id, $data->id);


        }
        return $this->successResponse(new OrdersDayResources($data), 'Data created successfully');

        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }


    }

    public function saveDay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric',
            'payments' => 'required|in:cash,masterCard,wallet',
            'lat_user' => 'required',
            'long_user' => 'required',
            'address_now' => 'required',
            'start_day' => 'required',
            'end_day' => 'required',
            'number_day' => 'required',
            'start_time' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }


        if (SaveRentDay::where('user_id', $request->user_id)->whereNotIn('status', ['done', 'cancel', 'accepted'])->where('start_day', $request->start_day)->where('end_day', $request->end_day)->first()) {
            return $this->errorResponse('There is a flight already booked for the same date');

        }

        $user = User::findOrfail($request->user_id);

        try {

            $latestOrderId = optional(SaveRentDay::latest()->first())->id;
            $orderCode = 'orderD_' . $latestOrderId . generateRandomString(5);
            $chatId = 'chatD_' . generateRandomString(4);

            $data = SaveRentDay::create([
                'address_now' => $request->address_now,
                'user_id' => $request->user_id,
                'trip_type_id' => 3,
                'order_code' => $orderCode,
                'total_price' => $request->total_price,
                'chat_id' => $chatId,
                'status' => 'accepted',
                'payments' => $request->payments,
                'lat_user' => $request->lat_user,
                'long_user' => $request->long_user,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'number_day' => $request->number_day,
                'start_time' => $request->start_time,
                'commit' => $request->commit,


            ]);
            sendNotificationUser($data->user_id, 'تم حجز الرحله بنجاح', 'حجز الرحله', true);

            return $this->successResponse(new OrdersSaveDayResources($data), 'Data created successfully');

        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }


    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_days,order_code',
            'status' => 'required|in:done,waiting,pending,cancel,accepted',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {
            $findOrder = OrderDay::where('order_code', $request->order_code)->first();

            if (!$findOrder) {
                return $this->errorResponse('Order not found', 404);
            }

            if ($request->status == 'done') {
                $this->completeOrder($findOrder);
            } else {
                $this->updateOrderStatus($findOrder, $request->status);
            }

            return $this->successResponse(new OrdersDayResources($findOrder), 'Data updated successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }
    }

    private function completeOrder(OrderDay $order)
    {
        CaptionActivity::where('captain_id', $order->captain_id)->update(['type_captain' => 'active']);
        $order->update(['status' => 'done']);
        sendNotificationUser($order->user_id, 'لقد تم انتهاء الرحله بنجاح', 'رحله سعيده', true);
        sendNotificationCaptain($order->captain_id, 'لقد تم انتهاء الرحله بنجاح', 'رحله سعيده كابتن', true);
        DeletedInFirebaseDay($order->user_id, $order->captain_id, $order->id);
    }

    private function updateOrderStatus(OrderDay $order, $status)
    {
        $order->update(['status' => $status]);

        sendNotificationUser($order->user_id, 'تغير حاله الطلب', $status, true);
        sendNotificationCaptain($order->captain_id, 'تغير حاله الطلب', $status, true);
    }


    public function canselOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_days,order_code',
            'cansel' => 'required',
            'type' => 'required|in:user,caption',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $findOrder = OrderDay::where('order_code', $request->order_code)->first();

        if (!$findOrder) {
            return $this->errorResponse('Order not found', 404);
        }

        $findOrder->update([
            'status' => 'cancel',
        ]);

        CanselOrderHoursDay::create([
            'type' => $request->type,
            'order_day_id' => $findOrder->id,
            'cansel' => $request->cansel,
            'user_id' => $findOrder->user_id,
            'captain_id' => $findOrder->captain_id,
        ]);

        if ($findOrder->user_id) {
            $this->updateUserProfileForCancel($findOrder->user_id);
        }

        if ($findOrder->captain_id) {
            $this->updateCaptainProfileForCancel($findOrder->captain_id);
        }

        sendNotificationUser($findOrder->user_id, 'تم الغاء الطلب', $request->cansel, true);
        sendNotificationCaptain($findOrder->captain_id, 'تم الغاء الطلب', $request->cansel, true);

        DeletedInFirebaseDay($findOrder->user_id, $findOrder->captain_id, $findOrder->id);

        return $this->successResponse(new OrdersDayResources($findOrder), 'Data updated successfully');
    }


    private function updateUserProfileForCancel($userId)
    {
        $userProfile = UserProfile::where('user_id', $userId)->first();
        if ($userProfile) {
            $userProfile->update([
                'number_trips_cansel_day' => $userProfile->number_trips_cansel_day + 1
            ]);
        }
    }

    private function updateCaptainProfileForCancel($captainId)
    {
        $captainProfile = CaptainProfile::where('captain_id', $captainId)->first();
        if ($captainProfile) {
            $captainProfile->update([
                'number_trips_cansel_day' => $captainProfile->number_trips_cansel_day + 1
            ]);
        }
    }


    public function canselDay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:save_rent_days,order_code'

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $orders = SaveRentDay::where('order_code', $request->order_code)->update([
            'status' => 'cancel'
        ]);
        return $this->successResponse('Data cancel successfully');

    }
}

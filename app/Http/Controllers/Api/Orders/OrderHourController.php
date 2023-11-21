<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrdersHoursResources;
use App\Http\Resources\Orders\OrdersResources;
use App\Http\Resources\Orders\OrdersSaveHoursResources;
use App\Models\CanselOrderHoursDay;
use App\Models\Captain;
use App\Models\CaptainProfile;
use App\Models\CaptionActivity;
use App\Models\OrderHour;
use App\Models\SaveRentHour;
use App\Models\Traits\Api\ApiResponseTrait;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderHourController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_hours,order_code',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {

            $order = OrderHour::where('order_code', $request->order_code)->firstOrFail();
            return $this->successResponse(new OrdersHoursResources($order), 'data return successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'captain_id' => 'required|exists:captains,id',
            'hour_id' => 'required|exists:hours,id',
            'total_price' => 'required|numeric',
            'payments' => 'required|in:cash,masterCard,wallet',
            'lat_user' => 'required',
            'long_user' => 'required',
            'address_now' => 'required',
            'data' => 'required',
            'hours_from' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        if (OrderHour::where('user_id', $request->user_id)->where('status', 'pending')->exists()) {
            return $this->errorResponse('This client is already on a journey');
        }

        if (OrderHour::where('captain_id', $request->captain_id)->where('status', 'pending')->exists()) {
            return $this->errorResponse('This captain is already on a journey');
        }
        try {

            $latestOrderId = optional(OrderHour::latest()->first())->id;
            $orderCode = 'orderH_' . $latestOrderId . generateRandomString(5);
            $chatId = 'chatH_' . generateRandomString(4);

            $user = User::findorfail($request->user_id);
            $caption = Captain::findorfail($request->captain_id);

            $data = OrderHour::create([
                'hour_id' => $request->hour_id,
                'address_now' => $request->address_now,
                'user_id' => $request->user_id,
                'captain_id' => $request->captain_id,
                'trip_type_id' => 2,
                'order_code' => $orderCode,
                'total_price' => $request->total_price,
                'chat_id' => $chatId,
                'status' => 'pending',
                'payments' => $request->payments,
                'lat_user' => $request->lat_user,
                'long_user' => $request->long_user,
                'data' => $request->data,
                'hours_from' => $request->hours_from,
                'commit' => $request->commit,
                'date_created' => Carbon::now()->format('Y-m-d'),

            ]);

            if ($data) {

                sendNotificationCaptain($request->captain_id, 'Trips Created Successfully User ' . $user->name, 'New Trips', true);
                sendNotificationUser($request->user_id, 'Trips Created Successfully Driver ' . $caption->name, 'New Trips', true);

                createInFirebaseHours($request->user_id, $request->captain_id, $data->id);


            }
            return $this->successResponse(new OrdersHoursResources($data), 'Data created successfully');

        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }


    }


    public function saveHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'hour_id' => 'required|exists:hours,id',
            'total_price' => 'required|numeric',
            'payments' => 'required|in:cash,masterCard,wallet',
            'lat_user' => 'required',
            'long_user' => 'required',
            'address_now' => 'required',
            'data' => 'required',
            'hours_from' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $user = User::findOrfail($request->user_id);

        if (SaveRentHour::where('user_id', $request->user_id)->whereNotIn('status', ['done', 'cancel', 'accepted'])->where('data',$request->data)->where('hours_from',$request->hours_from)->first()) {
            return $this->errorResponse('There is a flight already booked for the same date');
        }

        try {

            $latestOrderId = optional(SaveRentHour::latest()->first())->id;
            $orderCode = 'orderH_' . $latestOrderId . generateRandomString(5);
            $chatId = 'chatH_' . generateRandomString(4);

            $data = SaveRentHour::create([
                'hour_id' => $request->hour_id,
                'address_now' => $request->address_now,
                'user_id' => $request->user_id,
                'trip_type_id' => 2,
                'order_code' => $orderCode,
                'total_price' => $request->total_price,
                'chat_id' => $chatId,
                'status' => 'accepted',
                'payments' => $request->payments,
                'lat_user' => $request->lat_user,
                'long_user' => $request->long_user,
                'data' => $request->data,
                'hours_from' => $request->hours_from,
                'commit' => $request->commit,

            ]);
            sendNotificationUser($data->user_id, 'تم حجز الرحله بنجاح', 'حجز الرحله', true);

            return $this->successResponse(new OrdersSaveHoursResources($data), 'Data created successfully');

        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }


    }


    public function canselHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code'=>'required|exists:save_rent_hours,order_code'

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $orders = SaveRentHour::where('order_code',$request->order_code)->update([
            'status'=>'cancel'
        ]);
        return $this->successResponse('Data cancel successfully');

    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_hours,order_code',
            'status' => 'required|in:done,waiting,pending,cancel,accepted',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {
            $findOrder = OrderHour::where('order_code', $request->order_code)->first();


            if (!$findOrder) {
                return $this->errorResponse('Order not found', 404);
            }

            if ($request->status == 'done') {
                $this->completeOrder($findOrder);
            } else {
                $this->updateOrderStatus($findOrder, $request->status);
            }

            return $this->successResponse(new OrdersHoursResources($findOrder), 'Data updated successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong, please try again later');
        }
    }

    private function completeOrder(OrderHour $order)
    {
        CaptionActivity::where('captain_id', $order->captain_id)->update(['type_captain' => 'active']);
        $order->update(['status' => 'done']);
        sendNotificationUser($order->user_id, 'لقد تم انتهاء الرحله بنجاح', 'رحله سعيده', true);
        sendNotificationCaptain($order->captain_id, 'لقد تم انتهاء الرحله بنجاح', 'رحله سعيده كابتن', true);
        DeletedInFirebaseHours($order->user_id, $order->captain_id, $order->id);
    }

    private function updateOrderStatus(OrderHour $order, $status)
    {
        $order->update(['status' => $status]);

        sendNotificationUser($order->user_id, 'تغير حاله الطلب', $status, true);
        sendNotificationCaptain($order->captain_id, 'تغير حاله الطلب', $status, true);
    }


    public function canselOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|exists:order_hours,order_code',
            'cansel' => 'required',
            'type' => 'required|in:user,caption',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        $findOrder = OrderHour::where('order_code', $request->order_code)->first();

        if (!$findOrder) {
            return $this->errorResponse('Order not found', 404);
        }

        $findOrder->update([
            'status' => 'cancel',
        ]);

        CanselOrderHoursDay::create([
            'type' => $request->type,
            'order_hour_id' => $findOrder->id,
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

        DeletedInFirebaseHours($findOrder->user_id, $findOrder->captain_id, $findOrder->id);

        return $this->successResponse(new OrdersHoursResources($findOrder), 'Data updated successfully');
    }


    private function updateUserProfileForCancel($userId)
    {
        $userProfile = UserProfile::where('user_id', $userId)->first();
        if ($userProfile) {
            $userProfile->update([
                'number_trips_cansel_hours' => $userProfile->number_trips_cansel_hours + 1
            ]);
        }
    }

    private function updateCaptainProfileForCancel($captainId)
    {
        $captainProfile = CaptainProfile::where('captain_id', $captainId)->first();
        if ($captainProfile) {
            $captainProfile->update([
                'number_trips_cansel_hours' => $captainProfile->number_trips_cansel_hours + 1
            ]);
        }
    }
}

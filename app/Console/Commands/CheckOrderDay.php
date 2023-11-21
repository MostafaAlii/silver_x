<?php
namespace App\Console\Commands;
use App\Models\{SaveRentDay, UserSaveRend};
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckOrderDay extends Command {
    protected $signature = 'app:check-order-day';

    protected $description = 'Command description';

    /*public function handle()
    {
        $ordersSaveDays = SaveRentDay::get();
        if ($ordersSaveDays->count() > 0) {
            foreach ($ordersSaveDays as $ordersSaveDay) {
                $orders = SaveRentDay::findorfail($ordersSaveDay->id);
                $check = UserSaveRend::where('user_id',$orders->user_id)->first();
                if ($ordersSaveDay->status == 'cancel') {
                    $ordersSaveDay->delete();
                    $this->comment('Deleted Orders status cancel');
                }


                if ($ordersSaveDay->start_day == Carbon::now()->format('Y-m-d')) {

                    $timeDifferenceInMinutes = Carbon::now()->diffInMinutes($ordersSaveDay->start_time);

                    if ($timeDifferenceInMinutes == 20) {
                        if(!$check){
                            UserSaveRend::create(['user_id' => $orders->user_id, 'save_rent_day_id' => $orders->id]);
                            sendNotificationUser($ordersSaveDay->user_id, 'من فضلك قم بتأكيد الرحله', 'تأكيد الرحله', true);
                        }
                        $orders->update([
                            'status' => "accepted"
                        ]);
                    }

                    if ($timeDifferenceInMinutes == 10) {
                        if(!$check){
                            UserSaveRend::create(['user_id' => $orders->user_id, 'save_rent_day_id' => $orders->id]);
                            sendNotificationUser($ordersSaveDay->user_id, 'من فضلك قم بتأكيد الرحله', 'تأكيد الرحله', true);
                        }
                        $orders->update([
                            'status' => "accepted"
                        ]);;
                    }
                    if ($timeDifferenceInMinutes == 5) {
                        if(!$check){
                            UserSaveRend::create(['user_id' => $orders->user_id, 'save_rent_day_id' => $orders->id]);
                            sendNotificationUser($ordersSaveDay->user_id, 'من فضلك قم بتأكيد الرحله', 'تأكيد الرحله', true);
                        }
                        $orders->update([
                            'status' => "accepted"
                        ]);
                    }

                    if ($timeDifferenceInMinutes == 1) {
                        if(!$check){
                            UserSaveRend::create(['user_id' => $orders->user_id, 'save_rent_day_id' => $orders->id]);
                            sendNotificationUser($ordersSaveDay->user_id, 'من فضلك قم بتأكيد الرحله', 'تأكيد الرحله', true);
                        }
                        $orders->update([
                            'status' => "accepted"
                        ]);
                    }

                    $this->comment('Orders Send ' . $timeDifferenceInMinutes);
                }



                // Check Time Out

                $dataCheck = $ordersSaveDay->start_day . $ordersSaveDay->start_time;
                $dataCheckTimeOut = Carbon::parse($dataCheck)->addMinutes(20)->format('Y-m-d g:i A');
                $dataNowCheckTimeOut =Carbon::now()->format('Y-m-d g:i A');

                if ($dataCheckTimeOut == $dataNowCheckTimeOut){
                    sendNotificationUser($ordersSaveDay->user_id, 'لقد تم الغاء الرحله لعدم التأكيد', 'الغاء الرحله', true);
                    $ordersSaveDay->delete();
                }




                //Check Orders Sub Dayes

                $dataCheck = $ordersSaveDay->start_day;

                $dataSub = Carbon::now()->subDay()->format('Y-m-d');
                $checks = $dataCheck == $dataSub;
                if ($checks == true){
                    sendNotificationUser($ordersSaveDay->user_id, 'لقد تم الغاء الرحله لعدم التأكيد', 'الغاء الرحله', true);
                    $ordersSaveDay->delete();
                }

            }
        }
    }*/

    public function handle() {
        $ordersSaveDays = SaveRentDay::all();
        if ($ordersSaveDays->count() > 0) {
            foreach ($ordersSaveDays as $ordersSaveDay) {
                $this->processOrderSaveDay($ordersSaveDay);
            }
        }
    }

    private function processOrderSaveDay($ordersSaveDay) {
        $orders = SaveRentDay::findOrFail($ordersSaveDay->id);
        $check = UserSaveRend::where('user_id', $orders->user_id)->first();
        if ($ordersSaveDay->status == 'cancel') {
            $ordersSaveDay->delete();
            $this->comment('Deleted Orders status cancel');
        }
        $this->checkTimeAndSendNotification($ordersSaveDay, $orders, $check);
        $this->checkTimeoutAndCancelOrder($ordersSaveDay);
        $this->checkSubDayAndCancelOrder($ordersSaveDay);
    }

    private function checkTimeAndSendNotification($ordersSaveDay, $orders, $check) {
        $timeDifferenceInMinutes = Carbon::now()->diffInMinutes($ordersSaveDay->start_time);
        if (in_array($timeDifferenceInMinutes, [20, 10, 5, 1]) && !$check) {
            UserSaveRend::create(['user_id' => $orders->user_id, 'save_rent_day_id' => $orders->id]);
            sendNotificationUser($ordersSaveDay->user_id, 'من فضلك قم بتأكيد الرحله', 'تأكيد الرحله', true);
            $orders->update(['status' => 'accepted']);
        }
        $this->comment('Orders Send ' . $timeDifferenceInMinutes);
    }

    private function checkTimeoutAndCancelOrder($ordersSaveDay) {
        $dataCheck = $ordersSaveDay->start_day . $ordersSaveDay->start_time;
        $dataCheckTimeOut = Carbon::parse($dataCheck)->addMinutes(20)->format('Y-m-d g:i A');
        $dataNowCheckTimeOut = Carbon::now()->format('Y-m-d g:i A');

        if ($dataCheckTimeOut == $dataNowCheckTimeOut) {
            sendNotificationUser($ordersSaveDay->user_id, 'لقد تم الغاء الرحله لعدم التأكيد', 'الغاء الرحله', true);
            $ordersSaveDay->delete();
        }
    }

    private function checkSubDayAndCancelOrder($ordersSaveDay) {
        $dataCheck = $ordersSaveDay->start_day;
        $dataSub = Carbon::now()->subDay()->format('Y-m-d');
        $checks = $dataCheck == $dataSub;
        if ($checks) {
            sendNotificationUser($ordersSaveDay->user_id, 'لقد تم الغاء الرحله لعدم التأكيد', 'الغاء الرحله', true);
            $ordersSaveDay->delete();
        }
    }
}

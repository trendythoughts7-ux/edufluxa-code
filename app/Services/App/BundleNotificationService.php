<?php

namespace App\Services\App;

use App\Mail\SendNotifications;
use App\Models\Bundle;
use App\Models\Notification;

class BundleNotificationService
{
    public function sendNotificationToStudents($id, $data)
    {
        $bundle = Bundle::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                    $query->with([
                        'buyer'
                    ]);
                }
            ])
            ->first();

        if (empty($bundle)) {
            return null;
        }

        foreach ($bundle->sales as $sale) {
            if (!empty($sale->buyer)) {
                $user = $sale->buyer;

                Notification::create([
                    'user_id' => $user->id,
                    'group_id' => null,
                    'sender_id' => auth()->id(),
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'sender' => Notification::$AdminSender,
                    'type' => 'single',
                    'created_at' => time()
                ]);

                if (!empty($user->email) and env('APP_ENV') == 'production') {
                    \Mail::to($user->email)->queue(new SendNotifications(['title' => $data['title'], 'message' => $data['message']]));
                }
            }
        }

        return count($bundle->sales);
    }
}

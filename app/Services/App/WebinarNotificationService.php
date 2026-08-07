<?php

namespace App\Services\App;

use App\Mail\SendNotifications;
use App\Models\BundleWebinar;
use App\Models\File;
use App\Models\Notification;
use App\Models\Session;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use Illuminate\Support\Facades\Validator;

class WebinarNotificationService
{
    public function sendNotificationToStudents($id, $data)
    {
        $webinar = Webinar::where('id', $id)
            ->with([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                    $query->with([
                        'buyer'
                    ]);
                }
            ])
            ->first();

        if (empty($webinar)) {
            return null;
        }

        foreach ($webinar->sales as $sale) {
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

        return count($webinar->sales);
    }

    public function reorderItems($data)
    {
        $validator = Validator::make($data, [
            'items' => 'required',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'errors' => $validator->errors(),
            ];
        }

        $tableName = $data['table'];
        $itemIds = explode(',', $data['items']);

        if (count($itemIds)) {
            switch ($tableName) {
                case 'tickets':
                    foreach ($itemIds as $order => $id) {
                        Ticket::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'sessions':
                    foreach ($itemIds as $order => $id) {
                        Session::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'files':
                    foreach ($itemIds as $order => $id) {
                        File::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'text_lessons':
                    foreach ($itemIds as $order => $id) {
                        TextLesson::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapters':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapter::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapter_items':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapterItem::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                case 'bundle_webinars':
                    foreach ($itemIds as $order => $id) {
                        BundleWebinar::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
            }
        }

        return true;
    }
}

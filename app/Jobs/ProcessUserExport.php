<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\Notification;
use App\Mail\SendNotifications;
use App\Exports\UsersExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class ProcessUserExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $exportId;

    public function __construct($users, $exportId)
    {
        $this->users = $users;
        $this->exportId = $exportId;
    }

    public function handle()
    {
        $export = Export::find($this->exportId);
        if (empty($export)) {
            return;
        }

        $export->update(['status' => Export::STATUS_PROCESSING]);

        try {
            $fileName = 'users_export_' . $export->id . '_' . time() . '.xlsx';
            $absolutePath = storage_path('app/exports/' . $fileName);

            $fileContents = Excel::raw(new UsersExport($this->users), \Maatwebsite\Excel\Excel::XLSX);
            file_put_contents($absolutePath, $fileContents);

            $export->update([
                'status' => Export::STATUS_COMPLETED,
                'file_name' => $fileName,
                'row_count' => $this->users->count(),
                'completed_at' => now(),
                'expires_at' => now()->addDays(3),
            ]);

            $this->notifyUser($export, true);

        } catch (\Exception $exception) {
            $export->update([
                'status' => Export::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);

            $this->notifyUser($export, false);
        }
    }

    protected function notifyUser($export, $success)
    {
        $title = $success ? 'Your export is ready' : 'Your export failed';
        $message = $success
            ? 'Your requested export has been generated and is ready to download.'
            : 'Your requested export could not be completed. Please try again or contact support.';

        Notification::create([
            'user_id' => $export->user_id,
            'sender_id' => $export->user_id,
            'title' => $title,
            'message' => $message,
            'sender' => Notification::$SystemSender,
            'type' => 'single',
            'created_at' => time(),
        ]);

        $user = \App\User::find($export->user_id);
        if (!empty($user) and !empty($user->email)) {
            Mail::to($user->email)->queue(new SendNotifications([
                'title' => $title,
                'message' => $message,
            ]));
        }
    }
}

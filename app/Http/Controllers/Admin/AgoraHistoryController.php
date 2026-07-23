<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AgoraHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\AgoraHistory;
use App\Models\Export;
use App\Jobs\ProcessGenericExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AgoraHistoryController extends Controller
{
    public function index()
    {
        $this->authorize('admin_agora_history_list');

        $agoraHistories = AgoraHistory::whereNotNull('end_at')
            ->orderBy('start_at')
            ->with([
                'session' => function ($query) {
                    $query->with('webinar');
                }
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('update.agora_history'),
            'agoraHistories' => $agoraHistories
        ];

        return view('admin.agora_history.index', $data);
    }

    public function exportExcel(Request $request)
    {
        $agoraHistories = AgoraHistory::whereNotNull('end_at')
            ->orderBy('start_at')
            ->with([
                'session' => function ($query) {
                    $query->with('webinar');
                }
            ])
            ->get();

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'agora_history',
            'status' => Export::STATUS_PENDING,
        ]);

        ProcessGenericExport::dispatch(AgoraHistoryExport::class, $agoraHistories, $exportRecord->id, 'agora_history');

        return redirect()->back()->with('toast', [
            'status' => 'success',
            'title' => 'Export',
            'msg' => 'Your export is processing. You will get a notification when it is ready to download.',
        ]);
    }

}

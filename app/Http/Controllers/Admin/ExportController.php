<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function download($exportId)
    {
        $export = Export::find($exportId);

        if (empty($export)) {
            abort(404, 'Export not found.');
        }

        if ($export->user_id !== auth()->id()) {
            abort(403, 'You are not authorized to access this export.');
        }

        if ($export->status !== Export::STATUS_COMPLETED) {
            abort(404, 'Export is not ready for download.');
        }

        $filePath = storage_path('app/exports/' . $export->file_name);

        if (!file_exists($filePath)) {
            abort(404, 'Export file no longer exists.');
        }

        return response()->download($filePath, $export->file_name);
    }
}

<?php

namespace App\Services\App;

use App\Models\Sale;
use App\Models\Webinar;
use App\Exports\WebinarStudents;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Facades\DB;

class InstructorWebinarManagementService
{

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!$webinar) {
            abort(404);
        }

        $webinar->delete();

        return response()->json([
            'code' => 200,
            'redirect_to' => $request->get('redirect_to')
        ], 200);
    }

    public function duplicate($id)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })
            ->first();

        if (!empty($webinar)) {
            $tableColumns = Schema::getColumnListing($webinar->getTable());
            $new = [];
            foreach ($tableColumns as $col) {
                if (in_array($col, ['id', 'slug', 'created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }
                $new[$col] = $webinar->getAttribute($col);
            }

            foreach ($webinar->translatedAttributes as $attr) {
                $new[$attr] = $webinar->getAttribute($attr);
            }

            $newTitle = $new['title'] . ' ' . trans('public.copy');
            $new['title'] = $newTitle;
            $new['created_at'] = time();
            $new['updated_at'] = time();
            $new['status'] = Webinar::$pending;
            $new['slug'] = 'temp-' . uniqid();

            $newWebinar = Webinar::create($new);

            $realSlug = SlugService::createSlug($newWebinar, 'slug', $newTitle);
            DB::table($newWebinar->getTable())->where('id', $newWebinar->id)->update(['slug' => $realSlug]);

            return redirect('/panel/courses/' . $newWebinar->id . '/edit');
        }

        abort(404);
    }
    public function exportStudentsList($id)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })
            ->first();

        if (!empty($webinar)) {
            $sales = Sale::where('type', 'webinar')
                ->where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->whereHas('buyer')
                ->with([
                    'buyer' => function ($query) {
                        $query->select('id', 'full_name', 'email', 'mobile');
                    }
                ])->get();

            if (!empty($sales) and !$sales->isEmpty()) {
                $export = new WebinarStudents($sales);
                return Excel::download($export, trans('panel.users') . '.xlsx');
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('webinars.export_list_error_not_student'),
                'status' => 'error'
            ];

            return back()->with(['toast' => $toastData]);
        }

        abort(404);
    }
}

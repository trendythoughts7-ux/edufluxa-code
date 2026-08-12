<?php // @phpstan-ignore-line
 // @phpstan-ignore-line
namespace App\Http\Controllers\Admin; // @phpstan-ignore-line
 // @phpstan-ignore-line
use App\Http\Controllers\Controller; // @phpstan-ignore-line
use App\Models\Session; // @phpstan-ignore-line
use App\Models\Translation\SessionTranslation; // @phpstan-ignore-line
use App\Models\Webinar; // @phpstan-ignore-line
use App\Models\WebinarChapterItem; // @phpstan-ignore-line
use App\Sessions\Zoom; // @phpstan-ignore-line
use App\Sessions\ZoomOAuth; // @phpstan-ignore-line
use Illuminate\Http\Request; // @phpstan-ignore-line
use Illuminate\Support\Carbon; // @phpstan-ignore-line
use Validator; // @phpstan-ignore-line
 // @phpstan-ignore-line
class SessionController extends Controller // @phpstan-ignore-line
{ // @phpstan-ignore-line
    public function store(Request $request) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $this->authorize('admin_webinars_edit'); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $data = $request->get('ajax')['new']; // @phpstan-ignore-line
 // @phpstan-ignore-line
        $validator = Validator::make($data, [ // @phpstan-ignore-line
            'webinar_id' => 'required', // @phpstan-ignore-line
            'chapter_id' => 'required', // @phpstan-ignore-line
            'title' => 'required|max:64', // @phpstan-ignore-line
            'date' => 'required|date', // @phpstan-ignore-line
            'duration' => 'required|numeric', // @phpstan-ignore-line
            'link' => ($data['session_api'] == 'local') ? 'required|url' : 'nullable', // @phpstan-ignore-line
            'api_secret' => (in_array($data['session_api'], ['zoom', 'agora', 'jitsi'])) ? 'nullable' : 'required', // @phpstan-ignore-line
            'moderator_secret' => ($data['session_api'] == 'big_blue_button') ? 'required' : 'nullable', // @phpstan-ignore-line
        ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($validator->fails()) { // @phpstan-ignore-line
            return response([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => $validator->errors(), // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') { // @phpstan-ignore-line
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on'); // @phpstan-ignore-line
            $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null; // @phpstan-ignore-line
        } else { // @phpstan-ignore-line
            $data['check_previous_parts'] = false; // @phpstan-ignore-line
            $data['access_after_day'] = null; // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($data['webinar_id'])) { // @phpstan-ignore-line
            $webinar = Webinar::where('id', $data['webinar_id'])->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!empty($webinar)) { // @phpstan-ignore-line
                $teacher = $webinar->creator; // @phpstan-ignore-line
 // @phpstan-ignore-line
                if (!empty($data['session_api']) and $data['session_api'] == 'zoom' and empty(getFeaturesSettings('zoom_client_id'))) { // @phpstan-ignore-line
                    $error = [ // @phpstan-ignore-line
                        'zoom-not-complete-alert' => [] // @phpstan-ignore-line
                    ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
                    return response([ // @phpstan-ignore-line
                        'code' => 422, // @phpstan-ignore-line
                        'errors' => $error, // @phpstan-ignore-line
                    ], 422); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
 // @phpstan-ignore-line
                $sessionDate = convertTimeToUTCzone($data['date'], $webinar->timezone); // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($sessionDate->getTimestamp() < $webinar->start_date) { // @phpstan-ignore-line
                    $error = [ // @phpstan-ignore-line
                        'date' => [trans('webinars.session_date_must_larger_webinar_start_date', ['start_date' => dateTimeFormat($webinar->start_date, 'j M Y')])] // @phpstan-ignore-line
                    ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
                    return response([ // @phpstan-ignore-line
                        'code' => 422, // @phpstan-ignore-line
                        'errors' => $error, // @phpstan-ignore-line
                    ], 422); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                $session = Session::create([ // @phpstan-ignore-line
                    'creator_id' => $teacher->id, // @phpstan-ignore-line
                    'webinar_id' => $data['webinar_id'], // @phpstan-ignore-line
                    'chapter_id' => $data['chapter_id'] ?? null, // @phpstan-ignore-line
                    'date' => $sessionDate->getTimestamp(), // @phpstan-ignore-line
                    'duration' => $data['duration'], // @phpstan-ignore-line
                    'extra_time_to_join' => $data['extra_time_to_join'] ?? null, // @phpstan-ignore-line
                    'link' => $data['link'] ?? '', // @phpstan-ignore-line
                    'session_api' => $data['session_api'], // @phpstan-ignore-line
                    'api_secret' => $data['api_secret'] ?? '', // @phpstan-ignore-line
                    'moderator_secret' => $data['moderator_secret'] ?? '', // @phpstan-ignore-line
                    'check_previous_parts' => $data['check_previous_parts'], // @phpstan-ignore-line
                    'access_after_day' => $data['access_after_day'], // @phpstan-ignore-line
                    'enable_attendance' => (!empty($data['enable_attendance']) and $data['enable_attendance'] == 'on'), // @phpstan-ignore-line
                    'status' => (!empty($data['status']) and $data['status'] == 'on') ? Session::$Active : Session::$Inactive, // @phpstan-ignore-line
                    'created_at' => time() // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                if (!empty($session)) { // @phpstan-ignore-line
                    SessionTranslation::updateOrCreate([ // @phpstan-ignore-line
                        'session_id' => $session->id, // @phpstan-ignore-line
                        'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                    ], [ // @phpstan-ignore-line
                        'title' => $data['title'], // @phpstan-ignore-line
                        'description' => $data['description'], // @phpstan-ignore-line
                    ]); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($data['session_api'] == 'big_blue_button') { // @phpstan-ignore-line
                    $this->handleBigBlueButtonApi($session, $teacher); // @phpstan-ignore-line
                } elseif ($data['session_api'] == 'zoom') { // @phpstan-ignore-line
                    $zoomResult = $this->handleZoomApi($session, $teacher); // @phpstan-ignore-line
 // @phpstan-ignore-line
                    if ($zoomResult != "ok") { // @phpstan-ignore-line
                        return $zoomResult; // @phpstan-ignore-line
                    } // @phpstan-ignore-line
                } else if ($data['session_api'] == 'agora') { // @phpstan-ignore-line
                    $agoraSettings = [ // @phpstan-ignore-line
                        'chat' => (!empty($data['agora_chat']) and $data['agora_chat'] == 'on'), // @phpstan-ignore-line
                        'record' => (!empty($data['agora_record']) and $data['agora_record'] == 'on'), // @phpstan-ignore-line
                        'users_join' => true, // @phpstan-ignore-line
                    ]; // @phpstan-ignore-line
                    $session->agora_settings = json_encode($agoraSettings); // @phpstan-ignore-line
 // @phpstan-ignore-line
                    $session->save(); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                if (!empty($session) and !empty($session->chapter_id)) { // @phpstan-ignore-line
                    WebinarChapterItem::makeItem($webinar->creator_id, $session->chapter_id, $session->id, WebinarChapterItem::$chapterSession); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                $webinar->update([ // @phpstan-ignore-line
                    'updated_at' => time() // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
 // @phpstan-ignore-line
                return response()->json([ // @phpstan-ignore-line
                    'code' => 200, // @phpstan-ignore-line
                ], 200); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function update(Request $request, $id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $this->authorize('admin_webinars_edit'); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $data = $request->get('ajax')[$id]; // @phpstan-ignore-line
        $session = Session::where('id', $id) // @phpstan-ignore-line
            ->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $session_api = !empty($data['session_api']) ? $data['session_api'] : $session->session_api; // @phpstan-ignore-line
 // @phpstan-ignore-line
        $validator = Validator::make($data, [ // @phpstan-ignore-line
            'webinar_id' => 'required', // @phpstan-ignore-line
            'chapter_id' => 'required', // @phpstan-ignore-line
            'title' => 'required|max:64', // @phpstan-ignore-line
            'date' => ($session_api == 'local') ? 'required|date' : 'nullable', // @phpstan-ignore-line
            'duration' => ($session_api == 'local') ? 'required|numeric' : 'nullable', // @phpstan-ignore-line
            'link' => ($session_api == 'local') ? 'required|url' : 'nullable', // @phpstan-ignore-line
        ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($validator->fails()) { // @phpstan-ignore-line
            return response([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => $validator->errors(), // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') { // @phpstan-ignore-line
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on'); // @phpstan-ignore-line
            $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null; // @phpstan-ignore-line
        } else { // @phpstan-ignore-line
            $data['check_previous_parts'] = false; // @phpstan-ignore-line
            $data['access_after_day'] = null; // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        $webinar = Webinar::where('id', $data['webinar_id'])->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($webinar)) { // @phpstan-ignore-line
            if (!empty($session)) { // @phpstan-ignore-line
                $sessionDate = $session->date; // @phpstan-ignore-line
 // @phpstan-ignore-line
                if (!empty($data['date'])) { // @phpstan-ignore-line
                    $sessionDate = convertTimeToUTCzone($data['date'], $webinar->timezone); // @phpstan-ignore-line
 // @phpstan-ignore-line
                    if ($sessionDate->getTimestamp() < $webinar->start_date) { // @phpstan-ignore-line
                        $error = [ // @phpstan-ignore-line
                            'date' => [trans('webinars.session_date_must_larger_webinar_start_date', ['start_date' => dateTimeFormat($webinar->start_date, 'j M Y')])] // @phpstan-ignore-line
                        ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
                        return response([ // @phpstan-ignore-line
                            'code' => 422, // @phpstan-ignore-line
                            'errors' => $error, // @phpstan-ignore-line
                        ], 422); // @phpstan-ignore-line
                    } // @phpstan-ignore-line
 // @phpstan-ignore-line
                    $sessionDate = $sessionDate->getTimestamp(); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                $agoraSettings = null; // @phpstan-ignore-line
                if ($session_api == 'agora') { // @phpstan-ignore-line
                    $agoraSettings = [ // @phpstan-ignore-line
                        'chat' => (!empty($data['agora_chat']) and $data['agora_chat'] == 'on'), // @phpstan-ignore-line
                        'record' => (!empty($data['agora_record']) and $data['agora_record'] == 'on'), // @phpstan-ignore-line
                        'users_join' => true, // @phpstan-ignore-line
                    ]; // @phpstan-ignore-line
                    $agoraSettings = json_encode($agoraSettings); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                $changeChapter = ($data['chapter_id'] != $session->chapter_id); // @phpstan-ignore-line
                $oldChapterId = $session->chapter_id; // @phpstan-ignore-line
 // @phpstan-ignore-line
                $session->update([ // @phpstan-ignore-line
                    'chapter_id' => $data['chapter_id'], // @phpstan-ignore-line
                    'date' => $sessionDate, // @phpstan-ignore-line
                    'duration' => $data['duration'] ?? $session->duration, // @phpstan-ignore-line
                    'extra_time_to_join' => $data['extra_time_to_join'] ?? null, // @phpstan-ignore-line
                    'link' => $data['link'] ?? $session->link, // @phpstan-ignore-line
                    'session_api' => $session_api, // @phpstan-ignore-line
                    'api_secret' => $data['api_secret'] ?? $session->api_secret, // @phpstan-ignore-line
                    'status' => (!empty($data['status']) and $data['status'] == 'on') ? Session::$Active : Session::$Inactive, // @phpstan-ignore-line
                    'agora_settings' => $agoraSettings, // @phpstan-ignore-line
                    'check_previous_parts' => $data['check_previous_parts'], // @phpstan-ignore-line
                    'access_after_day' => $data['access_after_day'], // @phpstan-ignore-line
                    'enable_attendance' => (!empty($data['enable_attendance']) and $data['enable_attendance'] == 'on'), // @phpstan-ignore-line
                    'updated_at' => time() // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                SessionTranslation::updateOrCreate([ // @phpstan-ignore-line
                    'session_id' => $session->id, // @phpstan-ignore-line
                    'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                ], [ // @phpstan-ignore-line
                    'title' => $data['title'], // @phpstan-ignore-line
                    'description' => $data['description'], // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($changeChapter) { // @phpstan-ignore-line
                    WebinarChapterItem::changeChapter($session->creator_id, $oldChapterId, $session->chapter_id, $session->id, WebinarChapterItem::$chapterSession); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                $webinar->update([ // @phpstan-ignore-line
                    'updated_at' => time() // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                removeContentLocale(); // @phpstan-ignore-line
 // @phpstan-ignore-line
                return response()->json([ // @phpstan-ignore-line
                    'code' => 200, // @phpstan-ignore-line
                ], 200); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        removeContentLocale(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function destroy(Request $request, $id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $this->authorize('admin_webinars_edit'); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $session = Session::find($id); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($session)) { // @phpstan-ignore-line
            WebinarChapterItem::where('user_id', $session->creator_id) // @phpstan-ignore-line
                ->where('item_id', $session->id) // @phpstan-ignore-line
                ->where('type', WebinarChapterItem::$chapterSession) // @phpstan-ignore-line
                ->delete(); // @phpstan-ignore-line
 // @phpstan-ignore-line
            $session->delete(); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([ // @phpstan-ignore-line
            'code' => 200, // @phpstan-ignore-line
        ], 200); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    private function handleZoomApi($session, $user) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        try { // @phpstan-ignore-line
            if (!empty(getFeaturesSettings('zoom_client_id')) and !empty(getFeaturesSettings('zoom_client_secret'))) { // @phpstan-ignore-line
                $meeting = (new ZoomOAuth())->makeMeeting($session); // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($meeting) { // @phpstan-ignore-line
                    return "ok"; // @phpstan-ignore-line
                } else { // @phpstan-ignore-line
                    $session->delete(); // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } catch (\Exception $exception) { // @phpstan-ignore-line
            $session->delete(); // @phpstan-ignore-line
            //dd($exception); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([ // @phpstan-ignore-line
            'code' => 422, // @phpstan-ignore-line
            'status' => 'zoom_token_invalid', // @phpstan-ignore-line
            'zoom_error_msg' => trans('update.zoom_error_msg') // @phpstan-ignore-line
        ], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    private function handleBigBlueButtonApi($session, $user) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $this->handleBigBlueButtonConfigs(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $createMeeting = \Bigbluebutton::initCreateMeeting([ // @phpstan-ignore-line
            'meetingID' => $session->id, // @phpstan-ignore-line
            'meetingName' => $session->title, // @phpstan-ignore-line
            'attendeePW' => $session->api_secret, // @phpstan-ignore-line
            'moderatorPW' => $session->moderator_secret, // @phpstan-ignore-line
        ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $createMeeting->setDuration($session->duration); // @phpstan-ignore-line
        $response = \Bigbluebutton::create($createMeeting); // @phpstan-ignore-line
 // @phpstan-ignore-line
        return true; // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    private function handleBigBlueButtonConfigs() // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $settings = getFeaturesSettings(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        \Config::set("bigbluebutton.BBB_SECURITY_SALT", !empty($settings['bigbluebutton_security_salt']) ? $settings['bigbluebutton_security_salt'] : ''); // @phpstan-ignore-line
        \Config::set("bigbluebutton.BBB_SERVER_BASE_URL", !empty($settings['bigbluebutton_server_base_url']) ? $settings['bigbluebutton_server_base_url'] : ''); // @phpstan-ignore-line
    } // @phpstan-ignore-line
} // @phpstan-ignore-line

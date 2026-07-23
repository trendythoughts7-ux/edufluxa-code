<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- Zoom Client View CSS -->
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.5/css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.8.5/css/react-select.css" />

    <style>
        body {
            padding-top: 0 !important;
            background-color: #000;
        }
    </style>
</head>

<body>
    <!-- Root element for Zoom -->
    <div id="zmmtg-root"></div>

    <!-- Dependencies -->
    <script src="https://source.zoom.us/3.8.5/lib/vendor/react.min.js" crossorigin="anonymous"></script>
    <script src="https://source.zoom.us/3.8.5/lib/vendor/react-dom.min.js" crossorigin="anonymous"></script>
    <script src="https://source.zoom.us/3.8.5/lib/vendor/redux.min.js" crossorigin="anonymous"></script>
    <script src="https://source.zoom.us/3.8.5/lib/vendor/redux-thunk.min.js" crossorigin="anonymous"></script>
    <script src="https://source.zoom.us/3.8.5/lib/vendor/lodash.min.js" crossorigin="anonymous"></script>

    <!-- Zoom Client View JS -->
    <script src="https://source.zoom.us/zoom-meeting-3.8.5.min.js" crossorigin="anonymous"></script>

    <script>
        console.log("Zoom Script Loaded. Initializing...");

        ZoomMtg.setZoomJSLib('https://source.zoom.us/3.8.5/lib', '/av');
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();
        ZoomMtg.i18n.load('en-US');
        ZoomMtg.i18n.reload('en-US');

        var meetingNumber = "{{ $session->zoom_id }}";
        var passWord = "{{ $session->api_secret }}";
        var role = parseInt("{{ ($role == "moderator") ? 1 : 0 }}");
        var userName = "{{ $authUser->full_name }}";
        var userEmail = "{{ $authUser->email }}";
        var sdkKey = "{{ $sdkKey }}";
        var signature = "{{ $zoomIdSignature }}";
        var isModerator = "{{ $role == 'moderator' ? 1 : 0 }}";
        var leaveUrl = "{{ url('/panel/') }}";
        if (isModerator == "1") {
            leaveUrl = "{{ url('/panel/sessions/'.$session->id.'/endZoom') }}";
        }

        function startMeeting() {
            try {
                console.log("Calling Init...");
                ZoomMtg.init({
                    leaveUrl: leaveUrl,
                    success: function() {
                        console.log("Init Success. Joining...");
                        ZoomMtg.join({
                            signature: signature,
                            meetingNumber: meetingNumber,
                            userName: userName,
                            sdkKey: sdkKey,
                            userEmail: userEmail,
                            passWord: passWord,
                            success: function(res) {
                                console.log("Join Success:", res);
                            },
                            error: function(res) {
                                console.error("Join Error:", res);
                            }
                        });
                    },
                    error: function(res) {
                        console.error("Init Error:", res);
                    }
                });
            } catch (e) {
                console.error("Critical JS Error:", e);
            }
        }

        window.addEventListener('load', function() {
            console.log("Window loaded, starting meeting...");
            startMeeting();
        });
    </script>
</body>
</html>

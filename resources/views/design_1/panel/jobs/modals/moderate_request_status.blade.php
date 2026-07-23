<form class="js-moderate-job-request-form" action="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/moderate/store" method="post">
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="d-flex-center flex-column text-center my-16">
        <div class="">
            <img src="/assets/design_1/img/jobs/{{ $type }}_request_modal.svg" alt="" class="img-fluid" height="160px">
        </div>

        <h4 class="font-14 mt-12">{{ trans("update.{$type}_job_application_request") }}</h4>
        <p class="font-12 mt-8 text-gray-500">{{ trans("update.{$type}_job_application_request_modal_hint") }}</p>
    </div>

    <div class="form-group mt-24">
        <label class="form-group-label">{{ trans("update.{$type}_reason") }}</label>
        <select name="reason_id" class="js-ajax-reason_id form-control select2" data-dropdown-parent=".js-custom-modal">
            <option value="">{{ trans('update.select_a_reason') }}</option>

            @foreach($moderateReasons as $moderateReason)
                <option value="{{ $moderateReason->id }}">{{ $moderateReason->title }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group ">
        <label class="form-group-label">{{ trans('update.additional_description') }}</label>
        <textarea name="description" class="js-ajax-description form-control " rows="4"></textarea>

        <div class="invalid-feedback"></div>
    </div>
</form>

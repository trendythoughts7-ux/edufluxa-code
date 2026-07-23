@if(!empty($jobRequest->attachments) and $jobRequest->attachments->isNotEmpty())
    <h3 class="mt-24 font-16">{{ trans('update.attachments') }}</h3>
    <p class="mt-4 font-12 text-gray-500">{{ trans('update.review_the_attachments_earned_by_the_applicant_on_the_platform') }}</p>

    <div class="row">
        @foreach($jobRequest->attachments as $attachment)
            <div class="col-12 col-md-6 mt-16">
                <div class="d-flex gap-16 align-items-center justify-content-between p-16 rounded-12 border-gray-200">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <x-iconsax-bul-document-download class="icons text-primary" width="24px" height="24px"/>
                        </div>

                        <div class="ml-8">
                            <h6 class="font-12">{{ $attachment->title }}</h6>
                            <p class="mt-4 font-12 text-gray-500">{{ $attachment->getFileSize() }}</p>
                        </div>
                    </div>

                    <a href="/panel/jobs/my-requests/{{ $jobRequest->id }}/details/download-item?type=attachment&item={{ $attachment->id }}" class="d-flex-center" data-tippy-content="{{ trans('home.download') }}">
                        <x-iconsax-lin-import-2 class="icons text-gray-500" width="24px" height="24px"/>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

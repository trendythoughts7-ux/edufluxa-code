@if(!empty($userCertificates) and $userCertificates->isNotEmpty())
    <h3 class="mt-24 font-16">{{ trans('update.share_your_certificates') }}</h3>
    <p class="mt-4 font-12 text-gray-500">{{ trans('update.share_your_earned_certificates_with_the_employer') }}</p>

    <div class="row">
        @foreach($userCertificates as $userCertificate)
            @php
                $itemIcon = $userCertificate->getItemIcon();
            @endphp

            <div class="col-12 col-md-6 mt-16">
                <div class="custom-input-button with-active-text position-relative">
                    <input type="checkbox" name="certificates[]" id="certificate_{{ $userCertificate->id }}" value="{{ $userCertificate->id }}">
                    <label for="certificate_{{ $userCertificate->id }}" class="position-relative justify-content-start p-16 rounded-12">

                        @if(!empty($itemIcon))
                            <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                                <img src="{{ $itemIcon }}" alt="{{ $userCertificate->type }}" class="img-cover rounded-circle">
                            </div>
                        @else
                            <div class="">
                                @if(!empty($userCertificate->quiz_id))
                                    <x-iconsax-bul-clipboard-tick class="icons text-primary" width="24px" height="24px"/>
                                @else
                                    <x-iconsax-bul-teacher class="icons text-primary" width="24px" height="24px"/>
                                @endif
                            </div>
                        @endif

                        <div class="ml-8">
                            @if (!empty($userCertificate->quiz_id) and !empty($userCertificate->quiz))
                                <h6 class="font-12 text-ellipsis">{{ !empty($userCertificate->quiz->webinar) ? $userCertificate->quiz->webinar->title : trans('product.course') }}</h6>
                                <p class="mt-4 font-12 text-gray-500">{{ $userCertificate->quiz->title }} . {{ trans('quiz.quiz') }}</p>
                            @elseif (!empty($userCertificate->bundle_id) and !empty($userCertificate->bundle))
                                <h6 class="font-12 text-ellipsis">{{ $userCertificate->bundle->title }}</h6>
                                <p class="mt-4 font-12 text-gray-500">{{ trans('update.bundle_completion') }}</p>
                            @elseif (!empty($userCertificate->webinar_id) and !empty($userCertificate->webinar))
                                <h6 class="font-12 text-ellipsis">{{ $userCertificate->webinar->title }}</h6>
                                <p class="mt-4 font-12 text-gray-500">{{ trans('update.course_completion') }}</p>
                            @else
                                <h6 class="font-12 text-ellipsis">{{ trans("update.{$userCertificate->type}_certificate") }}</h6>
                                <p class="mt-4 font-12 text-gray-500">{{ $userCertificate->getItemTitle() }}</p>
                            @endif
                        </div>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
@endif

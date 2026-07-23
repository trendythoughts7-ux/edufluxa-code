@extends('design_1.panel.layouts.panel')

@section('content')

    {{-- Top Stats --}}
    @if(!empty($referredUsersCount) || isset($registrationBonus) || isset($affiliateBonus))
        @include('design_1.panel.marketing.affiliates.top_stats')
    @endif

    <div class="row">
        <div class="col-12 col-lg-4 mt-20">
            <div class="bg-white p-16 rounded-24">
                <h3 class="font-16 font-weight-bold pb-16 border-bottom-gray-100">{{ trans('panel.affiliate_summary') }}</h3>

                <div class="d-flex-center mt-32">
                    <div class="affiliate-summary-img">
                        <img src="/assets/design_1/img/panel/affiliate/affiliate_summary.svg" alt="affiliate_summary" class="img-cover">
                    </div>
                </div>

                @php
                    $referralSettings = $referralSettings ?? getReferralSettings();
                @endphp

                @if(!empty($referralSettings))
                    @if(!empty($referralSettings['affiliate_user_amount']))
                        <div class="d-flex align-items-center mt-16">
                            <div class="d-flex-center size-48 rounded-12 bg-gray-100">
                                <x-iconsax-lin-money-recive class="icons text-primary" width="24px" height="24px"/>
                            </div>
                            <div class="ml-8">
                                <h5 class="font-16 font-weight-bold">{{ handlePrice($referralSettings['affiliate_user_amount']) }}</h5>
                                <p class="mt-2 font-12 text-gray-500">{{ trans('update.your_referral_user_registration_amount') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(!empty($referralSettings['referred_user_amount']))
                        <div class="d-flex align-items-center mt-16">
                            <div class="d-flex-center size-48 rounded-12 bg-gray-100">
                                <x-iconsax-lin-profile-add class="icons text-primary" width="24px" height="24px"/>
                            </div>
                            <div class="ml-8">
                                <h5 class="font-16 font-weight-bold">{{ handlePrice($referralSettings['referred_user_amount']) }}</h5>
                                <p class="mt-2 font-12 text-gray-500">{{ trans('update.referred_user_registration_amount') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(!empty($referralSettings['affiliate_user_commission']))
                        <div class="d-flex align-items-center mt-16">
                            <div class="d-flex-center size-48 rounded-12 bg-gray-100">
                                <x-iconsax-lin-empty-wallet class="icons text-primary" width="24px" height="24px"/>
                            </div>
                            <div class="ml-8">
                                <h5 class="font-16 font-weight-bold">{{ $referralSettings['affiliate_user_commission'] }}%</h5>
                                <p class="mt-2 font-12 text-gray-500">{{ trans('update.purchase_commission') }}</p>
                            </div>
                        </div>
                    @endif

                    @if(!empty($referralSettings['course_affiliate_commission']))
                        <div class="d-flex align-items-center mt-16">
                            <div class="d-flex-center size-48 rounded-12 bg-gray-100">
                                <x-iconsax-lin-book class="icons text-primary" width="24px" height="24px"/>
                            </div>
                            <div class="ml-8">
                                <h5 class="font-16 font-weight-bold">{{ $referralSettings['course_affiliate_commission'] }}%</h5>
                                <p class="mt-2 font-12 text-gray-500">{{ trans('update.course_affiliate_commission') }}</p>
                            </div>
                        </div>
                    @endif

                @endif

            </div>
        </div>

        {{-- Your Affiliate Information --}}
        <div class="col-12 col-lg-4 mt-20">
            <div class="bg-white p-16 rounded-24">
                <h3 class="font-16 font-weight-bold pb-16 border-bottom-gray-100">{{ trans('update.your_affiliate_information') }}</h3>

                <div class="d-flex-center flex-column text-center mt-32">
                    <div class="affiliate-summary-img">
                        <img src="/assets/design_1/img/panel/affiliate/your_affiliate_information.svg" alt="your_affiliate_information" class="img-cover">
                    </div>

                    <div class="d-flex-center mt-16 p-8 rounded-32 bg-gray-100 form-group mb-0">
                        <span class="font-16 font-weight-bold mr-8">{{ $affiliateCode->code }}</span>

                        <button type="button" class="js-copy-input btn-transparent d-flex" data-tippy-content="{{ trans('public.copy') }}" data-copy-text="{{ trans('public.copy') }}" data-done-text="{{ trans('public.copied') }}">
                            <x-iconsax-lin-document-copy class="icons text-gray-400" width="20px" height="20px"/>
                        </button>
                        <input type="hidden" class="form-control" value="{{ $affiliateCode->code }}">
                    </div>

                    <h4 class="mt-12 font-14 font-weight-bold">{{ trans('panel.your_affiliate_code') }}</h4>

                    @if(!empty($referralSettings['referral_description']))
                        <p class="mt-8 font-12 text-gray-500 text-center">{{ $referralSettings['referral_description'] }}</p>
                    @endif
                </div>

                <div class="form-group mb-0 mt-28">
                    <label class="form-group-label">{{ trans('panel.affiliate_url') }}</label>

                    <button type="button" class="js-copy-input has-translation btn-transparent d-flex" data-tippy-content="{{ trans('public.copy') }}" data-copy-text="{{ trans('public.copy') }}" data-done-text="{{ trans('public.copied') }}">
                        <x-iconsax-lin-document-copy class="icons text-gray-400" width="24px" height="24px"/>
                    </button>

                    <input type="text" name="affiliate_url" readonly value="{{ $affiliateCode->getAffiliateUrl() }}" class="form-control"/>
                </div>
            </div>
        </div>


        {{-- How it works? --}}
        <div class="col-12 col-lg-4 mt-20">
            <div class="bg-white p-16 rounded-24">
                <h3 class="font-16 font-weight-bold pb-16 border-bottom-gray-100">{{ trans('update.how_it_works') }}</h3>

                @if(!empty($referralHowWorkSettings))
                    <div class="d-flex-center flex-column text-center mt-32">
                        @if(!empty($referralHowWorkSettings['image']))
                            <div class="affiliate-summary-img">
                                <img src="{{ $referralHowWorkSettings['image'] }}" alt="how_it_works" class="img-cover">
                            </div>
                        @endif
                    </div>

                    @if(!empty($referralHowWorkSettings['description']))
                        <p class="mt-16 font-14 text-gray-500 white-space-pre-wrap">{{ $referralHowWorkSettings['description'] }}</p>
                    @endif
                @endif
            </div>
        </div>
    </div>


    {{-- Tabs --}}
    <div class="bg-white rounded-24 pt-16 mt-24">
        <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
            <div class="">
                <h3 class="font-16">{{ trans('update.affiliate_history') }}</h3>
                <p class="font-14 text-gray-500 mt-4">{{ trans('update.manage_your_affiliate_links_and_referred_users') }}</p>
            </div>

            @if(($activeTab ?? 'referred_users') == 'courses')
                <button type="button" class="btn btn-sm btn-primary" id="generateLinkBtn">
                    <x-iconsax-lin-add class="icons mr-4" width="16px" height="16px"/>
                    {{ trans('update.generate_link') }}
                </button>
            @endif
        </div>

        <div class="d-flex align-items-center border-bottom-gray-100 border-top-gray-100 px-16">
            @php
                $activeTab = $activeTab ?? 'referred_users';
            @endphp

            <a href="/panel/marketing/affiliates" class="navbar-item navbar-item-h-52 d-flex align-items-center mr-20 mr-md-40 cursor-pointer {{ $activeTab == 'referred_users' ? 'active' : '' }}">
                <x-iconsax-lin-profile-2user class="icons" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('panel.referred_users') }}</span>
            </a>

            <a href="/panel/marketing/affiliates/courses" class="navbar-item navbar-item-h-52 d-flex align-items-center mr-20 mr-md-40 cursor-pointer {{ $activeTab == 'courses' ? 'active' : '' }}">
                <x-iconsax-lin-book class="icons" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('update.courses_affiliate_links') }}</span>
            </a>
        </div>

        {{-- Tab Content: Referred Users --}}
        @if($activeTab == 'referred_users')
            @if(!empty($affiliates) and !$affiliates->isEmpty())
                <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/marketing/affiliates">
                    <table class="table panel-table">
                        <thead>
                        <tr>
                            <th class="text-left">{{ trans('panel.user') }}</th>
                            <th class="text-center">{{ trans('panel.registration_bonus') }}</th>
                            <th class="text-center">{{ trans('panel.affiliate_bonus') }}</th>
                            <th class="text-center">{{ trans('panel.registration_date') }}</th>
                        </tr>
                        </thead>
                        <tbody class="js-table-body-lists">
                        @foreach($affiliates as $affiliateRow)
                            @include('design_1.panel.marketing.affiliates.table_items', ['affiliate' => $affiliateRow])
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer" data-container-items=".js-table-body-lists">
                        {!! $pagination !!}
                    </div>
                </div>
            @else
                @include('design_1.panel.includes.no-result',[
                    'file_name' => 'affiliate.svg',
                    'title' => trans('update.your_affiliate_list_is_empty'),
                    'hint' => nl2br(trans('update.when_a_user_Referred_by_you_it_will_be_appeared_here')),
                ])
            @endif
        @endif

        {{-- Tab Content: Courses Affiliate Links --}}
        @if($activeTab == 'courses')
            @if(!empty($affiliateItems) and count($affiliateItems) > 0)
                <div class="table-responsive-lg mt-16">
                    <table class="table panel-table">
                        <thead>
                        <tr>
                            <th class="text-left" style="min-width: 280px;">{{ trans('public.title') }}</th>
                            <th class="text-center">{{ trans('public.type') }}</th>
                            <th class="text-center">{{ trans('public.price') }}</th>
                            <th class="text-center">{{ trans('public.instructor') }}</th>
                            <th class="text-center">{{ trans('update.affiliate_sales') }}</th>
                            <th class="text-center">{{ trans('update.affiliate_earnings') }}</th>
                            <th class="text-left" style="min-width: 300px;">{{ trans('update.affiliate_link') }}</th>
                            <th class="text-right">{{ trans('public.controls') }}</th>
                        </tr>
                        </thead>
                        <tbody id="courseAffiliateTableBody">
                        @foreach($affiliateItems as $affiliateItem)
                            @include('design_1.panel.marketing.affiliates.course_item', [
                                'affiliateItem' => $affiliateItem,
                            ])
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                @include('design_1.panel.includes.no-result',[
                    'file_name' => 'course_list.svg',
                    'title' => trans('update.no_courses_in_affiliate_list'),
                    'hint' => nl2br(trans('update.no_courses_in_affiliate_list_hint')),
                ])
            @endif
        @endif

    </div>


@endsection

@push('scripts_bottom')
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    @if(($activeTab ?? 'referred_users') == 'courses')
    <script>
        var generateAffiliateLinkLang = '{{ trans('update.generate_affiliate_link') }}';
        var generateLinkLang = '{{ trans('update.generate_link') }}';
        var cancelLang = '{{ trans('public.cancel') }}';
        var pleaseSelectACourseLang = '{{ trans('update.please_select_a_course') }}';
        var requestFailedLang = '{{ trans('public.request_failed') }}';
        var copiedLang = '{{ trans('public.copied') }}';
    </script>
    <script src="/assets/design_1/js/panel/affiliate_links.min.js"></script>
    @endif
@endpush

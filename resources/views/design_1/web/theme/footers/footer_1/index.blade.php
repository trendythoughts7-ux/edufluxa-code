@if(!empty($themeFooterData['contents']))
    @php
        $themeFooterContent = $themeFooterData['contents'];
        $themeFooterBackground = null;
        $themeFooterDarkBackground = null;
        $themeFooterBackgroundColor = "secondary";
        $themeFooterHasNewsletter = (!empty($themeFooterContent['newsletter']) and $themeFooterContent['newsletter']['enable'] == "on");

        if (!empty($themeFooterContent['dark_mode_background'])) {
            $themeFooterDarkBackground = $themeFooterContent['dark_mode_background'];
        }
        if (!empty($themeFooterContent['background'])) {
            $themeFooterBackground = $themeFooterContent['background'];
        }
        if (!empty($themeFooterContent['background_color'])) {
            $themeFooterBackgroundColor = $themeFooterContent['background_color'];
        }
    @endphp

    <div class="theme-footer-1 theme-footer-1--premium position-relative {{ $themeFooterHasNewsletter ? 'has-newsletter' : '' }}">
        <div class="theme-footer-1__top-glow"></div>
        <div class="footer-glow-blob footer-glow-blob--1"></div>
        <div class="footer-glow-blob footer-glow-blob--2"></div>

        <div class="theme-footer-1__section position-relative">
            <div class="theme-footer-1__section-bg-wrapper light-only" style="background-color: var({{ "--".$themeFooterBackgroundColor }}); {{ (!empty($themeFooterBackground) ? "background-image: url({$themeFooterBackground}); " : '') }}"></div>
            <div class="theme-footer-1__section-bg-wrapper dark-only" style="background-color: var({{ "--".$themeFooterBackgroundColor }}); {{ (!empty($themeFooterDarkBackground) ? "background-image: url({$themeFooterDarkBackground}); " : '') }}"></div>

            @if($themeFooterHasNewsletter)
                @include('design_1.web.theme.footers.footer_1.newsletter', ['newsletterData' => $themeFooterContent['newsletter']])
            @endif

            <div class="position-relative z-index-2">
                <div class="container">
                    <div class="footer-partner-strip footer-fade-up" style="animation-delay: 0.05s;">
                        <div class="footer-partner-strip__text">
                            <h4>For Schools, Organizations &amp; Agents</h4>
                            <p>Partner with Edufluxa for bulk enrollments, institutional programs, and referral opportunities.</p>
                        </div>
                        <a href="{{ url('/contact') }}" class="footer-partner-strip__btn">Partner With Us</a>
                    </div>
                </div>
            </div>

            <div class="position-relative z-index-2">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-12 col-lg-5 footer-fade-up footer-col-bordered" style="animation-delay: 0.1s;">
                            <div class="footer-brand mb-24">
                                @if(!empty($generalSettings['logo']))
                                    <img src="{{ $generalSettings['logo'] }}" alt="Edufluxa" class="footer-brand__logo">
                                @endif
                                <p class="footer-brand__tagline">Learn Smarter. Rise Faster.</p>
                            </div>

                            @if(!empty($themeFooterContent['cta']))
                                <div class="d-inline-flex-center gap-8 border-2 border-white rounded-32 bg-white-10 text-white px-16 py-12">
                                    @if(!empty($themeFooterContent['cta']['emoji']))
                                        <div class="size-24">
                                            <img src="{{ $themeFooterContent['cta']['emoji'] }}" alt="footer cta btn icon" class="img-fluid" width="24px" height="24px">
                                        </div>
                                    @endif
                                    @if(!empty($themeFooterContent['cta']['pre_title']))
                                        <span class="">{{ $themeFooterContent['cta']['pre_title'] }}</span>
                                    @endif
                                </div>
                                @if(!empty($themeFooterContent['cta']['title']))
                                    <h3 class="mt-16 font-44 text-white mr-0 mr-lg-48">{{ $themeFooterContent['cta']['title'] }}</h3>
                                @endif
                                @if(!empty($themeFooterContent['cta']['button']) and !empty($themeFooterContent['cta']['button']['label']))
                                    <a href="{{ (!empty($themeFooterContent['cta']['button']['url'])) ? $themeFooterContent['cta']['button']['url'] : '' }}" class="btn-flip-effect btn btn-xlg btn-primary gap-8 mt-32 footer-cta-btn" data-text="{{ $themeFooterContent['cta']['button']['label'] }}">
                                        @if(!empty($themeFooterContent['cta']['button']['icon']))
                                            @svg("iconsax-{$themeFooterContent['cta']['button']['icon']}", ['width' => '24px', 'height' => '24px', 'class' => "icons"])
                                        @endif
                                        <span class="btn-flip-effect__text">{{ $themeFooterContent['cta']['button']['label'] }}</span>
                                    </a>
                                @endif
                            @endif
                        </div>

                        <div class="col-6 col-lg-2 mt-32 mt-lg-0 footer-fade-up footer-col-bordered" style="animation-delay: 0.15s;">
                            @if(!empty($themeFooterContent['links_1_section_title']))
                                <h4 class="font-16 text-white footer-col-title">{{ $themeFooterContent['links_1_section_title'] }}</h4>
                            @endif
                            @if(!empty($themeFooterContent['specific_links']) and is_array($themeFooterContent['specific_links']))
                                @foreach($themeFooterContent['specific_links'] as $specificLink1Data)
                                    @if(!empty($specificLink1Data['title']) and !empty($specificLink1Data['url']))
                                        <a href="{{ $specificLink1Data['url'] }}" target="_blank" class="d-block font-16 text-white opacity-70 footer-link {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                                            <span class="footer-link__text">{{ $specificLink1Data['title'] }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="col-6 col-lg-2 mt-32 mt-lg-0 footer-fade-up footer-col-bordered" style="animation-delay: 0.25s;">
                            @if(!empty($themeFooterContent['links_2_section_title']))
                                <h4 class="font-16 text-white footer-col-title">{{ $themeFooterContent['links_2_section_title'] }}</h4>
                            @endif
                            @if(!empty($themeFooterContent['specific_links_2']) and is_array($themeFooterContent['specific_links_2']))
                                @foreach($themeFooterContent['specific_links_2'] as $specificLink2Data)
                                    @if(!empty($specificLink2Data['title']) and !empty($specificLink2Data['url']))
                                        <a href="{{ $specificLink2Data['url'] }}" target="_blank" class="d-block font-16 text-white opacity-70 footer-link {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                                            <span class="footer-link__text">{{ $specificLink2Data['title'] }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="col-12 col-lg-3 mt-32 mt-lg-0 footer-fade-up" style="animation-delay: 0.35s;">
                            @if(!empty($themeFooterContent['contact']))
                                @if(!empty($themeFooterContent['contact']['section_title']))
                                    <h4 class="font-16 text-white footer-col-title">{{ $themeFooterContent['contact']['section_title'] }}</h4>
                                @endif
                                <div class="d-flex align-items-start gap-8 mt-20 footer-contact-item">
                                    <div class="size-24 footer-contact-icon"><x-iconsax-lin-location class="text-white" width="24px" height="24px"/></div>
                                    <span class="font-16 text-white opacity-70">Bangkok</span>
                                </div>
                                <div class="d-flex align-items-start gap-8 mt-16 footer-contact-item">
                                    <div class="size-24 footer-contact-icon"><x-iconsax-lin-call-calling class="text-white" width="24px" height="24px"/></div>
                                    <span class="font-16 text-white opacity-70">+66815245258</span>
                                </div>
                                <div class="d-flex align-items-start gap-8 mt-16 footer-contact-item">
                                    <div class="size-24 footer-contact-icon"><x-iconsax-lin-sms class="text-white" width="24px" height="24px"/></div>
                                    <span class="font-16 text-white opacity-70">info@edufluxa.com</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="theme-footer-1__bottom-section-divider theme-footer-1__bottom-section-divider--premium"></div>

                <div class="container">
                    <div class="footer-bottom-row">
                        <p class="footer-copyright">© 2026 Edufluxa. All Rights Reserved. Empowering Learning Worldwide.</p>

                        <div class="footer-social-block">
                            <span class="footer-social-label">FOLLOW US</span>
                            <div class="footer-social-row">
                                @php($footerSocials = collect(\App\Models\Setting::getSocials())->keyBy('title'))

                                @if($footerSocials->has('Whatsapp'))
                                <a href="{{ $footerSocials['Whatsapp']['link'] }}" target="_blank" rel="nofollow" title="WhatsApp" class="footer-social-icon" style="background:#25D366;">
                                    <svg viewBox="0 0 448 512" width="17" height="17" fill="#fff"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.7z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('LINE'))
                                <a href="{{ $footerSocials['LINE']['link'] }}" target="_blank" rel="nofollow" title="LINE" class="footer-social-icon" style="background:#06C755;">
                                    <svg viewBox="0 0 448 512" width="16" height="16" fill="#fff"><path d="M448 113.7V422c-.1 44.8-36.8 81.1-81.7 81H81.6C36.8 503-.1 466.3 0 421.5V113.7C.1 68.9 36.8 32.6 81.7 32.7h284.6c44.9-.1 81.8 36.2 81.7 81zM272.1 204.2c0-1.8-1.4-3.2-3.2-3.2h-11.4c-1 0-2.1.5-2.6 1.4l-32.6 44v-42.2c0-1.8-1.4-3.2-3.2-3.2H208c-1.8 0-3.2 1.4-3.2 3.2v71.1c0 1.8 1.4 3.2 3.2 3.2h11.4c1.1 0 2.1-.6 2.6-1.3l32.6-44v42.2c0 1.8 1.4 3.2 3.2 3.2h11.4c1.8 0 3.2-1.4 3.2-3.2v-71.2zm-93.7-3.2h-11.4c-1.8 0-3.2 1.4-3.2 3.2v71.1c0 1.8 1.4 3.2 3.2 3.2h11.4c1.8 0 3.2-1.4 3.2-3.2v-71.1c0-1.8-1.4-3.2-3.2-3.2zm-27.7 60.6h-31.1v-57.4c0-1.8-1.4-3.2-3.2-3.2h-11.4c-1.8 0-3.2 1.4-3.2 3.2v71.1c0 .9.3 1.6.9 2.2.6.5 1.3.9 2.2.9h45.8c1.8 0 3.2-1.4 3.2-3.2v-11.4c0-1.7-1.4-3.2-3.2-3.2zM332.1 201h-45.8c-1.7 0-3.2 1.4-3.2 3.2v71.1c0 1.7 1.4 3.2 3.2 3.2h45.8c1.8 0 3.2-1.4 3.2-3.2v-11.4c0-1.8-1.4-3.2-3.2-3.2H301v-12h31.1c1.8 0 3.2-1.4 3.2-3.2v-11.5c0-1.8-1.4-3.2-3.2-3.2H301v-12h31.1c1.8 0 3.2-1.4 3.2-3.2v-11.4c0-1.7-1.4-3.1-3.2-3.1z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('Facebook'))
                                <a href="{{ $footerSocials['Facebook']['link'] }}" target="_blank" rel="nofollow" title="Facebook" class="footer-social-icon" style="background:#1877F2;">
                                    <svg viewBox="0 0 320 512" width="15" height="15" fill="#fff"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('YouTube'))
                                <a href="{{ $footerSocials['YouTube']['link'] }}" target="_blank" rel="nofollow" title="YouTube" class="footer-social-icon" style="background:#FF0000;">
                                    <svg viewBox="0 0 576 512" width="18" height="18" fill="#fff"><path d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.1 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('Instagram'))
                                <a href="{{ $footerSocials['Instagram']['link'] }}" target="_blank" rel="nofollow" title="Instagram" class="footer-social-icon footer-social-icon--gradient">
                                    <svg viewBox="0 0 448 512" width="16" height="16" fill="#fff"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('WeChat'))
                                <a href="{{ $footerSocials['WeChat']['link'] }}" target="_blank" rel="nofollow" title="WeChat" class="footer-social-icon" style="background:#07C160;">
                                    <svg viewBox="0 0 576 512" width="17" height="17" fill="#fff"><path d="M385.2 167.6c6.4 0 12.6.3 18.8 1.1C387.4 90.3 303.3 32 205.2 32 96 32 7 104.8 7 197.4c0 53.4 29.3 97.5 76.3 131.6l-19 57.2 66.4-33.3c23.9 4.7 42.9 9.5 66.4 9.5 6 0 11.9-.3 17.8-.8-3.8-12.9-5.9-26.4-5.9-40.5-.1-84.9 72.9-153.5 176.2-153.5zm-104.5-52.7c14.3 0 23.9 9.6 23.9 23.9 0 14.3-9.6 23.9-23.9 23.9-14.3 0-28.6-9.6-28.6-23.9 0-14.3 14.3-23.9 28.6-23.9zm-137.9 47.8c-14.3 0-28.7-9.6-28.7-23.9 0-14.3 14.4-23.9 28.7-23.9 14.3 0 23.9 9.6 23.9 23.9 0 14.3-9.6 23.9-23.9 23.9zM563.8 341.3c0-77.3-77.3-140.4-163.5-140.4-91.2 0-163.6 63.1-163.6 140.4S309.1 481.7 400.3 481.7c19 0 38.1-4.8 57.2-9.6l52.4 28.7-14.3-47.8c38.1-28.7 68.2-66.3 68.2-111.7zm-215.9-23.9c-9.6 0-19.1-9.6-19.1-19.1 0-9.6 9.5-19.1 19.1-19.1 14.3 0 23.9 9.5 23.9 19.1 0 9.5-9.6 19.1-23.9 19.1zm104.5 0c-9.5 0-19-9.6-19-19.1 0-9.6 9.5-19.1 19-19.1 14.3 0 23.9 9.5 23.9 19.1 0 9.5-9.6 19.1-23.9 19.1z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('Telegram'))
                                <a href="{{ $footerSocials['Telegram']['link'] }}" target="_blank" rel="nofollow" title="Telegram" class="footer-social-icon" style="background:#26A5E4;">
                                    <svg viewBox="0 0 448 512" width="16" height="16" fill="#fff"><path d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.6 10.1l7.4-104.9L365.7 121.9c8.3-7.4-1.8-11.5-12.8-4.1L100.7 275.4 5.3 245.5c-21-6.5-21.4-21 4.4-31l318.7-122.9c17.5-6.4 32.8 4.1 27.9 27z"/></svg>
                                </a>
                                @endif

                                @if($footerSocials->has('Discord'))
                                <a href="{{ $footerSocials['Discord']['link'] }}" target="_blank" rel="nofollow" title="Discord" class="footer-social-icon" style="background:#5865F2;">
                                    <svg viewBox="0 0 640 512" width="18" height="18" fill="#fff"><path d="M524.5 69.8a1.5 1.5 0 0 0-.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0-1.9.9 337.5 337.5 0 0 0-14.9 30.6 447.8 447.8 0 0 0-134.4 0A309.5 309.5 0 0 0 237.7 32.9a1.9 1.9 0 0 0-1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0-.8.7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4 487.7 487.7 0 0 0 146.8 74.2 1.9 1.9 0 0 0 2.1-.7 348.2 348.2 0 0 0 30-48.8 1.9 1.9 0 0 0-1-2.6 321.2 321.2 0 0 1-45.9-21.9 1.9 1.9 0 0 1-.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9.2c3 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1-.2 3.1 301.4 301.4 0 0 1-45.9 21.8 1.9 1.9 0 0 0-1 2.6 391 391 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1.7 486 486 0 0 0 147-74.2 1.9 1.9 0 0 0 .8-1.4c12.2-126.8-20.6-236.9-87.1-334.6zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2s23.7-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.6-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.7-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.6-23.2 59.2-52.8 59.2z"/></svg>
                                </a>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-trust-bar">
                    <div class="container">
                        <div class="footer-trust-bar__row">
                            <div class="footer-trust-item">
                                <x-iconsax-lin-shield-tick class="footer-trust-item__icon" width="20px" height="20px"/>
                                <span>Secure &amp; Encrypted Payments</span>
                            </div>
                            <div class="footer-trust-item">
                                <x-iconsax-lin-medal-star class="footer-trust-item__icon" width="20px" height="20px"/>
                                <span>Verified Instructors</span>
                            </div>
                            <div class="footer-trust-item">
                                <x-iconsax-lin-award class="footer-trust-item__icon" width="20px" height="20px"/>
                                <span>Completion Certificates</span>
                            </div>
                            <div class="footer-trust-item">
                                <x-iconsax-lin-global class="footer-trust-item__icon" width="20px" height="20px"/>
                                <span>Learners Worldwide</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .theme-footer-1--premium { position: relative; overflow: hidden; }
        .theme-footer-1__top-glow {
            position: absolute; top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, #0056b3, #00aeef, #6f42c1, #0056b3);
            background-size: 300% 100%; animation: footerGlowMove 6s linear infinite; z-index: 5;
        }
        @keyframes footerGlowMove { 0% { background-position: 0% 0; } 100% { background-position: 300% 0; } }

        .footer-glow-blob {
            position: absolute; border-radius: 50%; filter: blur(90px); z-index: 0; pointer-events: none;
        }
        .footer-glow-blob--1 { width: 380px; height: 380px; background: rgba(0,86,179,0.25); top: -100px; left: -100px; }
        .footer-glow-blob--2 { width: 320px; height: 320px; background: rgba(111,66,193,0.2); bottom: -80px; right: -80px; }

        .footer-brand__logo { max-height: 40px; width: auto; display: block; margin-bottom: 10px; }
        .footer-brand__tagline { color: rgba(255,255,255,0.65); font-size: 14px; margin-bottom: 24px; }

        .footer-col-title { position: relative; padding-bottom: 10px; margin-bottom: 6px; }
        .footer-col-title::after { content:''; position:absolute; left:0; bottom:0; width:28px; height:2px; background:#00aeef; border-radius:2px; }

        .footer-col-bordered { position: relative; }
        @media (min-width: 992px) {
            .footer-col-bordered::after {
                content: '';
                position: absolute;
                top: 4px;
                right: 0;
                bottom: 4px;
                width: 1px;
                background: linear-gradient(180deg, transparent, rgba(255,255,255,0.15), transparent);
            }
        }

        .footer-link { position: relative; transition: color 0.3s ease, transform 0.3s ease; }
        .footer-link__text { position: relative; }
        .footer-link__text::after { content:''; position:absolute; left:0; bottom:-3px; width:0%; height:1px; background:#00aeef; transition:width 0.3s ease; }
        .footer-link:hover { color: #00aeef !important; transform: translateX(4px); }
        .footer-link:hover .footer-link__text::after { width: 100%; }

        .footer-contact-item { transition: transform 0.3s ease; }
        .footer-contact-item:hover { transform: translateX(4px); }
        .footer-contact-icon { transition: transform 0.4s ease; }
        .footer-contact-item:hover .footer-contact-icon { transform: scale(1.15) rotate(-5deg); }

        .footer-bottom-row {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 20px; padding: 24px 0;
        }
        .footer-copyright { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }

        .footer-social-block { display: flex; align-items: center; gap: 14px; }
        .footer-social-label {
            color: rgba(255,255,255,0.4); font-size: 11px; font-weight: 700;
            letter-spacing: 1.5px;
        }
        .footer-social-row { display: flex; align-items: center; gap: 12px; }

        .footer-social-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, filter 0.3s ease;
            text-decoration: none !important;
        }
        .footer-social-icon--gradient {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        }
        .footer-social-icon:hover {
            transform: translateY(-4px) scale(1.15);
            box-shadow: 0 6px 16px rgba(0,0,0,0.35);
            filter: brightness(1.1);
        }

        .footer-cta-btn { position: relative; overflow: hidden; }
        .footer-cta-btn::before {
            content:''; position:absolute; top:0; left:-75%; width:50%; height:100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
            transform: skewX(-20deg); transition: left 0.6s ease;
        }
        .footer-cta-btn:hover::before { left: 130%; }

        .theme-footer-1__bottom-section-divider--premium { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent) !important; height: 1px; }

        .footer-fade-up { opacity: 0; transform: translateY(18px); animation: footerFadeUp 0.7s ease forwards; }
        @keyframes footerFadeUp { to { opacity: 1; transform: translateY(0); } }

        .footer-partner-strip {
            display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;
            background: linear-gradient(120deg, rgba(0,86,179,0.25), rgba(111,66,193,0.25));
            border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 28px 32px;
            margin-top: 40px; margin-bottom: 40px; transition: transform 0.3s ease, border-color 0.3s ease;
            position: relative; z-index: 1;
        }
        .footer-partner-strip:hover { transform: translateY(-3px); border-color: rgba(0, 174, 239, 0.5); }
        .footer-partner-strip__text h4 { color: #fff; font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .footer-partner-strip__text p { color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 0; max-width: 480px; }
        .footer-partner-strip__btn {
            background: #00aeef; color: #fff !important; font-weight: 600; font-size: 15px;
            padding: 12px 28px; border-radius: 10px; white-space: nowrap; text-decoration: none !important;
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .footer-partner-strip__btn:hover { background: #0090c7; transform: scale(1.05); }

        .footer-trust-bar { border-top: 1px solid rgba(255,255,255,0.1); padding: 18px 0 28px; position: relative; z-index: 1; }
        .footer-trust-bar__row { display: flex; flex-wrap: wrap; justify-content: center; gap: 28px; }
        .footer-trust-item { display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.6); font-size: 13px; transition: color 0.3s ease, transform 0.3s ease; }
        .footer-trust-item:hover { color: #00aeef; transform: translateY(-2px); }

        @media (max-width: 991px) {
            .footer-partner-strip { flex-direction: column; text-align: center; }
            .footer-partner-strip__text p { max-width: 100%; }
            .footer-bottom-row { flex-direction: column; text-align: center; }
        }
        @media (prefers-reduced-motion: reduce) {
            .footer-fade-up, .theme-footer-1__top-glow, .footer-cta-btn::before { animation: none !important; opacity: 1 !important; transform: none !important; }
        }
    </style>
@endif

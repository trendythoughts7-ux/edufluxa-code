@if(getFeaturesSettings('enable_public_chat_questions'))
    @php
        $ctaInstructor = null;
        $ctaSourceType = null;
        $ctaSlug = null;

        if (!empty($course)) {
            $ctaInstructor = \App\User::find($course->teacher_id ?? $course->creator_id);
            $ctaSourceType = ($course instanceof \App\Models\Event) ? 'event' : 'course';
            $ctaSlug = $course->slug;
        } elseif (!empty($bundle)) {
            $ctaInstructor = \App\User::find($bundle->teacher_id ?? $bundle->creator_id);
            $ctaSourceType = 'bundle';
            $ctaSlug = $bundle->slug;
        }

        $ctaDisplayMethod = getFeaturesSettings('public_chat_cta_display_method') ?: 'cta_card';
        $ctaFloatStyle    = getFeaturesSettings('public_chat_float_cta_style') ?: 'style_1';

        $showCard  = in_array($ctaDisplayMethod, ['cta_card', 'both']);
        $showFloat = in_array($ctaDisplayMethod, ['float_cta', 'both']);

        $chatUrl = '/panel/public-chat/start/' . ($ctaSlug ?? '') . '/' . ($ctaSourceType ?? '');

        $showOnlineIndicator = getFeaturesSettings('show_instructor_online_course_page');
        $isInstructorOnlineSystem = false;
        if ($showOnlineIndicator && !empty($ctaInstructor)) {
            $isInstructorOnlineSystem = \App\Models\UserLoginHistory::where('user_id', $ctaInstructor->id)
                                        ->whereNull('session_end_at')
                                        ->where('session_start_at', '>', time() - 21600)
                                        ->exists();
        }
    @endphp

    @if(!empty($ctaInstructor) && $ctaInstructor->enable_public_chat)

        {{-- ============================================================
             CTA CARD (Sidebar static card)
        ============================================================ --}}
        @if($showCard)
            <a href="{{ $chatUrl }}" class="public-chat-cta-card card-with-mask position-relative mt-28 d-block">
                <div class="mask-8-white border-gray-200"></div>

                <div class="public-chat-cta-card-box position-relative z-index-2 d-flex align-items-center p-16 rounded-16">
                    {{-- Icon Circles --}}
                    <div class="public-chat-cta-card-icon-1 d-flex-center size-56 rounded-circle flex-shrink-0">
                        <div class="public-chat-cta-card-icon-2 d-flex-center size-40 rounded-circle position-relative z-index-2">
                            <x-iconsax-bul-messages-3 class="icon" width="24px" height="24px"/>
                            {{-- Online dot — based on user session --}}
                            <span class="public-chat-online-dot" id="publicChatOnlineDotCard" style="display:{{ $isInstructorOnlineSystem ? 'block' : 'none' }}; z-index: 3;"></span>
                        </div>
                    </div>

                    {{-- Text --}}
                    <div class="ml-8 flex-1 min-width-0">
                        <h3 class="public-chat-cta-card-title font-14 mb-0">{{ trans('update.chat_with_instructor') }}</h3>
                        <div class="public-chat-cta-card-subtitle mt-4 font-12">{{ trans('update.chat_with_instructor_desc') }}</div>
                    </div>

                    {{-- Arrow --}}
                    <div class="public-chat-cta-card-arrow flex-shrink-0 ml-16">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>
                </div>
            </a>
        @endif

        {{-- ============================================================
             FLOAT CTA (fixed on page) — pushed to body_end stack
        ============================================================ --}}
        @if($showFloat)
            @push('body_end')
                @if($ctaFloatStyle === 'style_1')
                    {{-- Float Style 1: Same multi-layer structure as CTA Card --}}
                    <div id="publicChatFloatCta" class="public-chat-float" style="width: 320px;">
                        <a href="{{ $chatUrl }}" class="public-chat-cta-card public-chat-cta-card--no-hover card-with-mask d-block" style="box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); border-radius: 16px;">
                            <div class="mask-8-white border-gray-200"></div>

                            <div class="public-chat-cta-card-box position-relative z-index-2 d-flex align-items-center p-16 rounded-16">
                                {{-- Multi-layer Avatar --}}
                                <div class="public-chat-cta-card-icon-1 d-flex-center size-56 rounded-circle flex-shrink-0">
                                    <div class="public-chat-cta-card-icon-2 d-flex-center size-40 rounded-circle position-relative z-index-2">
                                        <img src="{{ $ctaInstructor->getAvatar(40) }}"
                                             alt="{{ $ctaInstructor->full_name }}"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                        <span class="public-chat-online-dot public-chat-online-dot--float" id="publicChatOnlineDotFloat" style="display:{{ $isInstructorOnlineSystem ? 'block' : 'none' }}; z-index: 3;"></span>
                                    </div>
                                </div>

                                <div class="ml-8 flex-1 min-width-0">
                                    <h3 class="public-chat-cta-card-title font-14 mb-0">{{ trans('update.chat_with_instructor') }}</h3>
                                    <div class="public-chat-cta-card-subtitle mt-4 font-12">{{ trans('update.chat_with_instructor_desc') }}</div>
                                </div>

                                <div class="public-chat-cta-card-arrow ml-16 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                @else
                    {{-- Float Style 2: Circle avatar + Chat Now button --}}
                    <div id="publicChatFloatCta" class="public-chat-float public-chat-float--style2">
                        <a href="{{ $chatUrl }}" class="public-chat-float__circle">
                            <img src="{{ $ctaInstructor->getAvatar(56) }}"
                                 alt="{{ $ctaInstructor->full_name }}"
                                 style="width: 56px; height: 56px; object-fit: cover; border-radius: 50%; display: block;">
                            <span class="public-chat-online-dot public-chat-online-dot--lg" id="publicChatOnlineDotFloat" style="display:{{ $isInstructorOnlineSystem ? 'block' : 'none' }};"></span>
                        </a>
                        <a href="{{ $chatUrl }}" class="public-chat-float__btn btn btn-primary" style="width: 76px; height: 23px; min-height:23px;">
                            {{ trans('update.chat_now') }}
                        </a>
                    </div>
                @endif


                {{-- Handle overlap with course/event bottom fixed card --}}
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var bottomCard = document.querySelector('.course-bottom-fixed-card, .event-bottom-fixed-card');
                        var cartDrawer = document.querySelector('.cart-drawer');
                        var floatCta = document.getElementById('publicChatFloatCta');
                        
                        if (floatCta) {
                            function adjustFloatCta() {
                                // Hide if cart drawer is open
                                if (cartDrawer && cartDrawer.classList.contains('show')) {
                                    floatCta.style.display = 'none';
                                    return;
                                } else {
                                    floatCta.style.display = '';
                                }

                                // Adjust position for bottom fixed cards
                                if (bottomCard && bottomCard.classList.contains('show')) {
                                    // Move up by the height of the bottom card + some margin
                                    floatCta.style.bottom = (bottomCard.offsetHeight + 24) + 'px';
                                } else {
                                    floatCta.style.bottom = ''; // revert to CSS default
                                }
                            }
                            
                            var observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.attributeName === 'class') {
                                        adjustFloatCta();
                                    }
                                });
                            });
                            
                            if (bottomCard) {
                                observer.observe(bottomCard, { attributes: true });
                            }
                            if (cartDrawer) {
                                observer.observe(cartDrawer, { attributes: true });
                            }

                            adjustFloatCta(); // Initial check
                        }
                    });
                </script>
            @endpush
        @endif

    @endif
@endif
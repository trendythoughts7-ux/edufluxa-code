@php
    $themeSpecificLinks = (new \App\Mixins\Themes\ThemeHeaderMixins())->getHeader1NavbarSpecificLinks($themeHeaderData['contents']);
    $themeSpecificButton = (new \App\Mixins\Themes\ThemeHeaderMixins())->getHeader1NavbarSpecificButton($themeHeaderData['contents']);
@endphp

<header class="theme-header-1__main d-none d-lg-block">
    <div class="container h-100 position-relative">
        <div class="theme-header-1__main-mask"></div>

        <div class="position-relative z-index-2 bg-white rounded-24 w-100 h-100 p-16">
            <div class="row align-items-center h-100">

                {{-- 1. Logo --}}
                <div class="col-lg-2">
                    <a href="/" class="theme-header-1__logo text-left d-block">
                        @if(!empty($generalSettings['logo']))
                            <img src="{{ $generalSettings['logo'] }}" class="img-fluid" style="max-height: 35px;" alt="logo">
                        @endif
                    </a>
                </div>

                {{-- 2. Category --}}
                <div class="col-lg-2 d-flex align-items-center justify-content-center">
                    @include('design_1.web.theme.headers.header_1.includes.categories')
                </div>

                {{-- 3. Navigation --}}
                <div class="col-lg-8 d-none d-lg-flex align-items-center justify-content-between" style="position: static !important;">

                    <a href="{{ url('/') }}" class="nav-item-custom">Home</a>

                    {{-- Courses Mega Menu --}}
                    <div class="custom-mega-dropdown">
                        <a href="javascript:void(0)" class="nav-item-custom font-weight-bold">
                            Courses <i class="fa fa-chevron-down ml-1"></i>
                        </a>
                        <div class="mega-content-box shadow-lg courses-width">
                            <div class="row w-100 m-0">

                                <div class="col-md-4 border-right py-2">
                                    <h6 class="mega-title"><i class="fa fa-th-large mr-2"></i> By Subject</h6>
                                    <ul class="mega-list">
                                        <li style="list-style: none;"><a href="{{ url('/categories/academic-tutoring') }}" class="mega-link">Academic Tutoring</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/language-academy') }}" class="mega-link">Language Academy</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/homeschooling-programs') }}" class="mega-link">Homeschooling</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/skills-development') }}" class="mega-link">Skills Development</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/professional-adult-learning') }}" class="mega-link">Professional & Adult</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/kids-learning') }}" class="mega-link">Kids Learning</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-4 border-right py-2">
                                    <h6 class="mega-title"><i class="fa fa-graduation-cap mr-2"></i> By Age Group</h6>
                                    <ul class="mega-list">
                                        <li style="list-style: none;"><a href="{{ url('/categories/kids-foundation') }}" class="mega-link">Kids (Ages 3-8)</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/elementary-tutoring') }}" class="mega-link">Elementary (Ages 9-12)</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/middle-school-tutoring') }}" class="mega-link">Middle School (13-15)</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/high-school-tutoring') }}" class="mega-link">High School (16-18)</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/university-adult-tutoring') }}" class="mega-link">University & Adult</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-4 py-2">
                                    <div class="promo-box promo-blue">
                                        <i class="fa fa-laptop-code d-block mb-3" style="font-size: 35px; color: #0056b3;"></i>
                                        <h6>Learn at Your Pace</h6>
                                        <p>Expert-designed courses with lifetime access.</p>
                                        <a href="{{ url('/classes') }}" class="btn btn-primary btn-sm btn-block">Explore Courses</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Tutoring Mega Menu --}}
                    <div class="custom-mega-dropdown">
                        <a href="javascript:void(0)" class="nav-item-custom">
                            Tutoring <i class="fa fa-chevron-down ml-1"></i>
                        </a>
                        <div class="mega-content-box shadow-lg tutoring-width">
                            <div class="row w-100 m-0">

                                <div class="col-md-5 border-right py-2">
                                    <h6 class="mega-title">Session Type</h6>
                                    <ul class="mega-list">
                                        <li><a href="{{ url('/instructors') }}" class="mega-link">1-on-1 Tutoring</a></li>
                                        <li><a href="{{ url('/classes/group-classes') }}" class="mega-link">Group Classes</a></li>
                                        <li><a href="{{ url('/classes/homework-help') }}" class="mega-link">Homework Help</a></li>
                                        <li><a href="{{ url('/instructors') }}" class="mega-link">Trial Lesson (Free)</a></li>
                                        <li><a href="{{ url('/classes') }}" class="mega-link">Corporate Training</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-4 border-right py-2">
                                    <h6 class="mega-title">Subjects</h6>
                                    <ul class="mega-list">
                                        <li><a href="{{ url('/categories/academic-tutoring') }}" class="mega-link">Mathematics</a></li>
                                        <li><a href="{{ url('/categories/english-language') }}" class="mega-link">English Language</a></li>
                                        <li><a href="{{ url('/categories/academic-tutoring') }}" class="mega-link">Science</a></li>
                                        <li><a href="{{ url('/categories/language-academy') }}" class="mega-link">Languages</a></li>
                                        <li><a href="{{ url('/categories/test-preparation') }}" class="mega-link">Exam Coaching</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-3 py-2">
                                    <div class="promo-box promo-green">
                                        <i class="fa fa-chalkboard-teacher d-block mb-3" style="font-size: 35px; color: #28a745;"></i>
                                        <h6>Find the Right Tutor</h6>
                                        <p>Expert tutors for every subject and level.</p>
                                        <a href="{{ url('/instructors') }}" class="btn btn-success btn-sm btn-block">Find a Tutor</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Test Prep Mega Menu --}}
                    <div class="custom-mega-dropdown">
                        <a href="javascript:void(0)" class="nav-item-custom">
                            Test Prep <i class="fa fa-chevron-down ml-1"></i>
                        </a>
                        <div class="mega-content-box shadow-lg testprep-width">
                            <div class="row w-100 m-0">

                                <div class="col-md-7 border-right py-2">
                                    <h6 class="mega-title"><i class="fa fa-pencil-alt mr-2"></i> Exam Preparation</h6>
                                    <ul class="mega-list">
                                        <li style="list-style: none;"><a href="{{ url('/categories/ielts-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> IELTS Preparation</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/test-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> TOEFL Preparation</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/test-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> GED Exam Prep</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/test-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> SAT & ACT Prep</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/test-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> Cambridge (A1-C2)</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/test-preparation') }}" class="mega-link"><i class="fa fa-check-circle mr-2 text-primary"></i> PTE Academic</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-5 py-2">
                                    <div class="promo-box promo-orange">
                                        <i class="fa fa-award d-block mb-3" style="font-size: 35px; color: #e67e22;"></i>
                                        <h6>Achieve Your Target Score</h6>
                                        <p>Expert-led test prep with proven strategies.</p>
                                        <a href="{{ url('/categories/test-preparation') }}" class="btn btn-warning btn-sm btn-block text-white">Start Test Prep</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Resources Mega Menu --}}
                    <div class="custom-mega-dropdown">
                        <a href="javascript:void(0)" class="nav-item-custom">
                            Resources <i class="fa fa-chevron-down ml-1"></i>
                        </a>
                        <div class="mega-content-box shadow-lg resources-width">
                            <div class="row w-100 m-0">

                                <div class="col-md-4 border-right py-2">
                                    <h6 class="mega-title"><i class="fa fa-gift mr-2 text-success"></i> Free Materials</h6>
                                    <ul class="mega-list">
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Worksheets</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Practice Quizzes</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Flashcards</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Past Papers</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Study Guides</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-4 border-right py-2">
                                    <h6 class="mega-title"><i class="fa fa-star mr-2 text-warning"></i> Premium Materials</h6>
                                    <ul class="mega-list">
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">PDF Lesson Packs</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/classes') }}" class="mega-link">Text Courses</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/classes') }}" class="mega-link">Video Courses</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Workbooks</a></li>
                                        <li style="list-style: none;"><a href="{{ url('/categories/learning-materials') }}" class="mega-link">Expert Materials</a></li>
                                    </ul>
                                </div>

                                <div class="col-md-4 py-2">
                                    <div class="promo-box promo-purple">
                                        <i class="fa fa-book-open d-block mb-3" style="font-size: 35px; color: #6f42c1;"></i>
                                        <h6>Browse All Resources</h6>
                                        <p>Free & premium learning materials for every level.</p>
                                        <a href="{{ url('/categories/learning-materials') }}" class="btn btn-purple btn-sm btn-block">Browse Resources</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Blog --}}
                    <a href="{{ url('/blog') }}" class="nav-item-custom">Blog</a>

                    {{-- Find a Tutor CTA Button --}}
                    <a href="{{ url('/instructors') }}" class="btn-find-tutor">
                        <i class="fa fa-search mr-1"></i> Find a Tutor
                    </a>

                </div>

            </div>
        </div>
    </div>

<style>
    .nav-item-custom {
        color: #000 !important;
        text-decoration: none !important;
        font-weight: 500;
        font-size: 15px;
        padding: 25px 0;
        display: inline-block;
    }
    .nav-item-custom:hover { color: #0056b3 !important; }

    .btn-find-tutor {
        background: #0056b3;
        color: #fff !important;
        text-decoration: none !important;
        font-weight: 600;
        font-size: 14px;
        padding: 9px 18px;
        border-radius: 8px;
        display: inline-block;
        transition: 0.3s;
        white-space: nowrap;
    }
    .btn-find-tutor:hover {
        background: #003d80;
        color: #fff !important;
        text-decoration: none !important;
    }

    .custom-mega-dropdown { position: static !important; }

    .mega-content-box {
        display: none;
        position: absolute;
        top: 75px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        border-radius: 0 0 15px 15px;
        padding: 25px;
        z-index: 999999;
        border-top: 3px solid #0056b3;
    }

    .courses-width   { width: 900px; }
    .tutoring-width  { width: 800px; border-top-color: #28a745; }
    .testprep-width  { width: 650px; border-top-color: #e67e22; }
    .resources-width { width: 850px; border-top-color: #6f42c1; }

    .custom-mega-dropdown:hover .mega-content-box { display: block !important; }

    .mega-title {
        font-size: 15px;
        font-weight: 700;
        color: #002d5b;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }

    .mega-list { list-style: none; padding: 0; margin: 0; }
    .mega-list li { padding: 7px 0; font-size: 14px; color: #555; transition: 0.3s; cursor: pointer; }
    .mega-list li:hover { color: #0056b3; padding-left: 5px; }

    .promo-box { padding: 20px; border-radius: 12px; text-align: center; height: 100%; }
    .promo-blue   { background: #f0f7ff; }
    .promo-green  { background: #f0fff4; }
    .promo-orange { background: #fff8f0; }
    .promo-purple { background: #f8f0ff; }
    .promo-box h6 { font-weight: 700; margin-bottom: 8px; font-size: 14px; }
    .promo-box p  { font-size: 12px; color: #666; margin-bottom: 12px; }

    .btn-purple {
        background: #6f42c1 !important;
        color: #fff !important;
        border: none;
    }
    .btn-purple:hover { background: #5a32a3 !important; }

    .theme-header-1__main {
        height: 80px !important;
        position: relative;
    }

    .header-sticky.sticky {
        position: fixed !important;
        top: 0;
        width: 100%;
        animation: none !important;
    }

    .mega-link {
        color: #333 !important;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        transition: 0.3s;
    }
    .mega-list li:hover .mega-link,
    .mega-list li a.mega-link:hover {
        color: #0056b3 !important;
        text-decoration: none !important;
    }
</style>

</header>

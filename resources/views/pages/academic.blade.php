@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    Online Learning
                </span>

                <h1 class="font-36 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Explore Online Courses Designed for Modern Learners
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height: 1.7;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Discover flexible online courses across academic subjects,
                    languages, technology, personal development,
                    and exam preparation.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height: 2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    Our online courses are carefully designed to provide practical learning experiences,
                    engaging lessons, and long-term academic growth. Whether students are looking
                    to improve school performance, develop language skills, or learn modern digital skills,
                    Edufluxa offers structured programmes suitable for every learning stage.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <img
                    src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3"
                    class="img-fluid rounded-lg shadow-lg"
                    alt="Online Learning"
                >
            </div>

        </div>
    </div>
</section>

<section class="py-80 bg-white">
    <div class="container">

        <div class="text-center mb-60" data-aos="fade-up" data-aos-duration="1000">
            <span class="text-primary font-weight-bold">
                BY CATEGORY
            </span>

            <h3 class="font-32 font-weight-bold mt-3 mb-4">
                Academic Subjects
            </h3>

            <p class="text-muted mx-auto" style="max-width: 850px; line-height: 2;">
                Strengthen core academic skills through engaging and structured online lessons
                designed to support school success, independent learning,
                and examination performance.
            </p>
        </div>

        <div class="row">

            @php
                $subjects = [
                    'Mathematics',
                    'English',
                    'Science',
                    'Physics',
                    'Chemistry',
                    'Biology',
                    'Business Studies'
                ];
            @endphp

            @foreach($subjects as $index => $subject)

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 120 }}">
                    <div class="p-4 bg-light rounded-lg shadow-sm h-100 border-0">

                        <div class="mb-3">
                            <div style="
                                width:60px;
                                height:60px;
                                background:#4361ee;
                                border-radius:16px;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:white;
                                font-size:24px;
                                font-weight:bold;
                            " data-aos="zoom-in" data-aos-duration="600" data-aos-delay="{{ ($index * 120) + 150 }}">
                                {{ substr($subject,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $subject }}
                        </h4>

                        <p class="text-muted mb-0" style="line-height:1.8;">
                            Interactive online lessons and structured learning pathways
                            designed to help students build confidence and academic excellence.
                        </p>

                    </div>
                </div>

            @endforeach

        </div>
    </div>
</section>

<section class="py-80 bg-primary text-white text-center" data-aos="zoom-in-up" data-aos-duration="1000">
    <div class="container">

        <h3 class="font-36 font-weight-bold mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
            Start Your Learning Journey Today
        </h3>

        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Join modern learners building academic confidence and practical skills
            through flexible online education with Edufluxa.
        </p>

        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>

    </div>
</section>

@endsection
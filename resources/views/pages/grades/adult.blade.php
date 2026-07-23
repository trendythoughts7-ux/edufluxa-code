@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    By Grade / Level
                </span>

                <h1 class="font-38 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Adult Learning Programmes
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height:1.8;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Flexible online learning opportunities for professionals, parents, and adult learners seeking personal growth and career development.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height:2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    Edufluxa adult learning programmes provide flexible and practical online education
                    designed to help learners improve professional skills, build confidence,
                    and achieve personal and career growth goals at any stage of life.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <img
                    src="https://images.unsplash.com/photo-1519389950473-47ba0277781c"
                    alt="Adult Learning"
                    class="img-fluid rounded-lg shadow-lg"
                    style="border-radius:24px;"
                >
            </div>

        </div>
    </div>
</section>

<section class="py-80 bg-white">
    <div class="container">

        <div class="text-center mb-60" data-aos="fade-up" data-aos-duration="1000">
            <span class="text-primary font-weight-bold">
                BY GRADE / LEVEL
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Adult Learning
            </h3>

            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Flexible online learning programmes designed to support personal development, career advancement, and lifelong learning opportunities.
            </p>
        </div>

        <div class="row">
            @php
                $programmes = [
                    'Career Development',
                    'Professional Communication',
                    'Digital Skills',
                    'Leadership Training',
                    'Personal Growth',
                    'Flexible Online Learning'
                ];
            @endphp

            @foreach($programmes as $index => $programme)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 120 }}">
                    <div class="bg-light p-4 rounded-lg shadow-sm h-100 border-0">

                        <div class="mb-4">
                            <div style="
                                width:65px;
                                height:65px;
                                background:linear-gradient(135deg,#4361ee,#3a0ca3);
                                border-radius:18px;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:white;
                                font-size:24px;
                                font-weight:700;
                            " data-aos="zoom-in" data-aos-duration="600" data-aos-delay="{{ ($index * 120) + 150 }}">
                                {{ substr($programme,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $programme }}
                        </h4>

                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Practical learning programmes designed to support professional success and lifelong personal growth.
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
            Continue Learning at Every Stage of Life
        </h3>

        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Develop new skills, improve career opportunities, and achieve personal growth through flexible online education.
        </p>

        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>

    </div>
</section>

@endsection
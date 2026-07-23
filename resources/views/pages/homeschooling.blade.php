@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    Homeschooling Programmes
                </span>

                <h1 class="font-38 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Flexible Homeschooling Programmes for Modern Families
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height:1.8;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Structured online homeschooling solutions designed to support personalised education and academic growth.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height:2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    Edufluxa homeschooling programmes provide families with flexible, supportive, and academically structured learning pathways. We help students learn at their own pace while maintaining educational quality through personalised guidance, curriculum planning, and continuous academic support.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <img
                    src="https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e"
                    alt="Homeschooling"
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
                PROGRAMME OPTIONS
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Flexible Homeschooling Solutions
            </h3>

            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Choose from flexible homeschooling pathways designed to support personalised education, academic success, and independent learning.
            </p>
        </div>

        <div class="row">
            @php
                $programmes = [
                    'Full Homeschooling Programme',
                    'Part-Time Homeschooling',
                    'Exam Preparation Track',
                    'International Curriculum',
                    'Flexible Learning Plans'
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
                            Structured homeschooling support designed to provide flexibility, academic guidance, and personalised learning experiences.
                        </p>

                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

<section class="py-80 bg-light">
    <div class="container">

        <div class="text-center mb-60" data-aos="fade-up" data-aos-duration="1000">
            <span class="text-primary font-weight-bold">
                HOW IT WORKS
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Simple & Supportive Learning Process
            </h3>
        </div>

        <div class="row">
            @php
                $steps = [
                    'Enrolment Process',
                    'Partner Schools',
                    'Curriculum Overview',
                    'Assessments & Examinations',
                    'Certification'
                ];
            @endphp

            @foreach($steps as $index => $step)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 120 }}">
                    <div class="bg-white p-4 rounded-lg shadow-sm h-100 border-0">

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $step }}
                        </h4>

                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Professional academic systems designed to support structured learning, progress monitoring, and educational success.
                        </p>

                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

<section class="py-80 bg-white">
    <div class="container">

        <div class="text-center mb-60" data-aos="fade-up" data-aos-duration="1000">
            <span class="text-primary font-weight-bold">
                SUPPORT SYSTEM
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Dedicated Learning Support
            </h3>

            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Comprehensive support services designed to help students and parents stay organised, informed, and academically successful.
            </p>
        </div>

        <div class="row">
            @php
                $support = [
                    'Dedicated Academic Adviser',
                    'Weekly Study Plans',
                    'Progress Tracking',
                    'Parent Reports',
                    'Live Classes'
                ];
            @endphp

            @foreach($support as $index => $item)
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
                                {{ substr($item,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $item }}
                        </h4>

                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Reliable academic guidance and structured support designed to improve learning outcomes and student confidence.
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
            Start Your Homeschooling Journey Today
        </h3>

        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Discover flexible homeschooling programmes designed to support personalised education and long-term academic success.
        </p>

        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>

    </div>
</section>

@endsection
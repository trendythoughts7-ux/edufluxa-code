@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    Tutoring Services
                </span>

                <h1 class="font-38 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Interactive Group Learning Classes
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height:1.8;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Interactive small-group learning sessions that encourage collaboration, discussion, and affordable academic support.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height:2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    Edufluxa group classes are designed to promote peer learning, active participation, and shared academic growth.
                    Students learn in a supportive environment where collaboration and discussion enhance understanding and confidence.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <img
                    src="https://images.unsplash.com/photo-1529070538774-1843cb3265df"
                    alt="Group Classes"
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
                TUTORING OPTIONS
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Group Classes
            </h3>

            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Learn in a collaborative environment where students share ideas, solve problems together, and grow academically through guided group learning sessions.
            </p>
        </div>

        <div class="row">
            @php
                $features = [
                    'Small Group Size',
                    'Interactive Discussions',
                    'Affordable Learning',
                    'Peer Collaboration',
                    'Expert Tutor Guidance',
                    'Regular Practice Sessions'
                ];
            @endphp

            @foreach($features as $index => $feature)
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
                                {{ substr($feature,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $feature }}
                        </h4>

                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Structured group learning designed to improve understanding, confidence, and academic performance through collaboration.
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
            Learn Together, Grow Together
        </h3>

        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Join interactive group classes and enhance your academic skills through collaboration and guided learning.
        </p>

        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>

    </div>
</section>

@endsection
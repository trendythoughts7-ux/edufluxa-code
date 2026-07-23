@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    About Edufluxa
                </span>

                <h1 class="font-38 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Empowering Students Through Modern Online Education
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height:1.8;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Building a supportive global learning community focused on academic success, confidence, and lifelong growth.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height:2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    At Edufluxa, we believe every student deserves access to personalised, flexible, and high-quality education.
                    Our platform combines modern learning technology with expert teaching to create engaging educational experiences that support both academic achievement and personal development.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1200">
                <img
                    src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d"
                    alt="Online Education"
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
                OUR COMPANY
            </span>
            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Building Modern Educational Experiences
            </h3>
            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                We are committed to delivering flexible, student-centred education designed to support academic success and lifelong learning.
            </p>
        </div>

        <div class="row">
            @php
                $company = [
                    'Our Story',
                    'Mission & Vision',
                    'Our Values',
                    'Why Choose Edufluxa?',
                    'What Makes Us Different'
                ];
            @endphp

            @foreach($company as $index => $item)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 150 }}">
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
                            " data-aos="zoom-in" data-aos-duration="600" data-aos-delay="{{ ($index * 150) + 200 }}">
                                {{ substr($item,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $item }}
                        </h4>
                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Dedicated educational solutions focused on flexibility, academic excellence, and student success.
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
                OUR TEAM
            </span>
            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Experienced Educators & Academic Professionals
            </h3>
            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Our team includes experienced educators, tutors, and academic specialists committed to delivering high-quality online learning experiences.
            </p>
        </div>

        <div class="row">
            @php
                $team = [
                    'Leadership Team',
                    'Academic Team',
                    'Tutor Network',
                    'Join Our Team',
                    'Career Opportunities'
                ];
            @endphp

            @foreach($team as $index => $member)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 150 }}">
                    <div class="bg-white p-4 rounded-lg shadow-sm h-100 border-0">
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
                            " data-aos="zoom-in" data-aos-duration="600" data-aos-delay="{{ ($index * 150) + 200 }}">
                                {{ substr($member,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $member }}
                        </h4>
                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Passionate professionals dedicated to innovation, quality education, and student development.
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
                OUR IMPACT
            </span>
            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Supporting Learners Worldwide
            </h3>
            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                We continue to support students, families, and educators through accessible online education and modern learning opportunities.
            </p>
        </div>

        <div class="row">
            @php
                $impact = [
                    'Our Students',
                    'Success Stories',
                    'Global Reach',
                    'Community Initiatives',
                    'Partners & Affiliations'
                ];
            @endphp

            @foreach($impact as $index => $item)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 150 }}">
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
                            " data-aos="zoom-in" data-aos-duration="600" data-aos-delay="{{ ($index * 150) + 200 }}">
                                {{ substr($item,0,1) }}
                            </div>
                        </div>

                        <h4 class="font-20 font-weight-bold mb-3">
                            {{ $item }}
                        </h4>
                        <p class="text-muted mb-0" style="line-height:1.9;">
                            Expanding educational opportunities through innovative online learning and academic support systems.
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
            Join the Future of Online Education
        </h3>
        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Discover flexible, student-focused learning programmes designed to support academic success and personal growth.
        </p>
        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>
    </div>
</section>

@endsection
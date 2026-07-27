@extends('design_1.web.layouts.app')

@section('content')

<section class="position-relative overflow-hidden py-80 bg-light">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge badge-primary p-2 px-3 mb-3" data-aos="fade-right" data-aos-duration="800">
                    All Subjects
                </span>

                <h1 class="font-38 font-weight-bold text-dark mb-4" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Explore All Learning Subjects
                </h1>

                <h2 class="font-20 text-secondary mb-4" style="line-height:1.8;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Browse our full range of subjects and find the right learning path with personalised, expert-led tutoring.
                </h2>

                <p class="text-muted font-16 mb-5" style="line-height:2;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    Edufluxa offers structured, interactive online learning support across core academic subjects,
                    helping students build strong foundations, sharpen critical thinking, and achieve their
                    academic goals with confidence.
                </p>

                <a href="/contact" class="btn btn-outline-primary btn-lg" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    Contact Us
                </a>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <img
                    src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f"
                    alt="All Subjects"
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
                LEARNING SUBJECTS
            </span>

            <h3 class="font-34 font-weight-bold mt-3 mb-4">
                Choose a Subject
            </h3>

            <p class="text-muted mx-auto" style="max-width:850px; line-height:2;">
                Each subject is taught through structured, interactive programmes designed to build understanding,
                confidence, and strong academic performance.
            </p>
        </div>

        <div class="row">
            @php
                $subjects = [
                    ['title' => 'Mathematics', 'url' => '/classes/math', 'desc' => 'Strengthen numerical understanding, logical reasoning, and problem-solving skills.'],
                    ['title' => 'Science', 'url' => '/classes/science', 'desc' => 'Explore core scientific concepts through guided, curiosity-driven learning.'],
                    ['title' => 'Physics', 'url' => '/classes/physics', 'desc' => 'Build a strong foundation in physical concepts, formulas, and problem-solving.'],
                    ['title' => 'Biology', 'url' => '/classes/biology', 'desc' => 'Understand living systems and biological concepts through structured lessons.'],
                ];
            @endphp

            @foreach($subjects as $index => $subject)
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ $index * 120 }}">
                    <a href="{{ $subject['url'] }}" class="text-decoration-none">
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
                                    {{ substr($subject['title'],0,1) }}
                                </div>
                            </div>

                            <h4 class="font-20 font-weight-bold mb-3 text-dark">
                                {{ $subject['title'] }}
                            </h4>

                            <p class="text-muted mb-0" style="line-height:1.9;">
                                {{ $subject['desc'] }}
                            </p>

                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
</section>

<section class="py-80 bg-primary text-white text-center" data-aos="zoom-in-up" data-aos-duration="1000">
    <div class="container">

        <h3 class="font-36 font-weight-bold mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
            Find the Right Subject for You
        </h3>

        <p class="mx-auto mb-5" style="max-width:700px; line-height:2;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
            Start learning today with personalised, expert-led tutoring across all core subjects.
        </p>

        <a href="/register" class="btn btn-light btn-lg px-5" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
            Get Started
        </a>

    </div>
</section>

@endsection

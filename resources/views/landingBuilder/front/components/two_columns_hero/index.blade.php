@if(false) 
    {{-- 
        =================================================================
        🚫 ORIGINAL DATABASE SYSTEM (DISABLED)
        =================================================================
    --}}
    @php
        $contents = [];
        if (!empty($landingComponent->content)) {
            $contents = json_decode($landingComponent->content, true);
        }
    @endphp

    @push('styles_top')
        <link rel="stylesheet" href="{{ getLandingComponentStylePath("two_columns_hero") }}">
    @endpush

    <div class="two-columns-hero-section ">
        <div class="container h-100">
            <div class="row h-100 flex-column-reverse flex-lg-row">
                </div>
        </div>
    </div>
@endif

<div class="custom-edufluxa-hero" style="background: #f4f7f6; padding: 80px 0; overflow: hidden; width: 100%;">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-12 col-lg-7 text-center text-lg-start" style="padding-right: 30px;">
                
                <div class="d-inline-flex align-items-center mb-4 px-3 py-2 bg-white rounded-pill shadow-sm border" style="gap: 10px;" data-aos="fade-right" data-aos-duration="800">
                    <span class="badge bg-primary text-white px-2 py-1 rounded-pill fw-bold" style="font-size: 11px; letter-spacing: 0.5px; transform: translateY(-1px);">NEW</span>
                    <span class="text-dark fw-semibold" style="font-size: 13px; color: #2b3a55 !important;">Global Learning Platform</span>
                </div>

                <h1 class="mb-3" style="font-size: 38px; font-weight: 800; line-height: 1.4 !important; letter-spacing: -0.5px; color: #1e2638 !important; margin-bottom: 20px !important;" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="100">
                    Personalised Online Tutoring, <span class="text-primary">Language Learning</span> & Homeschooling for Every Student
                </h1>
                
                <h2 class="mb-4" style="font-size: 18px; font-weight: 500; line-height: 1.6 !important; color: #506578 !important; margin-bottom: 25px !important;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    Flexible, modern, and student-focused education designed to help learners succeed academically, confidently, and globally.
                </h2>
                
                <div class="hero-desc mb-4" style="font-size: 15px; line-height: 1.8 !important; color: #697a8d !important;" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    <p style="margin-bottom: 15px !important;">
                        At Edufluxa, we provide high-quality online tutoring, interactive language courses, and structured homeschooling programmes for students of all ages and academic levels. Our experienced tutors and personalised learning approach help students improve academic performance, build confidence, and achieve long-term success through flexible online education.
                    </p>
                    <p style="margin-bottom: 30px !important;">
                        Whether your child needs additional academic support, language improvement, or a complete homeschooling pathway, Edufluxa offers expert guidance, engaging lessons, and tailored learning experiences designed around individual goals and learning styles.
                    </p>
                </div>
                
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start" style="margin-top: 20px; gap: 15px;" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    
                    <a href="/book-demo" class="btn btn-primary" style="border-radius: 10px; font-size: 15px; font-weight: 700; min-height: 50px; padding: 10px 24px; display: inline-flex; justify-content: center; align-items: center; text-align: center; line-height: 1 !important; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none;">
                        Book a Free Demo
                    </a>
                    
                    <a href="/courses" class="btn btn-outline-secondary bg-white" style="border-radius: 10px; font-size: 15px; font-weight: 600; color: #4e5d78; border-color: #d1d8e0; min-height: 50px; padding: 10px 24px; display: inline-flex; justify-content: center; align-items: center; text-align: center; line-height: 1 !important; text-decoration: none;">
                        Explore Courses
                    </a>

                    <a href="/tutors" class="btn btn-outline-secondary bg-white" style="border-radius: 10px; font-size: 15px; font-weight: 600; color: #4e5d78; border-color: #d1d8e0; min-height: 50px; padding: 10px 24px; display: inline-flex; justify-content: center; align-items: center; text-align: center; line-height: 1 !important; text-decoration: none;">
                        Find a Tutor
                    </a>
                    
                </div>
            </div>

            <div class="col-12 col-lg-5 mt-5 mt-lg-0 text-center" data-aos="zoom-in" data-aos-duration="1100">
                <div class="position-relative d-inline-block w-100" style="max-width: 480px;">
                    <div class="position-absolute rounded-4 bg-primary opacity-10" style="top: 15px; left: 15px; width: 100%; height: 100%; z-index: 1; transform: rotate(1.5deg);"></div>
                    
                    <div class="position-relative overflow-hidden bg-white shadow p-2" style="border-radius: 20px; z-index: 2; border: 1px solid #eef2f5;">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800" alt="Edufluxa Global Education" class="img-fluid rounded-3" style="width: 100%; object-fit: cover; height: auto; display: block;">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
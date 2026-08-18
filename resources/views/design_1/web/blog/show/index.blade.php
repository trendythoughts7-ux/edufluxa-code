@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("show_blog") }}">
@endpush


@php
    $breadcrumbItems = [['name' => 'Home', 'url' => '/'], ['name' => 'Blog', 'url' => '/blog']];
    if (!empty($post->category)) {
        $breadcrumbItems[] = ['name' => $post->category->title, 'url' => '/blog/categories/' . $post->category->slug];
    }
    $breadcrumbItems[] = ['name' => $post->title, 'url' => '/blog/' . $post->slug];
@endphp
@include('design_1.web.includes.breadcrumb_jsonld')
@php
    $blogPublishedDate = \Carbon\Carbon::createFromTimestamp($post->created_at)->toIso8601String();
    $blogModifiedDate = \Carbon\Carbon::createFromTimestamp($post->updated_at)->toIso8601String();
    $blogDescription = !empty($post->meta_description) ? $post->meta_description : strip_tags($post->description);
@endphp
@push('scripts_top')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $blogDescription,
        'image' => url($post->image),
        'url' => url('/blog/' . $post->slug),
        'datePublished' => $blogPublishedDate,
        'dateModified' => $blogModifiedDate,
        'author' => [
            '@type' => 'Person',
            'name' => $post->author->full_name ?? $generalSettings['site_name'],
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $generalSettings['site_name'],
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url($generalSettings['logo']),
            ],
        ],
    ]) !!}
    </script>
@endpush
@section("content")
    <div class="blog-show-hero position-relative">
        <div class="blog-show-hero__mask"></div>
        <img src="{{ $post->image }}" alt="{{ $post->title }}" class="img-cover">
    </div>

    <div class="container position-relative blog-show-body pb-120">
        <div class="blog-show-cover-image position-relative rounded-32">
            <img src="{{ $post->image }}" alt="{{ $post->title }}" class="img-cover rounded-32">
        </div>

        {{-- Header --}}
        @include('design_1.web.blog.show.includes.header')

        {{-- Short Description --}}
        <div class="mt-28 p-16 rounded-16 border-gray-200 bg-gray-100">
            {!! nl2br($post->description) !!}
        </div>

        {{-- Post content --}}
        <div class="mt-24">
            {!! nl2br($post->content) !!}
        </div>

        {{-- Author Info --}}
        @include('design_1.web.blog.show.includes.author_info')

        {{-- Suggested Post --}}
        @include('design_1.web.blog.show.includes.suggested_post')

        {{-- Comments --}}
        @if($post->enable_comment)
            @include('design_1.web.blog.show.includes.comments')
        @endif

    </div>

    {{-- Fixed Bottom --}}
    @include('design_1.web.blog.show.includes.fixed_bottom')

@endsection

@push('scripts_bottom')
    <script>
        var closeLang = '{{ trans('public.close') }}';
        var shareLang = '{{ trans('public.share') }}';
        var reportCommentLang = '{{ trans('update.report_comment') }}';
        var reportLang = '{{ trans('panel.report') }}';
    </script>

    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>

    <script src="{{ getDesign1ScriptPath("comments") }}"></script>
    <script src="{{ getDesign1ScriptPath("show_blog") }}"></script>
@endpush

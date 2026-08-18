@php
    $breadcrumbListItems = [];
    if (!empty($breadcrumbItems) && is_array($breadcrumbItems)) {
        foreach ($breadcrumbItems as $bcIndex => $bcItem) {
            $breadcrumbListItems[] = [
                '@type' => 'ListItem',
                'position' => $bcIndex + 1,
                'name' => strip_tags($bcItem['name']),
                'item' => url($bcItem['url']),
            ];
        }
    }
@endphp
@if(!empty($breadcrumbListItems))
@push('scripts_top')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbListItems,
    ]) !!}
    </script>
@endpush
@endif

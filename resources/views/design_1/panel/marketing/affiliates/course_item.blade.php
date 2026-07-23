@php
    $item = $affiliateItem['item'];
    $affiliateUrl = $affiliateItem['affiliateUrl'];
    $salesCount = $affiliateItem['salesCount'] ?? 0;
    $totalEarnings = $affiliateItem['totalEarnings'] ?? 0;
    $linkId = $affiliateItem['linkId'] ?? null;
    $typeLabel = $affiliateItem['typeLabel'] ?? '';
    $type = $affiliateItem['type'] ?? 'course';

    // Get image based on type
    $itemImage = null;
    if (method_exists($item, 'getIcon')) {
        $itemImage = $item->getIcon();
    } elseif (method_exists($item, 'getImage')) {
        $itemImage = $item->getImage();
    }

    // Get teacher/creator name
    $ownerName = '';
    if (!empty($item->teacher)) {
        $ownerName = $item->teacher->full_name;
    } elseif (!empty($item->creator)) {
        $ownerName = $item->creator->full_name;
    }
@endphp

<tr>
    <td class="text-left" style="min-width: 280px;">
        <div class="d-flex align-items-center">
            @if(!empty($itemImage))
                <div class="size-48 rounded-12 bg-gray-100 flex-shrink-0">
                    <img src="{{ $itemImage }}" alt="{{ $item->title }}" class="img-cover rounded-12">
                </div>
            @endif
            <div class="ml-8">
                <a href="{{ $item->getUrl() }}" target="_blank"
                    class="font-14 font-weight-500 text-dark d-block text-ellipsis">
                    {{ truncate($item->title, 35) }}
                </a>
                @if(!empty($item->category))
                    <span class="font-12 text-gray-500 d-block mt-4">{{ $item->category->title }}</span>
                @endif
            </div>
        </div>
    </td>
    <td class="text-center align-middle">
        <span
            class="text-center font-14 ">{{ $typeLabel }}</span>
    </td>
    <td class="text-center align-middle">
        @if($type == 'event' && method_exists($item, 'getMinAndMaxPrice'))
            @php $eventPrices = $item->getMinAndMaxPrice(); @endphp
            <span class="font-14 text-dark">{{ handlePrice($eventPrices['min']) }}</span>
        @else
            <span class="font-14 text-dark">{{ handlePrice($item->price ?? 0) }}</span>
        @endif
    </td>
    <td class="text-center align-middle">
        @if(!empty($ownerName))
            <span class="font-14">{{ $ownerName }}</span>
        @endif
    </td>
    <td class="text-center align-middle">
        <span class="font-14 font-weight-500 text-dark">{{ $salesCount }}</span>
    </td>
    <td class="text-center align-middle">
        <span class="font-14 font-weight-500 text-{{ $totalEarnings > 0 ? 'success' : 'dark' }}">
            {{ handlePrice($totalEarnings) }}
        </span>
    </td>
    <td class="text-left" style="min-width: 280px;">
        <div class="form-group mb-0">
            <input type="text" readonly value="{{ $affiliateUrl }}" class="form-control form-control-sm">
        </div>
    </td>
    <td class="text-right">
        <div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
            <button type="button" class="d-flex-center size-36 bg-gray border-gray-200 rounded-10">
                <x-iconsax-lin-more class="icons text-gray-500" width="18" />
            </button>

            <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220 dropdown-menu-top-32">
                <ul class="my-8">
                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="#" class="js-copy-affiliate-link"
                            data-link="{{ $affiliateUrl }}">{{ trans('update.copy_link') }}</a>
                    </li>

                    @if(!empty($linkId))
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/marketing/affiliates/courses/{{ $linkId }}/delete"
                                class="d-flex align-items-center w-100 px-16 py-8 btn-transparent text-danger delete-action">{{ trans('public.delete') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </td>
</tr>
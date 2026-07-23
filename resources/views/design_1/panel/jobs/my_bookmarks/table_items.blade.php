@php
    $job = $bookmark->job;
@endphp

<tr>
    {{-- Job Position --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $job->getIcon() }}" alt="{{ $job->title }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <a href="{{ $job->getUrl() }}" target="_blank" class="text-dark">{{ $job->title }}</a>
                <div class="mt-4 font-12 text-gray-500">{{ $job->category->title }}</div>
            </div>
        </div>
    </td>

    {{-- Employer --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $job->creator->getAvatar(8) }}" alt="{{ $job->creator->full_name }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <div class="">{{ $job->creator->full_name }}</div>

                @if(!empty($job->creator->email))
                    <div class="mt-4 font-12 text-gray-500">{{ $job->creator->email }}</div>
                @endif

                @if(!empty($job->creator->mobile))
                    <div class="mt-4 font-12 text-gray-500">+{{ $job->creator->mobile }}</div>
                @endif
            </div>
        </div>
    </td>

    {{-- Employment Type --}}
    <td class="text-center">
        {{ !empty($job->employmentType) ? $job->employmentType->title : '-' }}
    </td>

    {{-- Experience Level --}}
    <td class="text-center">
        {{ !empty($job->experienceLevel) ? $job->experienceLevel->title : '-' }}
    </td>

    {{-- Work Arrangement --}}
    <td class="text-center">
        {{ trans("update.{$job->work_arrangement}") }}
    </td>

    {{-- Salary --}}
    <td class="text-center">
        <div class="d-flex align-items-center gap-2 text-dark">
            @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                @if($job->payment_type == "fixed_amount")
                    <span class="">{{ handlePrice($job->fixed_salary) }}</span>
                @else
                    <span class="">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                @endif

                <span class="">/{{ trans("update.{$job->payment_period}") }}</span>
            @else
                <span class="">{{ trans('update.negotiable') }}</span>
            @endif
        </div>
    </td>

    {{-- Created Date --}}
    <td class="text-center">
        <span>{{ dateTimeFormat($job->created_at, 'j M Y') }}</span>
    </td>

    {{-- Actions --}}
    <td class="text-right">

        <div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
            <button type="button" class="d-flex-center size-36 bg-gray border-gray-200 rounded-10">
                <x-iconsax-lin-more class="icons text-gray-500" width="18"/>
            </button>

            <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220 dropdown-menu-top-32">
                <ul class="my-8">

                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="{{ $job->getUrl() }}" target="_blank" class="">{{ trans('update.view_job') }}</a>
                    </li>


                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="/panel/jobs/my-bookmarks/{{ $bookmark->id }}/delete" class="delete-action text-danger">{{ trans('update.remove_bookmark') }}</a>
                    </li>

                </ul>
            </div>
        </div>

    </td>

</tr>

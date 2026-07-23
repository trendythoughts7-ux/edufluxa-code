@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item">{{ trans('update.jobs') }}</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Top Stats --}}
            @include('admin.jobs.lists.top_stats')

            {{-- Filters --}}
            @include('admin.jobs.lists.filters')


            <div class="card">
                <div class="card-header justify-content-between">
                    <div>
                        <h5 class="font-14 mb-0">{{ trans('update.jobs') }}</h5>
                        <p class="font-12 mt-4 mb-0 text-gray-500">{{ trans('update.manage_all_jobs_in_a_single_place') }}</p>
                    </div>

                    <div class="d-flex align-items-center gap-12">

                        @can('admin_jobs_lists_export_excel')
                            <a href="{{ getAdminPanelUrl('/jobs/export-excel?'. http_build_query(request()->all())) }}" class="btn bg-white bg-hover-gray-100 border-gray-400 text-gray-500">
                                <x-iconsax-lin-import-2 class="icons text-gray-500" width="18px" height="18px"/>
                                <span class="ml-4 font-12">{{ trans('admin/main.export_xls') }}</span>
                            </a>
                        @endcan

                        @can('admin_jobs_create')
                            <a href="{{ getAdminPanelUrl('/jobs/create') }}" target="_blank" class="btn btn-primary">
                                <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                <span class="ml-4 font-12">{{ trans('update.new_job') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-0">
                    <div>
                        <table class="table custom-table font-14 ">
                            <tr>
                                <th class="text-left">{{trans('update.job')}}</th>
                                <th class="text-left">{{trans('update.employer')}}</th>
                                <th>{{trans('update.employment_type')}}</th>
                                <th>{{trans('update.experience_level')}}</th>
                                <th>{{trans('update.work_arrangement')}}</th>
                                <th>{{trans('update.salary')}}</th>
                                <th>{{trans('update.apply_requests')}}</th>
                                <th>{{trans('admin/main.created_at')}}</th>
                                <th>{{trans('update.expiry_end_date')}}</th>
                                <th>{{trans('admin/main.status')}}</th>
                                <th width="120">{{trans('admin/main.actions')}}</th>
                            </tr>

                            @foreach($jobs as $job)
                                @include('admin.jobs.lists.table_items')
                            @endforeach
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    {{ $jobs->appends(request()->input())->links() }}
                </div>

            </div>

        </div>
    </section>
@endsection

@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('update.response_reasons') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item "><a href="{{ getAdminPanelUrl("/jobs") }}">{{trans('update.jobs')}}</a></div>
                <div class="breadcrumb-item">{{ trans('update.response_reasons') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">

                        <div class="card-header justify-content-between">

                            <div>
                                <h5 class="font-14 mb-0">{{ $pageTitle }}</h5>
                                <p class="font-12 mt-4 mb-0 text-gray-500">{{ trans('update.manage_all_items_in_a_single_place') }}</p>
                            </div>

                            <div class="d-flex align-items-center gap-12">
                                <a href="{{ getAdminPanelUrl("/jobs/response-reasons/create") }}" target="_blank" class="btn btn-primary">
                                    <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                    <span class="ml-4 font-12">{{ trans('admin/main.add_new') }}</span>
                                </a>
                            </div>
                        </div>


                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table custom-table font-14">
                                    <tr>
                                        <th>{{ trans('admin/main.title') }}</th>
                                        <th class="text-center">{{ trans('public.type') }}</th>
                                        <th class="text-center">{{ trans('public.status') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($responseReasons as $responseReason)
                                        <tr>
                                            <td>{{ $responseReason->title }}</td>

                                            <td class="text-center">{{ trans("update.{$responseReason->type}") }}</td>

                                            <td class="text-center">{{ $responseReason->enable ? trans('admin/main.active') : trans('admin/main.inactive') }}</td>

                                            <td width="80px">
                                                <div class="btn-group dropdown table-actions position-relative">
                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @can('admin_store_filters_edit')
                                                            <a href="{{ getAdminPanelUrl() }}/jobs/response-reasons/{{ $responseReason->id }}/edit"
                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                            </a>
                                                        @endcan

                                                        @include('admin.includes.delete_button',[
                                                            'url' => getAdminPanelUrl().'/jobs/response-reasons/'.$responseReason->id.'/delete',
                                                            'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                            'btnText' => trans('admin/main.delete'),
                                                            'btnIcon' => 'trash',
                                                            'iconType' => 'lin',
                                                            'iconClass' => 'text-danger mr-2'
                                                        ])

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $responseReasons->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


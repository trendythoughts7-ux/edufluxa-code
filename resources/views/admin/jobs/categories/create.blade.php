@extends('admin.layouts.app')

@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/jobs/categories">{{ trans('admin/main.categories') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle  }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl('/jobs/categories/') . (!empty($category) ? ($category->id.'/update') : 'store') }}"
                                  method="Post">
                                {{ csrf_field() }}

                                @if(!empty(getGeneralSettings('content_translate')))
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('auth.language') }}</label>
                                        <select name="locale" class="form-control {{ !empty($category) ? 'js-edit-content-locale' : '' }}">
                                            @foreach($userLanguages as $lang => $language)
                                                <option value="{{ $lang }}" @if($locale == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                            @endforeach
                                        </select>
                                        @error('locale')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                @endif

                                <div class="form-group">
                                    <label>{{ trans('admin/main.title') }}</label>
                                    <input type="text" name="title"
                                           class="form-control  @error('title') is-invalid @enderror"
                                           value="{{ (!empty($category) and !empty($category->translate($locale))) ? $category->translate($locale)->title : old('title') }}"
                                           placeholder="{{ trans('admin/main.choose_title') }}"/>
                                    @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.subtitle') }}</label>
                                    <input type="text" name="subtitle"
                                           class="form-control  @error('subtitle') is-invalid @enderror"
                                           value="{{ (!empty($category) and !empty($category->translate($locale))) ? $category->translate($locale)->subtitle : old('subtitle') }}"
                                           placeholder="{{ trans('admin/main.choose_subtitle') }}"/>
                                    @error('subtitle')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('update.bottom_seo_title') }}</label>
                                    <input type="text" name="bottom_seo_title" value="{{ (!empty($category) and !empty($category->translate($locale))) ? $category->translate($locale)->bottom_seo_title : old('bottom_seo_title') }}" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('update.bottom_seo_description') }}</label>
                                    <textarea name="bottom_seo_description" rows="4" class="form-control">{{ (!empty($category) and !empty($category->translate($locale))) ? $category->translate($locale)->bottom_seo_description : old('bottom_seo_description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.icon') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button type="button" class="input-group-text admin-file-manager " data-input="icon" data-preview="holder">
                                                <i class="fa fa-upload"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="icon" id="icon" value="{{ !empty($category) ? $category->icon : old('icon') }}" class="form-control @error('icon') is-invalid @enderror"/>
                                        <div class="input-group-append">
                                            <button type="button" class="input-group-text admin-file-view" data-input="icon">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>

                                        <div class="invalid-feedback">@error('icon') {{ $message }} @enderror</div>
                                    </div>
                                </div>

                                <div class="form-group custom-switches-stacked">
                                    <label class="custom-switch pl-0">
                                        <input type="hidden" name="enable" value="">
                                        <input type="checkbox" name="enable" id="enableSwitch" value="on" {{ (!empty($category) and $category->enable) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="enableSwitch">{{ trans('admin/main.active') }}</label>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input id="hasSubCategory" type="checkbox" name="has_sub"
                                               class="custom-control-input" {{ (!empty($subCategories) and !$subCategories->isEmpty()) ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                               for="hasSubCategory">{{ trans('admin/main.has_sub_category') }}</label>
                                    </div>
                                </div>

                                <div id="subCategories" class="ml-0 {{ (!empty($subCategories) and !$subCategories->isEmpty()) ? '' : ' d-none' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <strong class="d-block">{{ trans('admin/main.add_sub_categories') }}</strong>

                                        <button type="button" class="btn btn-success add-btn"><i class="fa fa-plus"></i> {{ trans('update.add') }}</button>
                                    </div>

                                    <ul class="draggable-lists list-group">

                                        @if((!empty($subCategories) and !$subCategories->isEmpty()))
                                            @foreach($subCategories as $key => $subCategory)
                                                <li class="form-group list-group">

                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text cursor-pointer move-icon">
                                                                <i class="fa fa-arrows-alt"></i>
                                                            </div>
                                                        </div>

                                                        <input type="text" name="sub_categories[{{ $subCategory->id }}][title]"
                                                               class="form-control w-auto flex-grow-1"
                                                               value="{{ (!empty($subCategory->translate($locale))) ? $subCategory->translate($locale)->title : '' }}"
                                                               placeholder="{{ trans('admin/main.choose_title') }}"/>

                                                        <div class="input-group-append">
                                                            <button type="button" class="btn remove-btn btn-danger"><i class="fa fa-times"></i></button>
                                                        </div>
                                                    </div>

                                                    <div class="input-group mt-2">
                                                        <input type="text" name="sub_categories[{{ $subCategory->id }}][subtitle]"
                                                               class="form-control w-auto flex-grow-1"
                                                               value="{{ (!empty($subCategory->translate($locale))) ? $subCategory->translate($locale)->subtitle : '' }}"
                                                               placeholder="{{ trans('admin/main.choose_subtitle') }}"/>
                                                    </div>

                                                    <div class="input-group mt-2">
                                                        <input type="text" name="sub_categories[{{ $subCategory->id }}][bottom_seo_title]"
                                                               class="form-control w-auto flex-grow-1"
                                                               value="{{ (!empty($subCategory->translate($locale))) ? $subCategory->translate($locale)->bottom_seo_title : '' }}"
                                                               placeholder="{{ trans('update.bottom_seo_title') }}"/>
                                                    </div>

                                                    <div class="input-group mt-2">
                                                        <textarea type="text" name="sub_categories[{{ $subCategory->id }}][bottom_seo_description]"
                                                                  class="form-control w-auto flex-grow-1" rows="3"
                                                                  placeholder="{{ trans('update.bottom_seo_description') }}">{{ (!empty($subCategory->translate($locale))) ? $subCategory->translate($locale)->bottom_seo_description : '' }}</textarea>
                                                    </div>

                                                    <div class="input-group mt-1">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="input-group-text admin-file-manager " data-input="icon_{{ $subCategory->id }}" data-preview="holder">
                                                                <i class="fa fa-upload"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="sub_categories[{{ $subCategory->id }}][icon]" id="icon_{{ $subCategory->id }}" class="form-control" value="{{ $subCategory->icon }}" placeholder="{{ trans('admin/main.icon') }}"/>
                                                    </div>

                                                    <div class="form-group custom-switches-stacked mb-0">
                                                        <label class="custom-switch pl-0">
                                                            <input type="hidden" name="sub_categories[{{ $subCategory->id }}][enable]" value="">
                                                            <input type="checkbox" name="sub_categories[{{ $subCategory->id }}][enable]" id="enableSwitch_{{ $subCategory->id }}" value="on" {{ ($subCategory->enable) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                            <span class="custom-switch-indicator"></span>
                                                            <label class="custom-switch-description mb-0 cursor-pointer" for="enableSwitch_{{ $subCategory->id }}">{{ trans('admin/main.active') }}</label>
                                                        </label>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>

                                <div class="text-right mt-4">
                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>

                            <li class="form-group main-row list-group d-none">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text cursor-pointer move-icon">
                                            <i class="fa fa-arrows-alt"></i>
                                        </div>
                                    </div>

                                    <input type="text" name="sub_categories[record][title]"
                                           class="form-control w-auto flex-grow-1"
                                           placeholder="{{ trans('admin/main.choose_title') }}"/>

                                    <div class="input-group-append">
                                        <button type="button" class="btn remove-btn btn-danger"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>

                                <div class="input-group mt-2">
                                    <input type="text" name="sub_categories[record][subtitle]"
                                           class="form-control w-auto flex-grow-1"
                                           placeholder="{{ trans('admin/main.choose_subtitle') }}"/>
                                </div>

                                <div class="input-group mt-2">
                                    <input type="text" name="sub_categories[record][bottom_seo_title]"
                                           class="form-control w-auto flex-grow-1"
                                           value=""
                                           placeholder="{{ trans('update.bottom_seo_title') }}"/>
                                </div>

                                <div class="input-group mt-2">
                                    <textarea type="text" name="sub_categories[record][bottom_seo_description]"
                                              class="form-control w-auto flex-grow-1" rows="3"
                                              placeholder="{{ trans('update.bottom_seo_description') }}"></textarea>
                                </div>

                                <div class="input-group mt-1">
                                    <div class="input-group-prepend">
                                        <button type="button" class="input-group-text admin-file-manager " data-input="icon_record" data-preview="holder">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                    </div>
                                    <input type="text" name="sub_categories[record][icon]" id="icon_record" class="form-control" placeholder="{{ trans('admin/main.icon') }}"/>
                                </div>

                                <div class="form-group custom-switches-stacked mb-0">
                                    <label class="custom-switch pl-0">
                                        <input type="hidden" name="sub_categories[record][enable]" value="">
                                        <input type="checkbox" name="sub_categories[record][enable]" id="enableSwitch_record" value="on" class="custom-switch-input"/>
                                        <span class="custom-switch-indicator"></span>
                                        <label class="custom-switch-description mb-0 cursor-pointer" for="enableSwitch_record">{{ trans('admin/main.active') }}</label>
                                    </label>
                                </div>

                            </li>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>

    <script src="/assets/admin/js/parts/categories.min.js"></script>
@endpush

<div class="mt-16">
    <div class="form-group">
        <label class="form-group-label">{{ trans('public.type') }}</label>
        <select id="affiliateTypeSelect" class="form-control" style="width:100%;">
            <option value="course">{{ trans('webinars.course') }}</option>
            <option value="bundle">{{ trans('update.bundle') }}</option>
            <option value="product">{{ trans('update.product') }}</option>
            <option value="event">{{ trans('update.event') }}</option>
        </select>
    </div>

    <div class="form-group mt-16">
        <label class="form-group-label">{{ trans('update.select_a_item') }}</label>
        <select id="courseSearchSelect" class="form-control" style="width:100%;"
            data-placeholder="{{ trans('update.search_and_select_course') }}">
            <option value=""></option>
        </select>
        <div class="invalid-feedback" id="courseSelectError"></div>
    </div>
</div>
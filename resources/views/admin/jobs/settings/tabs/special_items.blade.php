<div class="form-group mb-16">
    <label>{{ trans('public.title') }}</label>
    <input type="text"
           name="value[special_items][{{ !empty($itemKey) ? $itemKey : 'record' }}][title]"
           class="form-control "
           value="{{ (!empty($specialData) and !empty($specialData['title'])) ? $specialData['title'] : '' }}">
</div>

<div class="form-group">
    <label>{{ trans('admin/main.url') }}</label>
    <input type="text"
           name="value[special_items][{{ !empty($itemKey) ? $itemKey : 'record' }}][url]"
           class="form-control "
           value="{{ (!empty($specialData) and !empty($specialData['url'])) ? $specialData['url'] : '' }}">
</div>

<div class="d-flex align-items-center justify-content-end mt-24">
    <button type="button" class="js-addable-accordion-remove-btn btn btn-danger">{{ trans('public.delete') }}</button>
</div>


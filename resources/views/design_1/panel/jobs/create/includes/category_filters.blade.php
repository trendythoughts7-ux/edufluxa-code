@foreach($jobCategoryFilters as $filter)
    <div class="col-12 col-md-3 mt-16">
        <div class="create-course-filter-card bg-white p-16 rounded-12 border-gray-200">
            <h5 class="font-14 font-weight-bold mb-16">{{ $filter->title }}</h5>

            @foreach($filter->options as $option)
                <div class="custom-control custom-checkbox {{ $loop->first ? '' : 'mt-12' }}">
                    <input type="checkbox" name="filters[]" value="{{ $option->id }}" id="filterOptions{{ $option->id }}" class="custom-control-input" {{ ((!empty($jobFilterOptions) && in_array($option->id, $jobFilterOptions)) ? 'checked' : '') }}>
                    <label class="custom-control__label cursor-pointer" for="filterOptions{{ $option->id }}">{{ $option->title }}</label>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

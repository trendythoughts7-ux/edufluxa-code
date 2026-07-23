<div class="mt-24">
    <h4 class="font-14 font-weight-bold">{{ trans('update.attachments') }}</h4>

    @error('attachments')
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    <div class="js-attachment-item-inputs d-flex align-items-center gap-16 position-relative mt-24">
        <div class="form-group mb-0 flex-1">
            <label class="form-group-label">{{ trans('public.title') }}</label>
            <input type="text" name="attachments[record][title]" class="form-control">
        </div>

        <div class="form-group mb-0 flex-1">
            <label class="form-group-label">{{ trans('update.attachment') }}</label>
            <div class="d-flex align-items-center">
                <div class="custom-file bg-white">
                    <input type="file" name="attachments[record][file]" class="custom-file-input bg-white js-ajax-upload-file-input" data-upload-name="attachments[record][file]" id="attachmentInput1">
                    <span class="custom-file-text bg-white"></span>
                    <label class="custom-file-label bg-transparent" for="attachmentInput1">
                        <x-iconsax-lin-export class="icons text-gray-border" width="24px" height="24px"/>
                    </label>
                </div>
            </div>
        </div>

        <div class="js-add-attachment d-flex-center size-48 bg-primary rounded-12 cursor-pointer">
            <x-iconsax-lin-add class="text-white" width="24px" height="24px"/>
        </div>
    </div>

    <div class="js-attachments-card">

    </div>


</div>

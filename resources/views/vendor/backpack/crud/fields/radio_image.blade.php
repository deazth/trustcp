@push('after_styles')
<style>
    [type=radio] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
</style>
@endpush

<!-- radio -->
@php
    $optionValue = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';


    // check if attribute is casted, if it is, we get back un-casted values
    if(Arr::get($crud->model->getCasts(), $field['name']) === 'boolean') {
        $optionValue = (int) $optionValue;
    }

    // if the class isn't overwritten, use 'radio'
    if (!isset($field['attributes']['class'])) {
        $field['attributes']['class'] = 'radio';
    }

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitRadioElement';
@endphp

@include('crud::fields.inc.wrapper_start')

    <div>
        <label>{!! $field['label'] !!}</label>
        @include('crud::fields.inc.translatable_icon')
    </div>

    <input type="hidden" value="{{ $optionValue }}" name="{{$field['name']}}" />

    @if( isset($field['options']) && $field['options'] = (array)$field['options'] )

        <div class="row">
        @foreach ($field['options'] as $value => $label )

            <div class="form-check {{ isset($field['inline']) && $field['inline'] ? 'form-check-inline' : '' }} col-md-4 col-sm-6 col-lg-3">

                <label class="{{ isset($field['inline']) && $field['inline'] ? 'radio-inline' : '' }} form-check-label font-weight-normal">
                    <input  type="radio"
                            class="form-check-input"
                            value="{{$value}}"
                            @include('crud::fields.inc.attributes')
                            >

                    <img src="{{ asset('img/postcard') }}/{{$value}}_front_2.png" width="100%" id="{{$label}}">
                </label>
            </div>

        @endforeach

        </div>
    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        function bpFieldInitRadioElement(element) {
            element.find('img').css('border', '2px solid transparent');

            var hiddenInput = element.find('input[type=hidden]');
            var value = hiddenInput.val();
            var id = 'radio_'+Math.floor(Math.random() * 1000000);

            // set unique IDs so that labels are correlated with inputs
            element.find('.form-check input[type=radio]').each(function(index, item) {
                $(this).attr('id', id+index);
                $(this).siblings('label').attr('for', id+index);
            });

            // when one radio input is selected
            element.find('input[type=radio]').change(function(event) {
                // the value gets updated in the hidden input
                hiddenInput.val($(this).val());
                // all other radios get unchecked
                element.find('input[type=radio]').not(this).prop('checked', false)
                element.find('img').not("#"+this.value).css('border', '2px solid transparent');
                $("#"+this.value).css('border', '2px solid red');
            });

            // select the right radios
            element.find('input[type=radio][value="'+value+'"]').prop('checked', true);
            $("#"+value).css('border', '2px solid red');
        }
    </script>
    @endpush

@endif

<!-- toggle2 field -->
<!-- checkbox field -->

<style>
/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 32px;
  
}

/* Hide default HTML checkbox */
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

/* The slider */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #FF6624;
}

input:focus + .slider {
    box-shadow: 0 0 1px #FF6624;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

</style>




@include('crud::fields.inc.wrapper_start')
@include('crud::fields.inc.translatable_icon')
<label>{!! $field['label'] !!}</label>
<div class="checkbox">
    <label class="switch">
        <input type="hidden" name="{{ $field['name'] }}" value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? 0 }}">
        <input type="checkbox" data-init-function="bpFieldInitCheckbox" @if (old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false) checked="checked" @endif @if (isset($field['attributes'])) @foreach ($field['attributes'] as $attribute=> $value)
        {{ $attribute }}="{{ $value }}"
        @endforeach
        @endif
        >
        <span class="slider round"></span></label>
        

    {{-- HINT --}}
    @if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

</div>
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
@php
$crud->markFieldTypeAsLoaded($field);
@endphp
{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
<script>
    function bpFieldInitCheckbox(element) {
        var hidden_element = element.siblings('input[type=hidden]');
        var id = 'checkbox_' + Math.floor(Math.random() * 1000000);

        // make sure the value is a boolean (so it will pass validation)
        if (hidden_element.val() === '') hidden_element.val(0);

        // set unique IDs so that labels are correlated with inputs
        element.attr('id', id);
        element.siblings('label').attr('for', id);

        // set the default checked/unchecked state
        // if the field has been loaded with javascript
        if (hidden_element.val() != 0) {
            element.prop('checked', 'checked');
        } else {
            element.prop('checked', false);
        }

        // when the checkbox is clicked
        // set the correct value on the hidden input
        element.change(function() {
            if (element.is(":checked")) {
                hidden_element.val(1);
            } else {
                hidden_element.val(0);
            }
        })
    }
</script>
@endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
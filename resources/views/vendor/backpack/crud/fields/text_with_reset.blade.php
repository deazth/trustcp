<!-- text input -->
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')



@if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
    @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix']
            !!}</span>

        <div class="col-md-3">
            <input type="button" id="tribe_assigment_reset_button" class="tribe_button btn btn-primary " value="{!! $field['reset_label']
            !!}"
                title="Cause sometimes you don't meant to." />
            hehhe 1q
        </div>

    </div> @endif

    <div class="row">
        <div class="col">
            <input type="text" name="{{ $field['name'] }}"
                value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
                @include('crud::fields.inc.attributes')>
        </div>

        <div class="col-md-3 col-sm-6">
            <input type="button" id="reset_button" class="btn btn-primary "value="{!! $field['reset_label']
            !!}" 
                title="Cause sometimes you don't meant to." />

        </div>
    </div>

    @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix']
            !!}</span></div> @endif
    @if(isset($field['prefix']) || isset($field['suffix']))



</div> @endif



{{-- HINT --}}
@if (isset($field['hint']))
<p class="help-block">{!! $field['hint'] !!}</p>
@endif


{{-- FIELD EXTRA JS --}}
{{-- push things in the after_scripts section --}}
@push('crud_fields_scripts')
<script>
    $("#reset_button").click(function() {
        @foreach ($field['reset_fields'] as $rf)
        $("#{{$rf}}").val("");
        @endforeach


    });
</script>
@endpush

</div>
@extends(backpack_view('blank'))
@php
$widgets['before_content'][] = [
'type' => 'jumbotron',
'heading' => 'Welcome '.'
<div id="welcomeName" />',

'button_link' => backpack_url('logout'),
'button_text' => trans('backpack::base.logout'),
];
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">

        <div class="col-8">
            <pre class="mb-0">
Name     : {{ $user['name'] }}
Staffno  : {{ $user['staff_no'] }}
Division : {{ $user['unit'] }}
Unit     : {{ $user['subunit'] }}
Position : {{ $user['position'] }}
Email    : {{ $user['email'] }}
Mobile   : {{ $user['mobile_no'] }}
@if(isset($superior))
Report To : <a href="{{ route('staff', ['staff_id' => $superior->id], false) }}">{{ $superior->name }}</a>
@endif
            </pre>
        </div>
        <div class="col-3">
            <img class="card-img" style="border: 1px solid #000; max-width:120px; max-height:120px;"
                src="{{ route('staff.image', ['staff_no' => $user['staff_no']]) }}" loading="lazy">
        </div>

    </div>
    {{ route('staff.image', ['staff_no' => $user['staff_no']]) }}
</div> <!-- container -->

@stop

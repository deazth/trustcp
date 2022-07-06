@extends(backpack_view('blank'))

@section('after_styles')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css" />
@stop

@section('header')
<section class="container-fluid">
  <h2>
    <span class="text-capitalize">Diary Overview</span>
    <small>{{ $user->name }}</small>
  </h2>
</section>
@endsection





@section('content')
<div class="row ">
  <div class="col-xl-6 card p-1">

    <div class="card-header">
      Summary
    </div>




    <div class="card mb-3" title="{{ isset($todaytitle) ? $todaytitle : '' }}">
      <div class="card-header bg-{{ $todaycol }} text-white">Today's Productivity</div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-4 border-right">
            <h1 class="card-title">{{ $todaydf->actual_hours }}</h1>
            <p class="card-text">
              Actual Hours
            </p>
          </div>
          <div class="col-4 border-right">
            <h1 class="card-title">{{ $todaydf->expected_hours }}</h1>
            <p class="card-text">
              Expected Hours
            </p>
          </div>
          <div class="col-4">
            <h1 class="card-title">{{ $todayperc }}%</h1>
            <p class="card-text">
              Productivity
            </p>
          </div>
        </div>
      </div>
    </div>


    <div class="card mb-3" title="{{ $weektitle }}">
      <div class="card-header bg-{{ $weekcol }} text-white">Past 7 Days ( {{ $cdate->toDateString() }} to {{ $ldate->toDateString() }} )</div>

      <div class="card-body">
        <div class="row text-center">
          <div class="col-4 border-right">
            <h1 class="card-title">{{ number_format($weekact, 1) }}</h1>
            <p class="card-text">
              Total Actual Hours
            </p>
          </div>
          <div class="col-4 border-right">
            <h1 class="card-title">{{ number_format($weekexp, 1) }}</h1>
            <p class="card-text">
              Expected Hours
            </p>
          </div>
          <div class="col-4">
            <h1 class="card-title">{{ $weekperc }}%</h1>
            <p class="card-text">
              Productivity
            </p>
          </div>
        </div>
      </div>
    </div>







      <!-- chart-->
      {!! $aca['chart']->container() !!}



  </div>
  <div class="col-xl-6 card p-1">
    <div class="card-header">
      Recent Diary Entries
    </div>
    <div class="card-body">
      {!! $calendar->calendar() !!}
    </div>
  </div>
</div>
@stop

@section('after_scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>

{!! $calendar->script() !!}

@if (is_array($aca['path']))
@foreach ($aca['path'] as $string)
<script src="{{ $string }}" charset="utf-8"></script>
@endforeach
@elseif (is_string($aca['path']))
<script src="{{ $aca['path'] }}" charset="utf-8"></script>
@endif

{!! $aca['chart']->script() !!}

{{$aca['chart']->id}}
<script>
  window.addEventListener('resize', function(){
    {{$aca['chart']->id}}.resize();
  });
</script>


@stop

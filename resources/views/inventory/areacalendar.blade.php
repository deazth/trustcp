@extends(backpack_view('blank'))

@section('after_styles')
  <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
  @if($gotdata == true)
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
  @endif
@stop

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Meeting Area Calendar</span>
        <small><a href="{{ backpack_url('userareabooking') }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> <span>Back to my bookings</span></a></small>
	  </h2>
	</section>
@endsection

@section('content')
  <div class="row">
    <div class="card col-xl-8 mb-1">
      <form method="get" action="{{ route('inventory.area.calendar') }}">
        <div class="row m-2">
          <label class="col-md-3 col-form-label text-md-right">Meeting Areas</label>
          <div class="col-md-8">
            <select class="form-control" name="areaid" id="arealistdd" onchange="this.form.submit();">
              @foreach($arealist as $ar)
                @if($gotdata == true)
                  <option value="{{ $ar->id }}" @if($ar->id == $marea->id) selected @endif >{{ $ar->long_label }}</option>
                @else
                  <option value="{{ $ar->id }}">{{ $ar->long_label }}</option>
                @endif

              @endforeach
            </select>
          </div>
        </div>
      </form>
    </div>
  	<div class="col-xl-8 card">
      <div class="card-body">
        @if($gotdata == true)
        {!! $calendar->calendar() !!}
        @else
          <p class="card-text">No meeting area available</p>
        @endif
      </div>
    </div>
  </div>
@stop

@section('after_scripts')
  <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
  @if($gotdata == true)
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>

  {!! $calendar->script() !!}
  @endif

<script type="text/javascript">
  $(document).ready(function() {
    document.getElementById("arealistdd").select2({
        theme: "bootstrap"
    });
    // $('#arealistdd').select2({
    //     theme: "bootstrap"
    // });
  });
</script>

@stop

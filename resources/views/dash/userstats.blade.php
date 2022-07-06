@extends(backpack_view('blank'))
@section('title', 'User Statistic')

@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
@stop

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{{ $title }}</span>
	  </h2>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="col-md-12 mb-1">
		<div class="card mb-3">
			<div class="card-body">
					<form action="{{ route($route)}}" method="get" id="searform">
						<input id="dp_start_id" type="hidden" name="sdate" value="{{ $sdate }}">
				    <input id="dp_end_id" type="hidden" name="edate" value="{{ $edate }}">

						<div class="row m-2">

							<div class="col-md-5 col-lg-6">
								<label>Date Range (Longer duration takes longer time to load)</label>
								<div class="input-group date">
						        <input
											id="dp_obj"
						            data-bs-daterangepicker="{{ json_encode(['autoApply' => true,
								        'startDate' => $sdate,
								        'endDate' => $edate,
								        'locale' => [
								            'firstDay' => 0,
								            'format' => 'YYYY-MM-DD',
								            'applyLabel'=> trans('backpack::crud.apply'),
								            'cancelLabel'=> trans('backpack::crud.cancel'),
								        ]]) }}"
						            data-init-function="bpFieldInitDateRangeElement"
						            type="text"
						            @include('crud::fields.inc.attributes')
						            >
						    </div>
							</div>
		        </div>
						<div class="row">
							<div class="col ">
                <div class="input-group">
                  <button type="submit" class="btn btn-success btn-sm">Get Data</button>
                </div>

							</div>
						</div>
					</form>
			</div>
		</div>
	</div>
	@foreach($thecharts as $aca)
	<div class="col-md-12 mb-1">
		<div class="card text-white">
			<div class="card-header">{{ $aca['title'] }}</div>
			<div class="card-body">
				{{-- <div class="card-wrapper"> --}}
					{!! $aca['chart']->container() !!}
				{{-- </div> --}}
			</div>
		</div>
	</div>
	@endforeach
</div>
@stop

@section('after_scripts')
	<script type="text/javascript" src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
	<script>
		function bpFieldInitDateRangeElement() {

			moment.locale('{{app()->getLocale()}}');

			var $visibleInput = $('#dp_obj');
			var $startInput = $('#dp_start_id');
			var $endInput = $('#dp_end_id');

			var $configuration = $visibleInput.data('bs-daterangepicker');
			// set the startDate and endDate to the defaults
			$configuration.startDate = moment($configuration.startDate);
			$configuration.endDate = moment($configuration.endDate);

			// if the hidden inputs have values
			// then startDate and endDate should be the values there
			if ($startInput.val() != '') {
					$configuration.startDate = moment($startInput.val());
			}
			if ($endInput.val() != '') {
					$configuration.endDate = moment($endInput.val());
			}

			$visibleInput.daterangepicker($configuration);

			var $picker = $visibleInput.data('daterangepicker');

			$visibleInput.on('keydown', function(e){
					e.preventDefault();
					return false;
			});

			$visibleInput.on('apply.daterangepicker hide.daterangepicker', function(e, picker){
					$startInput.val( picker.startDate.format('YYYY-MM-DD') );
					$endInput.val( picker.endDate.format('YYYY-MM-DD') );
					$('#dp_obj').submit();
			});
		}

		jQuery('document').ready(function($){
			bpFieldInitDateRangeElement();
		});

	</script>
	@foreach($thecharts as $aca)
  @if (is_array($aca['path']))
    @foreach ($aca['path'] as $string)
      <script src="{{ $string }}" charset="utf-8"></script>
    @endforeach
  @elseif (is_string($aca['path']))
    <script src="{{ $aca['path'] }}" charset="utf-8"></script>
  @endif

  {!! $aca['chart']->script() !!}
	@endforeach
@stop

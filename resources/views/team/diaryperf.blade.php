@extends(backpack_view('blank'))
@section('title', 'Subordinates Diary Performance')

@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet" />
@stop

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{{ $user->id_name }}</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="card mb-3">
		<div class="card-header">Subordinate's Diary Performances</div>
		<div class="card-body">
				<form action="{{ route('team.diaryperf')}}" method="get">
					<input type="hidden" name="uid" value="{{ $user->id }}" />
					<input id="dp_start_id" type="hidden" name="sdate" value="{{ $sdate }}">
			    <input id="dp_end_id" type="hidden" name="edate" value="{{ $edate }}">
			    <label>Date Range (Longer duration will cause the page to load slower)</label>
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
			        	<div class="input-group-append">
				            <span class="input-group-text">
			                <button type="submit" class="btn btn-success btn-sm">Get Data</button>
			            </span>
			        </div>
			    </div>
				</form>
				<br />
				<hr /><br />

			<div class="table-responsive">
				<table id="dtabless" class="table table-striped table-hover " style="white-space: nowrap;">
					<thead>
	          <tr>
							@foreach ($header as $key => $value)
							<th scope="col">{{ $value }}</th>
							@endforeach
	          </tr>
	        </thead>
	        <tbody>
						@foreach ($tmember as $key => $value)
							<tr>
								<td>{{ $value['name'] }}</td>
								@foreach ($value['dfs'] as $df)
								<td @if($df->is_off_day == true) class="bg-secondary" @endif>{{ $df->performance }}</td>
								@endforeach
								<td>{{ $value['dc'] }}</td>
								<td>{{ $value['total_exp'] }}</td>
								<td>{{ $value['total_act'] }}</td>
								<td>{{ $value['avg_prod'] }}</td>
							</tr>
						@endforeach
	        </tbody>
				</table>
			</div>
		</div>
	</div>

@stop

@section('after_scripts')
	<script type="text/javascript" src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

	<script type="text/javascript" src="{{ asset('packages/datatables.net/js/jquery.dataTables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('packages/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.bootstrap4.min.js" type="text/javascript"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js" type="text/javascript"></script>
  <script src="//cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js" type="text/javascript"></script>

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
			});
		}

		jQuery('document').ready(function($){
			bpFieldInitDateRangeElement();
			$('#dtabless').DataTable({
        paging: true,
        dom: "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row mt-2 d-print-none '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>",
        buttons: [
            'csv', 'excel'
        ]
    	});
		});

	</script>
@stop

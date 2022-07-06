@extends(backpack_view('blank'))
@section('title', 'Involvement Percentage List')

@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet" />
@stop

@section('content')
	<div class="card mb-3">
		<div class="card-header">Involvement Fill Percentage</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="dtabless" class="table table-striped table-hover " style="white-space: nowrap;">
					<thead>
	          <tr>
							<th scope="col">Staff</th>
							<th scope="col">Division</th>
							<th scope="col">Percentage</th>
							<th scope="col">Band</th>
							<th scope="col">Current Job Category</th>
							<th scope="col">Preferred Job Category</th>
	          </tr>
	        </thead>
	        <tbody>
						@foreach ($tmember as $key => $value)
							<tr>
								<td><a href="{{ route('staff.detail', ['uid' => $value->id]) }}">{{ $value->id_name }}</a></td>
								<td>{{ $value->Unit->pporgunitdesc }}</td>
								<td>{{ $value->InvTotalPerc() }}</td>
								<td>{{ $value->job_grade }}</td>
								<td>{{ $value->GetUserInfo()->CurJobType->category ?? '' }}</td>
								<td>{{ $value->GetUserInfo()->PrefJobType->category ?? ''}}</td>
							</tr>
						@endforeach
	        </tbody>
				</table>
			</div>
		</div>
	</div>

@stop

@section('after_scripts')

	<script type="text/javascript" src="{{ asset('packages/datatables.net/js/jquery.dataTables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('packages/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.bootstrap4.min.js" type="text/javascript"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js" type="text/javascript"></script>
  <script src="//cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js" type="text/javascript"></script>

	<script>

		jQuery('document').ready(function($){
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

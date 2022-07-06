@extends(backpack_view('blank'))
@section('title', 'LDAP Finder')

@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet" />
@stop

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">LDAP Finder</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="card mb-3">
		<div class="card-body">
				<form action="{{ route('suppa.findldap')}}" method="get">
					<label>Cost Center</label>
					<div class="input-group">
						<input  type="text" name="costcenter" value="{{ $input }}" @include('crud::fields.inc.attributes') />
					</div>
					<div class="input-group-append">
						<span class="input-group-text">
							<button type="submit" class="btn btn-success btn-sm">Get Data</button>
						</span>
					</div>
				</form>

			<hr />
			<br />

			<div class="table-responsive">
				<table id="dtabless" class="table table-striped table-hover">
					<thead>
	          <tr>
	            <th scope="col">Staff No</th>
	            <th scope="col">Name</th>
	            <th scope="col">Report To</th>
							<th scope="col">PPORGUNIT</th>
							<th scope="col">Subunit</th>
							<th scope="col">Employee Type</th>
							<th scope="col">Email</th>
	          </tr>
	        </thead>
	        <tbody>
						@if($gotdata)
	          @foreach($data as $atask)
	          <tr>
	            <td>{{ $atask['STAFF_NO'] }}</td>
	            <td>{{ $atask['NAME'] }}</td>
	            <td>{{ $atask['SUPERIOR'] }}</td>
	            <td>{{ $atask['UNIT'] }}</td>
	            <td>{{ $atask['SUBUNIT'] }}</td>
	            <td>{{ $atask['EMPLOYEE_TYPE'] }}</td>
	            <td>{{ $atask['EMAIL'] }}</td>
	          </tr>
	          @endforeach
						@endif
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

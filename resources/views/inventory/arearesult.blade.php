@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Meeting Area Search Result</span>
        <small>Available area.</small>
        <small><a href="{{ backpack_url('userareabooking') }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> <span>Back to my bookings</span></a></small>
	  </h2>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body">
				<label>Search Parameters</label>
				<p class="card-text">
					Start Time: {{ $start_time }}
					<br />
					End Time : {{ $end_time }}
					@if(isset($sqldebug))
					<br />
					<code>{{ json_encode($sqldebug) }}</code>
					@endif
				</p>
			</div>
		</div>
	</div>
</div>
@stop

@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Agile Office - Workspace</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="row">
    <div class="col mb-3">
			<div class="card bg-dark">
				@include('crud::inc.grouped_errors')
				<div class="card-body p-1 text-center">
					<a class='nav-link  text-white' href='{{ route('inv.seat.checkinform') }}'><i class="las la-camera"></i> Click here to launch QR Scanner</a>
				</div>
			</div>


		</div>
		<div class="col-md-12 mb-3">
			<div class="card bg-light">
				<div class="card-header">Current Check-ins</div>
				<div class="card-body">
					<div class="row">
						@if(sizeof($curc) > 0)
							@if(sizeof($curc) > 1)
							<div class="col-md-6 col-lg-4 mb-2 p-1">
								<div class="card">
									<div class="card-body">
										Multiple active Check-ins
									</div>
									<div class="card-footer">
										<a href="{{ route('inv.seat.docheckoutall') }}"><button class="btn btn-warning">Checkout All <i class="las la-sign-out-alt"></i></button></a>
									</div>
								</div>
							</div>
							@endif
							@foreach($curc as $ac)
								<div class="col-md-6 col-lg-4 mb-2 p-1">
									<div class="card">
										<div class="card-body">
											{{ $ac->Seat ? $ac->Seat->label : 'Deleted Seat' }}
											@if($ac->event_attendance_id)
											<p class="card-text text-muted">{{ $ac->EventAttendance->name }}</p>
											@endif
										</div>
										<div class="card-footer">
											<a href="{{ route('inv.seat.docheckout', ['id' => $ac->id]) }}"><button class="btn btn-warning">Checkout <i class="las la-sign-out-alt"></i></button></a>
										</div>
									</div>
								</div>
							@endforeach
						@else
							<div class="col text-center">
								<p class="card-text">No active check-ins</p>
							</div>

						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12 mb-3">
			<div class="card bg-light">
				<div class="card-header">Upcoming Seat Reservations</div>
				<div class="card-body">
					<div class="row">
						@if(sizeof($resv) > 0)
							@foreach($resv as $ac)
								<div class="col-md-6 col-lg-4 mb-2 p-1">
									<div class="card">
										<div class="card-header">
											{{ $ac->Seat ? $ac->Seat->label : 'Deleted Seat' }}
										</div>
										<div class="card-body">
											<p class="card-text text-muted">{{ $ac->FloorSection->long_label }}</p>
											<p class="card-text text-monospace">
												From: {{ (new \Carbon\Carbon($ac->start_time))->toDayDateTimeString() }}<br />
												To &nbsp;: {{ (new \Carbon\Carbon($ac->end_time))->toDayDateTimeString() }}
											</p>
										</div>
									</div>
								</div>
							@endforeach
						@else
							<div class="col text-center">
								<p class="card-text">No Seat Reservation</p>
								<p class="card-text">Please make a seat reservation before checking-in to the seat</p>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@stop

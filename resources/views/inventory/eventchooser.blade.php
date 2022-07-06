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
    <div class="col-md-10 mb-3">
			<div class="card">
				@include('crud::inc.grouped_errors')
				<div class="card-body">
					<a class='nav-link' href='{{ route('inv.seat.checkinform') }}'><i class="nav-icon las la-stamp"></i></i> Workspace Check-in</a>
				</div>
			</div>


		</div>
		<div class="col-md-12 mb-3">
			<div class="card bg-light">
				<div class="card-header">Events for {{ $seat->label }}</div>
				<div class="card-body">
					<div class="row">
					@foreach($events as $ac)
						<div class="col-lg-6 mb-2">
							<div class="card">
								<div class="card-header">
									{{ $ac->event_name }}
								</div>
								<div class="card-body py-2">
									<p class="small my-0">
										Organizer: {{ $ac->organizer->name }}<br />
										Start: {{ $ac->start_time }}<br />
										End: {{ $ac->end_time }}s
									</p>
								</div>
								<div class="card-footer">
									<a href="{{ route('inv.event.docheckin', ['qr' => $ac->qr_code]) }}"><button class="btn btn-success">Check-in</button></a>
								</div>
							</div>
						</div>
					@endforeach
					<div class="col-lg-6 mb-2">
						<div class="card">
							<div class="card-header">
								No, I just want to sit here
							</div>
							<div class="card-body py-2">
								<p class="small my-0">
									I'm not here to attend any meeting event
								</p>
							</div>
							<div class="card-footer">
								<a href="{{ route('inv.seat.docheckin', ['qr' => $seat->qr_code, 'f' => true]) }}"><button class="btn btn-info">Check-in</button></a>
							</div>
						</div>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@stop

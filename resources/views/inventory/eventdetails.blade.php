@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Event details</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="row">
    <div class="col-md-12 mb-3">
			<div class="card bg-light">
				<div class="card-header">{{ $event->event_name }}</div>
				<div class="card-body">
					<p class="card-text">
						Organizer: {{ $event->Organizer->id_name }}<br />
						Start: {{ $event->start_time }}<br />
						End: {{ $event->end_time }}<br />
						Location: {{ $event->Meetingarea->long_label }}
					</p>
				</div>
			</div>


		</div>
		<div class="col-md-12 mb-3">
			<div class="card bg-light">
				<div class="card-header">Attendance</div>
				<div class="card-body p-1">
					<div class="table-responsive">
						<table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
							<thead>
								<tr>
									<th>Participant</th>
									<th>Division</th>
									<th>Email</th>
									@foreach($evinfo['head'] as $dhead)
										<th>{{ $dhead }}</th>
									@endforeach
								</tr>
							</thead>
	            <tbody>
								@foreach ($evinfo['user'] as $us)
									<tr>
										<td>{{ $us->id_name }}</td>
										<td>{{ $us->Unit->pporgunitdesc }}</td>
										<td>{{ $us->email }}</td>
										@foreach($us->attended as $att)
											<td>
												@if($att == true)
													<i class="las la-check"></i>
												@else
													<i class="las la-times"></i>
												@endif
											</td>
										@endforeach
									</tr>
								@endforeach
	            </tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@stop

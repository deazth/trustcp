@extends(backpack_view('blank'))

@section('title', 'User Home')

@section('content')
	<div class="row">
		<div class="col-md-12">
			<h4 class="m-3">Welcome, {{ ucwords(strtolower(backpack_user()->name)) }}</h4>
</div>
<div class="col-md-12">
	<div class="card">
		<div class="card-header font-weight-bold">Agile Office</div>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-6 col-md-6 mb-1 col-lg-4 col-xl-3">
					<a href="{{ route('inv.seat.checkinform')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/2030.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Workspace Check-In</div>
							</div>
						</div>

					</a>
				</div>
				<div class="col-sm-6 col-md-6 mb-1 col-lg-4 col-xl-3">
					<a href="{{ route('userseatbook.finder')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/calendar-planner.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Seat Reservation</div>
							</div>
						</div>
					</a>
				</div>
				{{-- <div class="col-sm-6 col-md-6 mb-1 col-lg-4 col-xl-3">
					<a href="{{ route('userareabooking.finder')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/meeting_room_2.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Meeting Area Reservation</div>
							</div>
						</div>
					</a>
				</div> --}}
				<div class="col-sm-6 col-md-6 mb-1 col-lg-4 col-xl-3">
					<a href="{{ route('locationhistory.checkinloc')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/business-people-using-maps-phones.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Location Check-In</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-md-12 col-lg-6">
	<div class="card">
		<div class="card-header font-weight-bold">Productivity</div>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-6 col-md-6 mb-1">
					<a href="{{ route('gwdactivity.create')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/closeup-hands-writing-diary.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Diary Update</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-sm-6 col-md-6 mb-1">
					<a href="{{ route('personalskillset.create')}}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/hands-working-digital-device-network-graphic-overlay.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Skillset Declaration</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-md-12 col-lg-6">
	<div class="card">
		<div class="card-header font-weight-bold">Team Monitoring</div>
		<div class="card-body">
			<div class="row">
				<div class="col-sm-6 col-md-6 mb-1">
					<a href="{{ route('team.diaryperf') }}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/quality-assurance-clipboard-icon.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Diary Performance</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-sm-6 col-md-6 mb-1">
					<a href="{{ route('team.lastloc') }}">
						<div class="card m-1 border-0 shadow-none">
							<img class="card-img-top" src="/tmpictstock/magnifying-glass-map-close-up.png" alt="scan qr">
							<div class="card-body p-0">
								<div class="card-text text-center text-info">Whereabout</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

</div>

@stop

@extends(backpack_view('blank'))

@section('title', 'Workspace QR Result')

@section('header')
<section class="container-fluid">
	<h2>
		<span class="text-capitalize">QR Scan Result</span>
		<small>Seat Status.</small>
		<small><a href="{{ route('inv.landing') }}" class="d-print-none font-sm">
				<i
					class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i>
				<span>Back</span></a>
		</small>
	</h2>
</section>
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">QR Info</div>
			<div class="card-body">
				<div class="row">
					<div class="col-8">
						<p class="card-text">
							QR Label: {{ $label }} <br />
							QR Type: {{ $type }} <br />
							Status:
							@if($status == 'Available')
							<span class="text-success font-weight-bold">Available</span>
							@else
							<span class="text-danger font-weight-bold">{{ $status }}</span>
							@endif
							<br />
							Location: {{ $location }}
						</p>
					</div>
					<div class="col-2">
						@if($status == 'Available' && $type != 'event')
						<form method="post" action="{{ route('inv.seat.realdocheckin') }}">
							@csrf

							<input type="hidden" name="sid" value="{{ $id }}" />
							<input type="hidden" name="lat" value="0" class="inlat" />
							<input type="hidden" name="long" value="0" class="inlong" />
							<button class="btn btn-primary cekinbtn btn-block"><i class="las la-sign-in-alt"></i>
								Check-in</button>
						</form>
						@endif

						<form action="{{env('FIXIT_URL')}}" method="get" target="_blank" style="display: inline;">
						<input type="hidden" name="path" value="pwa" />	
						<input type="hidden" name="ws" value="{{ $id }}" />
							<button class="btn btn-primary btn-block cekinbtn mt-2" ><i class="las la-sign-in-alt"></i>
								FixIT</button>
						</form>





					</div>
				</div>
			</div>
			<div class="card-footer" id="alertbox">
				Location detection not supported. Please use different browser or mobile app instead.
			</div>
		</div>
	</div>
	@if(sizeof($extra) > 0)
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">Events scheduled here</div>
			<div class="card-body">
				<div class="row">
					@foreach($extra as $ex)
					<div class="col-lg-6">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-9">
										<p class="card-text">
											Event: {{ $ex['name'] }} <br />
											Organizer: {{ $ex['org'] }} <br />
											Start: {{ $ex['startt'] }} <br />
											End: {{ $ex['endt'] }}
										</p>
									</div>
									<div class="col-3">
										@if($status == 'Available')
										<form method="post" action="{{ route('inv.seat.realdoevcheckin') }}">
											@csrf
											<input type="hidden" name="sid" value="{{ $ex['ev_id'] }}" />
											<input type="hidden" name="lat" value="0" class="inlat" />
											<input type="hidden" name="long" value="0" class="inlong" />
											<button class="btn btn-primary cekinbtn"><i class="las la-sign-in-alt"></i>
												Check-in</button>
										</form>
										@endif



									</div>
								</div>
							</div>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
	@endif
</div>
@stop

@section('after_scripts')
<script type="text/javascript">
	function getLocation() {
		$('.cekinbtn').hide();
		if (navigator.geolocation) {

			navigator.geolocation.getCurrentPosition(showPosition, showError);

		} else {
			alert('Location not supported');

			$('.inlat').val(0);
			$('.inlong').val(0);
		}
	}

	function showError(error) {
		switch (error.code) {
			case error.PERMISSION_DENIED:
				document.getElementById('alertbox').innerHTML = "Location access denied";
				break;
			case error.POSITION_UNAVAILABLE:
				document.getElementById('alertbox').innerHTML =
					"Location access unavailable. Tried using different browser";
				break;
			case error.TIMEOUT:
				document.getElementById('alertbox').innerHTML = "Location access not available - timed out";

				break;
			case error.UNKNOWN_ERROR:
				document.getElementById('alertbox').innerHTML = "Location access error: " + JSON.stringify(error);
				break;
		}

		$('.inlat').val(0);
		$('.inlong').val(0);
	}

	function showPosition(position) {
		var llat = position.coords.latitude.toFixed(5);
		var llong = position.coords.longitude.toFixed(5);
		$('.inlat').val(llat);
		$('.inlong').val(llong);
		document.getElementById('alertbox').classList.add('d-none');
		$('.cekinbtn').show();

		// document.getElementById('gmapbtn').href = "https://www.google.com/maps/search/?api=1&query=" + llat + "," + llong;

	}

	$(document).ready(function() {
		getLocation();
	});
</script>
@stop
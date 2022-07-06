@extends(backpack_view('blank'))

@section('title', 'Location Check-in')

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Check-in Location. Please access this page using mobile phone's web browser for better location detection.</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="row">
    <div class="col mb-1">
			<div class="card">
				<div class="card-body">
          <form method="POST" action="{{ route('locationhistory.docheckinloc', [], false) }}">
            @csrf
            <div class="form-group row">
                <label for="lat" class="col-md-4 col-form-label text-md-right">Latitude</label>
                <div class="col-md-6">
                  <input type="text" class="form-control" name="lat" id="lat" placeholder="Location is" maxlength="300" readonly/>
                </div>
            </div>
            <div class="form-group row">
                <label for="lon" class="col-md-4 col-form-label text-md-right">Longitude</label>
                <div class="col-md-6">
                  <input type="text" class="form-control" name="long" id="lon" placeholder="not enabled" maxlength="300" readonly/>
                </div>
            </div>
            <div class="form-group row">
                <label for="addr" class="col-md-4 col-form-label text-md-right">Address</label>
                <div class="col-md-6">
                  <textarea rows="3" class="form-control" id="addr" name="address" placeholder="pending coord" readonly></textarea>
                </div>
            </div>
            <div id="batens" class="form-group hidden row mb-0">
                <div class="col text-center">
                  @if(isset($user->curr_attendance))
                  <button type="submit" class="btn btn-success" name="action" value="updateloc" title="Tukar Tempat">Update Location</button>
                  <button type="submit" class="btn btn-warning" name="action" value="clockout" title="Keluar">Check-out Location</button>
                  @else
                  <button type="submit" class="btn btn-primary" name="action" value="clockin" title="Masuk">Check-in Location</button>
                  @endif
									<a target="_blank" id="gmapbtn" class="btn btn-info" href="#">View in map</a>
                </div>
            </div>
            <input type="hidden" name="staff_id" value="{{ $user->id }}" />
          </form>
				</div>
			</div>
		</div>
	</div>
@stop

@section('after_scripts')
  <script type="text/javascript">

  function getLocation() {
    if (navigator.geolocation) {

      navigator.geolocation.getCurrentPosition(showPosition, showError);

    } else {
      alert('Location not supported');

      document.getElementById('lat').value = "";
      document.getElementById('lon').value = "";
      document.getElementById('batens').classList.add('d-none');
    }
  }

  function showError(error) {
    switch(error.code) {
      case error.PERMISSION_DENIED:
        document.getElementById('lon').placeholder = "denied";
        break;
      case error.POSITION_UNAVAILABLE:
        document.getElementById('lon').placeholder = "unavailable. Tried using chrome?";
        break;
      case error.TIMEOUT:
        document.getElementById('lon').placeholder = "not available - timed out";
        break;
      case error.UNKNOWN_ERROR:
        document.getElementById('lon').placeholder = ".. error?";
        break;
    }

    document.getElementById('lat').value = "";
    document.getElementById('lon').value = "";
    document.getElementById('batens').classList.add('d-none');
  }

  function showPosition(position) {
		var llat = position.coords.latitude.toFixed(5);
		var llong = position.coords.longitude.toFixed(5);
    document.getElementById('lat').value = llat;
    document.getElementById('lon').value = llong;

		document.getElementById('gmapbtn').href = "https://www.google.com/maps/search/?api=1&query=" + llat + "," + llong;
    document.getElementById('addr').innerHTML = 'Getting address. Please wait';

    var search_url = "{{ route('wa.reversegeo') }}";

    $.ajax({
      url: search_url,
      data: {
        'lat' : position.coords.latitude,
        'lon' : position.coords.longitude
      },
      success: function(result) {
        document.getElementById('addr').innerHTML = result;
      },
      error: function(xhr){
        document.getElementById('addr').innerHTML = xhr.statusText;
        // alert("An error occured: " + xhr.status + " " + xhr.statusText);
      }
    });


  }

  $(document).ready(function() {
    getLocation();
  } );

  </script>
@stop

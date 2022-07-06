@extends(backpack_view('blank'))
@section('title', 'Subordinates Last Known Location')

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{{ $user->id_name }}</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="card mb-3">
		<div class="card-header">Subordinates Last Known Location</div>
		<div class="card-body">
			<p class="card-text">
				<form action="{{ route('team.lastloc')}}" method="get">
					<input type="hidden" name="uid" value="{{ $user->id }}" />
					Last Known location for date <input type="date" name="indate" value="{{ $indate }}" onchange="this.form.submit();" />
				</form>

			</p>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
	          <tr>
	            <th scope="col">Name</th>
	            <th scope="col">Time</th>
	            <th scope="col">Location</th>
							<th scope="col">Map</th>
	          </tr>
	        </thead>
	        <tbody>
	          @foreach($tmember as $atask)
	          <tr>
	            <td>{{ $atask['name'] }}</td>
	            <td>{{ $atask['time'] }}</td>
	            <td>{{ $atask['addr'] }}</td>
							<td>
								@if($atask['gotloc'])
								<a class="btn btn-sm btn-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ $atask['lat'] }},{{ $atask['long'] }}" title="View in Google Maps." target="_blank"><i class="las la-map-marked"></i> View in map</a>
								@endif
							</td>

	          </tr>
	          @endforeach
	        </tbody>
				</table>
			</div>
		</div>
	</div>

@stop

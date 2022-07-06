@extends(backpack_view('blank'))
@section('title', 'Sudo Jobs monitor')
@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Hey, Super Admin</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="card mb-3">
		<div class="card-header">Jobs In Queue</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
	          <tr>
	            <th scope="col">Queue</th>
	            <th scope="col">Attempts</th>
	            <th scope="col">Count</th>
	          </tr>
	        </thead>
	        <tbody>
	          @foreach($workcount as $atask)
	          <tr>
	            <td>{{ $atask->queue }}</td>
	            <td>{{ $atask->attempts }}</td>
	            <td>{{ $atask->work_count }}</td>
	          </tr>
	          @endforeach
	        </tbody>
				</table>

			</div>
		</div>
	</div>
	<div class="card mb-3">
		<div class="card-header">Jobs Currently Processing</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
	          <tr>
	            <th scope="col">Time</th>
	            <th scope="col">Payload</th>
	          </tr>
	        </thead>
	        <tbody>
	          @foreach($inprogress as $atask)
	          <tr>
	            <td>{{ \Carbon\Carbon::createFromTimestamp($atask->reserved_at)->toDateTimeString() }}</td>
	            <td>{{ $atask->payload }}</td>
	          </tr>
	          @endforeach
	        </tbody>
				</table>

			</div>
		</div>
	</div>
	<div class="card mb-3">
		<div class="card-header">Manual Queue Job</div>
		<div class="card-body">
			<form method="post" action="{{ route('suppa.runjob')}}">
				@csrf
				<button name="jobtype" type="submit" class="btn btn-primary" value="SapLeaveManager">SapLeaveManager</button>
				<button name="jobtype" type="submit" class="btn btn-primary" value="SapProfileLoader">SapProfileLoader</button>
			</form>
		</div>
	</div>

@stop

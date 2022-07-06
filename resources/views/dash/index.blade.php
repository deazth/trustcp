@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">trUSt Statistics</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="card">
		<div class="card-header">User Stats</div>
		<div class="card-body">
			<div class="row">
		    <div class="col-md-4 mb-1">
		      <a href="{{ route('userstat.monthlyuserstat')}}">
		  			<div class="card bg-primary">
		  				<div class="card-body text-center">
		            Monthly Usage
		  				</div>
		  			</div>
		      </a>
				</div>
				<div class="col-md-4 mb-1">
		      <a href="{{ route('userstat.dailyuserstat')}}">
		  			<div class="card bg-primary">
		  				<div class="card-body text-center">
		            Daily Usage
		  				</div>
		  			</div>
		      </a>
				</div>
			</div>
		</div>
	</div>

	{{-- <div class="card">
		<div class="card-header">Meeting area</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 mb-1">
					<a href="{{ route('sbdash.rpt_ao_area_daily')}}">
						<div class="card bg-primary">
							<div class="card-body text-center">
								Daily Utilization
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-4 mb-1">
					<a href="{{ route('sbdash.rpt_ao_area_monthly')}}">
						<div class="card bg-primary">
							<div class="card-body text-center">
								Monthly Utilization
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div> --}}

@stop

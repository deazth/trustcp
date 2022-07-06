@extends(backpack_view('blank'))

@section('header')
<section class="container-fluid">
	<h2>
		<span class="text-capitalize">Agile Office - Seat Monthly Utilization</span>
	</h2>
</section>
@endsection

@section('content')
<div class="row">
	<div class="col mb-1">
		<div class="card ">
			<form method="get" action="{{ route($currenturl) }}">
				<div class="row m-2">
					<label class="col-md-3 col-lg-3 col-form-label text-md-right">Select location</label>
					<div class="col-md-9 col-lg-4">
						<select class="form-control" name="bid" id="arealist" onchange="this.form.submit();">
							@foreach($bl as $ar)
							@if($gotdata == true)
							<option value="{{ $ar->id }}" @if($ar->id == $selb) selected @endif >{{ $ar->long_label }}</option>
							@else
							<option value="{{ $ar->id }}">{{ $ar->long_label }}</option>
							@endif

							@endforeach
						</select>
					</div>
					<label class="col-md-3 col-lg-2 col-form-label text-md-right">Month</label>
					<div class="col-md-3 col-lg-3">
						<input class="form-control" name="indate" type="month" onchange="this.form.submit();" max="{{ $maxdate }}" value="{{ $indate }}" />
					</div>
				</div>
			</form>
		</div>
	</div>

	@if($gotdata == true)
	@foreach($thecharts as $aca)
	<div class="col-md-12 mb-1">
		<div class="card text-white">
			<div class="card-header">{{ $aca['title'] }}</div>
			<div class="card-body">
				{{-- <div class="card-wrapper"> --}}
				{!! $aca['chart']->container() !!}
				{{-- </div> --}}
			</div>
		</div>
	</div>
	@endforeach
	@endif
</div>
@stop

@push('after_scripts')
@foreach($thecharts as $aca)
@if (is_array($aca['path']))
@foreach ($aca['path'] as $string)
<script src="{{ $string }}" charset="utf-8"></script>
@endforeach
@elseif (is_string($aca['path']))
<script src="{{ $aca['path'] }}" charset="utf-8"></script>
@endif

{!! $aca['chart']->script() !!}
@endforeach

<script>


	 function buildMonthTooltip (params) {
		 var rt = params[0]['name'] ;
		 rt = rt + "<br/>";
		 rt = rt + params[0]['seriesName'];
		 rt = rt + ": ";
		 rt = rt + params[0].data['value1'];

		 return rt;
      

	 
    }

//	var currencyLabels =  "s";

</script>

@endpush
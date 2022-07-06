@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Agile Office - Seat Utilization</span>
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
	          <div class="col-md-9 col-lg-5">
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
						<label class="col-md-3 col-lg-1 col-form-label text-md-right">Date</label>
						<div class="col-md-3 col-lg-3">
							<input class="form-control" name="indate" type="date" onchange="this.form.submit();" max="{{ $maxdate }}" value="{{ $indate }}"/>
						</div>
	        </div>
	      </form>
	    </div>
		</div>

		@if($gotdata == true)

		@if(sizeof($summ) > 0)
		<div class="col-md-12 mb-1">
			<div class="card text-white">
				<div class="card-header">Overview: {{ $build->GetLabel() }}</div>
				<div class="card-body p-1">
          <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
						<thead>
							<tr>
								<th>{{ $summh1 }}</th>
								<th>Total Seat</th>
								<th>Peak Occupancy</th>
								<th>Average Util</th>
							</tr>
						</thead>
						<tbody>
							@foreach($summ as $af)
							<tr>
								<td><a href="{{route($nexturl, ['bid' => $af['fc_id'], 'indate' => $indate ])}}">{{ $af['fc_name'] }}</a></td>
								<td>{{ $af['total_seat'] }}</td>
								<td>{{ $af['max_occupied_seat'] }}</td>
								<td>{{ $af['utilization'] }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
		@endif
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

@endpush

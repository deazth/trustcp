@extends(backpack_view('blank'))

@section('title', 'Involvement Stats')

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">GITD Involvement Stats</span>
	  </h2>
	</section>
@endsection

@section('content')
	<div class="row">
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

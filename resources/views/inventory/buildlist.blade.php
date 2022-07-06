@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2><span class="text-capitalize">Current Workspace Availability</span></h2>
	</section>
@endsection

@section('content')
  <div class="col-md-12 mb-1">
    <div class="card text-white">
      <div class="card-header">{{ $aca['title'] }}</div>
      <div class="card-body">
          {!! $aca['chart']->container() !!}
      </div>
    </div>
  </div>
@stop

@push('after_scripts')
  @if (is_array($aca['path']))
    @foreach ($aca['path'] as $string)
      <script src="{{ $string }}" charset="utf-8"></script>
    @endforeach
  @elseif (is_string($aca['path']))
    <script src="{{ $aca['path'] }}" charset="utf-8"></script>
  @endif

  {!! $aca['chart']->script() !!}

<script type="text/javascript">
	function goToNext(params){
		let itemid = window.itemlist.find(x => x.name == params.value).id;
		// alert(itemid);
		let baseurl = "{{ route('inventory.seat.showbuilding') }}";

		// alert(baseurl + '/' + '{{ $nexttype }}' + '/' + itemid);
		window.location.href = baseurl + '/' + '{{ $nexttype }}' + '/' + itemid;
	}
</script>

@endpush

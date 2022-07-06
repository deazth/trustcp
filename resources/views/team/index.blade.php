@extends(backpack_view('blank'))

@section('title', 'Subordinates')

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{{ $user->name }} - Subordinates</span>
	  </h2>
	</section>
@endsection

@section('content')
		<div class="card">
			<div class="card-body">
				<div class="row">
            @foreach($user->Subordinates as $asub)
            <div class="col-md-4 col-sm-6">
            @if($asub['status'] != 0)
            <div class="card mb-3 text-center">

              <div class="card-body">
                <div class="row">
                  @if(substr($asub->staff_no, 0, 1) == 'X')
                  <div class="col p-1">
                  @else
                  <div class="col-3 p-1">
                    <img class="card-img"  style="border: 1px solid #000; max-width:64px; max-height:64px;" src="{{ route('staff.image', ['staff_no' => $asub['staff_no']]) }}" alt="gambo staff">
                  </div>
                  <div class="col-9 p-1">
                  @endif
                    <a href="{{ route('staff.detail', ['uid' => $asub->id]) }}">
                      <h5 class="card-title">{{ $asub['staff_no'] }}</h5>
                    </a>
                    <p class="card-text">{{ $asub['name'] }}</p>
                  </div>
                </div>
                @if(substr($asub->staff_no, 0, 1) == 'X')
                <div class="row">
                  <div class="col text-left">
                    <pre class="mb-0">Div : {{ $asub->unit }} <br />Unit: {{ $asub->subunit }}</pre>
                  </div>

                </div>
                @endif
              </div>
            </div>
            @else
            <div class="card mb-3 text-center text-white bg-secondary">
              <div class="card-body">
                <div class="row">
                  <div class="col-3 p-1">
                    <img class="card-img"  style="border: 1px solid #000; max-width:64px; max-height:64px;" src="{{ route('staff.image', ['staff_no' => $asub['staff_no']]) }}" alt="gambo staff">
                  </div>
                  <div class="col-9 p-1">
                    <h5 class="card-title">{{ $asub['staff_no'] }}</h5>
                    <p class="card-text">{{ $asub['name'] }}</p>
                  </div>
                </div>
              </div>
            </div>
            @endif
            </div>
            @endforeach
          </div>
        </div>
			</div>
		</div>

@stop

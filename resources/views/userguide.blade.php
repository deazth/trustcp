@extends(backpack_view('blank'))

@section('content')
  <h4 class="m-3">User Guides</h4>
  <div class="card-columns">
  @foreach ($guides as $key => $value)
    <a href="{{ route('uguide.download', ['id' => $value->id])}}" target="_blank">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">{{ $value->title }}</h5>
        <p class="card-text">{!! nl2br($value->desc) !!}</p>
        <p class="card-text"><small class="text-muted">Last updated: {{ $value->updated_at }}</small></p>
      </div>
    </div></a>
  @endforeach
  </div>

@stop

@extends(backpack_view('blank'))

@section('content')

  <div class="visible-print border text-center">
    <b>Scan to check in</b><br />
    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->merge('/public/img/trust-stayhome.png')->size(300)
      ->margin(1)->errorCorrection('H')->generate('trUSt : sample')) !!}">
    <p>Meja Something</p>
  </div>
@stop

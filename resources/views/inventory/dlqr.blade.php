@extends(backpack_view('blank'))

@section('content')

  <div class="visible-print text-center">
    {{-- <b>Scan to check in</b><br /> --}}
    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->merge('/public/img/trust_qr.png')->size(600)
      ->margin(1)->errorCorrection('H')->generate("https://trustbp.tm.com.my/v2/download")) !!}">
    <p class="my-1"><b>Download for iOS / Android</b></p>
    {{-- <p style="line-height: 1em">{{ $obj->floor_section ? $obj->floor_section->long_label : 'For online participant' }}</p> --}}
  </div>
@stop

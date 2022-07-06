@extends(backpack_view('layouts.plain'))
@section('content')
  <div class="d-print-none">
    <form method="get" action="{{ route('floorsection.getAllQr', ['fcid' => $build_id], false)}}">

      <div class="form-group row">
        <label for="width" class="col-md-3 col-form-label text-md-right">count per row</label>
        <div class="col-md-2">
            <input id="width" value="{{ $colcount }}" type="number" name="colcount" required>
        </div>
          <label for="width" class="col-md-3 col-form-label text-md-right">Size (px)</label>
          <div class="col-md-4">
              <input id="width" value="{{ $width }}" type="number" name="width" required>
          </div>
      </div>
      <div class="form-group row">
        <div class="col-md-4">

        </div>
        <div class="form-check col-md-6">
          <input class="form-check-input" type="checkbox" value="1" id="flexCheckDefault" name="inimg" @if($inimg) checked @endif />
          <label class="form-check-label" for="flexCheckDefault">Include Logo</label>

          <button type="submit" class="btn btn-primary">Regen</button>
        </div>
      </div>
    </form>
  </div>

  <!-- <div class="d-flex flex-wrap">  style="display:block" style="page-break-inside:avoid;page-break-before:always;"-->
  @php($iter = 0)
  <div class="row flex-nowrap">

  @foreach($seats as $seat)
    @if($iter % $colcount == 0)
    </div>
    <div class="row flex-nowrap" style="page-break-inside:avoid;page-break-before:auto;" >
    @endif
    <div class="visible-print border text-center" >
      {{-- style="float:left; break-inside:avoid" --}}
      <b>Scan to check in</b><br />
      @if($inimg)
      <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->merge('/public/img/trust_qr.png')->size($width)
        ->margin(1)->errorCorrection('H')->generate($seat->seat_url)) !!}">
      @else
      {!! QrCode::size($width)->margin(1)->generate($seat->seat_url); !!}
      @endif
      <p><b>{{ $seat['label'] }}</b></p>
      <p style="line-height: 1em">{{ $fc_label }}</p>
    </div>
    @php($iter++)
  @endforeach
  </div>
@endsection

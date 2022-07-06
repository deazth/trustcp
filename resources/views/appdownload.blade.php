@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
  @if(isset($alert))
  <div class="alert alert-success" role="alert">{{ $alert }}</div>
  @endif
    <div class="row justify-content-center">
      <div class="col">
        <h4>trUSt Mobile App Download Page</h4>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="card">
          <div class="card-header">
            Download iOS Installer
          </div>
          <div class="card-body">
            <div class="form-group row mb-0">
                <div class="col">
                  @if($ipa)
                    <a href="itms-services://?action=download-manifest&amp;url={{ route('app.ios.plist') }}"><button id="ios_dl" type="button" class="btn btn-info">Click in iOS device to download</button></a> Or<br />
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->merge('/public/img/trust_qr.png')->size(300)
                      ->margin(1)->errorCorrection('H')->generate("itms-services://?action=download-manifest&url=" . route('app.ios.plist'))) !!}">
                    <p class="my-1"><b>Scan this using iOS device</b></p>

                  @else
                    <button id="ios_dl" type="button" class="btn btn-secondary" disabled>Not Available</button>
                  @endif

                </div>
            </div>
            <div class="row mt-3">
              <div class="col"><div class="card-text">
                Steps to do when you get the error message as below
              </div></div>
            </div>
            <div class="row">
              <div class="col">
                <img class="card-img-top" src="/img/ios_err_01.png" alt="scan qr">
              </div>
              <div class="col">
                <img class="card-img-top" src="/img/ios_err_02.png" alt="scan qr">
              </div>
              <div class="col">
                <img class="card-img-top" src="/img/ios_err_03.png" alt="scan qr">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="card">
          <div class="card-header">
            Download for Android
          </div>
          <div class="card-body">
            <div class="form-group row mb-0">
                <div class="col">
                  <a href="https://play.google.com/store/apps/details?id=com.tm.trUSt" target="_blank" rel="external"><button type="button" class="btn btn-dark"><i class="lab la-google-play"></i> Open in PlayStore</button></a>
                  @if($apk)
                    <a href="{{ route('app.down', ['type' => 'apk'], false) }}"><button id="and_dl" type="button" class="btn btn-success">Download APK</button></a>
                  {{-- @else
                    <button id="and_dl"  type="button" class="btn btn-secondary" disabled>Not Available</button> --}}
                  @endif
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @if( backpack_user()->hasPermissionTo('super-admin') )
    <div class="row">
      <div class="col">
        <div class="card">
          <div class="card-header">
            Admin
          </div>
          <div class="card-body">
            <h5 class="card-title">Upload new IPA</h5>
            <form method="POST" action="{{ route('app.up', [], false) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="type" value="ipa"  />
              <div class="form-group row">
                  <label for="bundle_id" class="col-md-4 col-form-label text-md-right">bundle-identifier</label>
                  <div class="col-md-6">
                      <input id="bundle_id" type="text" class="form-control" value="com.tm.trUSt" name="bundle_id" required />
                  </div>
              </div>
              <div class="form-group row">
                  <label for="bundle_version" class="col-md-4 col-form-label text-md-right">bundle-version</label>
                  <div class="col-md-6">
                      <input id="bundle_version" type="text" class="form-control" name="bundle_version" required autofocus />
                  </div>
              </div>
              <div class="form-group row">
                  <label for="ipafile" class="col-md-4 col-form-label text-md-right">IPA file</label>
                  <div class="col-md-6">
                      <input id="ipafile" type="file" class="form-control" name="inputfile" required >
                  </div>
              </div>
              <div class="form-group row justify-content-center">
                  <button type="submit" class="btn btn-primary">Upload IPA</button>
              </div>
            </form>
            @if($ipa)
            <div class=" mb-3">
              <a href="{{ route('app.ios') }}">{{ route('app.ios') }}</a><br />
              <a href="{{ route('app.del', ['type' => 'ipa'], false) }}"><button type="button" class="btn btn-danger">Delete IPA</button></a>
            </div>
            @endif
            {{-- <form method="POST" action="{{ route('app.up', [], false) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="type" value="plist"  />
              <div class="input-group mb-3">
                <div class="custom-file">
                  <input type="file" class="custom-file-label" name="inputfile" required >
                </div>
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary">Upload PLIST</button>
                </div>
              </div>
            </form> --}}
            <hr />

            <h5 class="card-title">Upload new APK</h5>

            <form method="POST" action="{{ route('app.up', [], false) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="type" value="apk"  />
              <div class="input-group mb-3">
                <div class="custom-file">
                  <input type="file" class="custom-file-label" name="inputfile" required >
                </div>
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary">Upload APK</button>
                </div>
              </div>
            </form>
            @if( $apk)
              <br />
            <a href="{{ route('app.del', ['type' => 'apk'], false) }}"><button type="button" class="btn btn-danger">Delete IPK</button></a>
            @endif
          </div>
        </div>
      </div>
    </div>

    @endif
</div>
@stop

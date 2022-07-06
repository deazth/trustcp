@extends(backpack_view('blank'))

@section('title', 'Workspace QR Scan')

@section('content')
  <div class="row">
    <div class="card col-md-10 mb-1">
      <div id="reader"></div>
    </div>
  </div>
@stop


@section('after_scripts')
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

<script type="text/javascript">
  $(document).ready(function() {
    // This method will trigger user permissions
    Html5Qrcode.getCameras().then(devices => {
      /**
       * devices would be an array of objects of type:
       * { id: "id", label: "label" }
       */
      if (devices && devices.length) {
        var cameraId = devices[0].id;
        // .. use this to start scanning.

        // Create instance of the object. The only argument is the "id" of HTML element created above.
        const html5QrCode = new Html5Qrcode("reader");

        html5QrCode.start(
          { facingMode: "environment" },     // retreived in the previous step.
          {
            fps: 5,    // sets the framerate to 10 frame per second
            qrbox: 250  // sets only 250 X 250 region of viewfinder to
                        // scannable, rest shaded.
          },
          qrCodeMessage => {
            // do something when code is read. For example:
            // console.log(`QR Code detected: ${qrCodeMessage}`);
            window.location.href = qrCodeMessage;
          },
          errorMessage => {
            // parse error, ideally ignore it. For example:
            console.log(`QR Code no longer in front of camera.`);
          })
        .catch(err => {
          // Start failed, handle it. For example,
          console.log(`Unable to start scanning, error: ${err}`);
        });
      } else {
        alert('No camera');
      }
    }).catch(err => {
      alert(JSON.stringify(err));
    });
  });
</script>
@stop

@extends(backpack_view('blank'))
@section('content')

@if (!isset ($jquery) || (isset($jquery) && $jquery == true))
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
@endif




@if($show_happy==1)
<script type="text/javascript">
  $(window).on('load', function() {
    $('#primaryModal').modal('show');
  });
</script>
@endif

<div class="row">
  <div class="col-md-9">






    <div class="animated fadeIn">


      <div class="card">

        <div class="card-body">

          <ul class="nav nav-tabs" id="myTab1" role="tablist">

            <li class="nav-item"><a class="nav-link active " id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">Profile</a></li>
            <li class="nav-item"><a class="nav-link " id="checkin-tab" data-toggle="tab" href="#checkin" role="tab" aria-controls="checkin" aria-selected="false">Check-Ins</a></li>
            <li class="nav-item"><a class="nav-link " id="mun-tab" data-toggle="tab" href="#mun" role="tab" aria-controls="mun" aria-selected="false">Mun</a></li>
            <li class="nav-item"><a class="nav-link " id="nana-tab" data-toggle="tab" href="#nana" role="tab" aria-controls="mun" aria-selected="false">Nana</a></li>
            <li class="nav-item"><a class="nav-link " id="devan-tab" data-toggle="tab" href="#devan" role="tab" aria-controls="mun" aria-selected="false">Devan</a></li>
          </ul>
          <div class="tab-content" id="myTab1Content">

            <div class="tab-pane fade active show" id="profile" role="tabpanel" aria-labelledby="profile-tab">
              <div class="row">

                <div class="col-8">
                  <pre class="mb-0">
                  Name     : {{ $user['name'] }}
                  Staffno  : {{ $user['staff_no'] }}
                  Division : {{ $user['unit'] }}
                  Unit     : {{ $user['subunit'] }}
                  Position : {{ $user['position'] }}
                  Email    : {{ $user['email'] }}
                  Mobile   : {{ $user['mobile_no'] }}
                  @if(isset($superior))
                  Report To : <a href="{{ route('staff', ['staff_id' => $superior->id], false) }}">{{ $superior->name }}</a>
                  @endif
                </pre>

                </div>
                <div class="col-3">
                  <div class="card-img" id="profile_img">

                  </div>
                </div>

              </div>
            </div>
            <div class="tab-pane" id="checkin" role="tabpanel" aria-labelledby="checkin-tab">

            </div>

            <div class="tab-pane" id="mun" role="tabpanel" aria-labelledby="mun-tab">

            </div>

            <div class="tab-pane" id="nana" role="tabpanel" aria-labelledby="nana-tab">

            </div>
            <div class="tab-pane" id="devan" role="tabpanel" aria-labelledby="devan-tab">
              @include('sample.devan'));

            </div>

          </div>
        </div><!-- card-body -->
      </div><!-- card -->
    </div><!-- animated -->
  </div>



  <div class="col-md-3">
    <div id="newsDiv"></div>

  </div>
</div>




<script>
  function doGet(url, params) {
    params = params || {};

    $.get(url, params, function(response) { // requesting url which in form
      $('#newsDiv').html(response); // getting response and pushing to element with id #response
    });
  }

  //doGet("{{route('news.carousel')}}");
</script>


<script>
  $("#checkin").load("{{route('dash.checkin')}}");
  $("#mun").load("{{route('sample.mun')}}");
  $("#nana").load("{{route('sample.nana')}}");
  $('.nav-tabs a').on('click', function() {
    window.location.hash = $(this).attr('href');
  })
  doGet("{{route('news.carousel')}}");
  var img = $("<img />").attr('src', " {{route('staff.image', ['staff_no' => $user['staff_no']]) }} ");
  img.attr('style', "border: 1px solid #000; max-width:120px; max-height:120px;");

  $("#checkin-tab").click(
    function() {
      $("#checkin").load("{{route('dash.checkin')}}");

    }

  );



  $("#profile_img").append(img);
</script>



@endsection


@section('after_scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>

<!-- /.modal-->
<div class="modal fade op09" style="top: 3em;" id="primaryModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">

        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">

        @include('smile.body')
      </div>

    </div>
    <!-- /.modal-content-->
  </div>
  <!-- /.modal-dialog-->
</div>
<!-- /.modal-->
@stop
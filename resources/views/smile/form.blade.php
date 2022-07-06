@extends(backpack_view('blank'))

@section('content')
<style>
.smily_card {
    margin: auto!important;
    width: 12rem!important;
    height: 15rem!important;
    border-radius: 2rem!important;
    -webkit-box-shadow: 0 1px 15px 1px rgba(69,65,78,.37)!important;
    box-shadow: 0 1px 15px 1px rgba(69,65,78,.37)!important;
}
.img-feel {
    width: 60%!important;
    -webkit-box-shadow: 0 5px 10px 2px rgba(18,19,18,.19)!important;
    box-shadow: 0 5px 10px 2px rgba(18,19,18,.19)!important;
    border-radius: 50%!important;
}

.v_happy {
background-color: #2ecc71!important;

}
.happy {
background-color: #00a6fb!important;
}
.neutral {
background-color: #DAA520!important;
}
.upset {
    background-color: #f81!important;
}
.v_upset {
    background-color: #ea2803!important;
}
.fv_happy {
color: #2ecc71!important;

}
.fhappy {
color: #00a6fb!important;
}
.fneutral {
color: #DAA520!important;
}
.fupset {
color: #f81!important;
}
.fv_upset {
color: #ea2803!important;
}
.card_text {
    font-size: 28px!important;
    font-weight: 500!important;
    width: 60%!important;
    margin: auto!important;
}
</style>

<div class="container">
  <div class="row ">
    <div class="col text-center"><br><br>
      <h2>What makes you feel <span class="f{{$ty->remark}}"><b>'{{$ty->type}}'</b></span>?</h2>
      <!-- <h5> <span style="color:#00000063; ">Please select one : </span></h5> -->
    </div>
    <br><br>
  </div>
  <form method="POST" action="{{ route('smile.submit', [], false) }}">
    @csrf

  <div class="row text-">
    <div class="col-lg-4 text-center ">
      <div class=" smily_card {{$ty->remark}}" >
        <div style="padding: 19px !important;">
          <img class="img-feel" src="/images/smile/{{$ty->remark}}.svg">
        </div>
        <div class="card_text" style="color: white !important">
          <h3>{{$ty->type}}</h3>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="row" style="margin-top: 15px;">
        <div class="col-md-4 text-center">
          <label for="inputkey"><h5> <span style="color:#00000063; ">Please select : </span></h5></label>
        </div>
        <div class="col-md-6 text-center">
          <select type="text" id="reason" name="reason" style="width: 100%" required autofocus>
            <option value="" hidden disabled selected></option>
            @foreach($reasons as $sebab)
            <option value="{{$sebab->id}}">{{$sebab->reason}}</option>
            @endforeach
          </select>
        </div>
      </div>
        <div class="row" style="margin-top: 10px;">
          <div class="col-lg-12 text-center">
            <textarea rows="6" class="form-control" id="remark" name="remark" maxlength="450" placeholder="Tell us more on your feeling and what can be done to make you happier?" required>{{ old('details') }}</textarea>
          </div>
          <div class="col-lg-12 text-center">
            <br>
            <input type="hidden" id="type" name="type" value="{{$ty->id}}">
            <button type="submit" class="btn btn-primary m-1">Submit</button>
            <a href="{{ route('smile', [], false) }}"><button type="button" class="btn btn-success m-1" >Back</button></a>
          </div>
        </div>
    </div>

  </div>
</form>
</div>
@endsection

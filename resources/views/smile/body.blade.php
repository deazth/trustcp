



<style>
.col20 {
    width: 20%;
}

/* .row-eq-height {
   display: -webkit-box;
   display: -webkit-flex;
   display: -ms-flexbox;
   display: flex;
   flex-wrap: wrap;
   padding-top:32px;
    } */
.smile_icon {
    border-radius: 15px !important;
}

.v_happy {
    background-color: #2ecc71 !important;
}

.happy {
    background-color: #00a6fb !important;
}

.neutral {
    background-color: #DAA520 !important;
}

.upset {
    background-color: #f81 !important;
}

.v_upset {
    background-color: #ea2803 !important;
}

.grow {
    transition: .2s;
}

.grow:hover {
    transform: scale(1.1);
}

.emoji_width {

    box-shadow: 0 5px 10px 2px rgba(18, 19, 18, .19) !important;
    border-radius: 50% !important;
    width: 60px !important;
    /* margin-top: 2rem!important; */
}

.minmax {
    /* min-width: 60px!important;
      max-width: 400px!important;*/
    min-width: 125px !important;
   
    max-height: 230px !important;
    border-radius: 15px !important;
    margin-bottom: 9px !important;
    margin-left: 10px !important;
    margin-right: 10px !important;
    border-style: none;
}

.martext {
    font-size: 20px !important;
    font-weight: 500 !important;
    display: block !important;
    margin-top: 12px !important;
    margin-left: 5px !important;
    margin-right: 5px !important;
}

/*
  .feel_text {
    font-size: 24px!important;
    font-weight: 500!important;
    display: block!important;
    margin-top: 12px!important;
    margin-left: 20px!important;
    margin-right: 20px!important;
  }*/
.img {
    vertical-align: middle;
    border-style: none;
}

.smile button {background-color:lightgray}
</style>









<div class="container">
@if(session()->has('alert'))
    <div class="alert alert-{{ session()->get('alert_class') }} alert-dismissible">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>{!! session()->get('alert') !!}</strong>
    </div>
    @endif
    <div class="row justify-content-center">

        <div class="text-center">

            <div class="col-md-12">


                <h2> HOW ARE YOU FEELING NOW? </h2>
            </div>
            <div>
                <h6> <span style="border-bottom: 1px black solid; color:#00000063; ">Today, {{ date('d F, H:i A') }}
                    </span></h6>
            </div>
            <br><br>
            <!-- <div class="row row-eq-height"> -->
            <div class="container">
                <div class="card-deck ">
                    <div class="card text-white v_happy grow minmax ">
                        <a href="{{ route('smile.form', ['type' => 1], false) }}" class="text-white">
                            <div class="card-body ">
                                <div class="img">
                                    <img class="emoji_width" src="/images/smile/v_happy.svg">
                                </div>
                                <div class="martext">
                                    Very Happy
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- <div class="card text-white happy mb-5  grow smile_icon"> -->
                    <div class="card text-white happy grow minmax ">
                        <a href="{{ route('smile.form', ['type' => 2], false) }}" class="text-white">
                            <div class="card-body ">
                                <div class="img-line">
                                    <img class="emoji_width" src="/images/smile/happy.svg">
                                </div>
                                <div class="martext">
                                    Happy
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="card text-white neutral grow minmax ">
                        <a href="{{ route('smile.form', ['type' => 3], false) }}" class="text-white">
                            <div class="card-body ">
                                <div class="img-line">
                                    <img class="emoji_width" src="/images/smile/neutral.svg">
                                </div>
                                <div class="martext">
                                    Neutral
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="card text-white upset grow minmax ">
                        <a href="{{ route('smile.form', ['type' => 4], false) }}" class="text-white">
                            <div class="card-body ">
                                <div class="img-line">
                                    <img class="emoji_width" src="/images/smile/upset.svg">
                                </div>
                                <div class="martext">
                                    Upset
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="card text-white v_upset grow minmax ">
                        <a href="{{ route('smile.form', ['type' => 5], false) }}" class="text-white">
                            <div class="card-body ">
                                <div class="img-line">
                                    <img class="emoji_width" src="/images/smile/v_upset.svg">
                                </div>
                                <div class="martext">
                                    <!-- <div class="feel_text"> -->
                                    Very Upset
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- <div class="row justify-content-center">
          <div class="col-lg-2 col-md-12" style="background-color:lavender;">
              <a href="{{ route('smile.form', [], false) }}">
                <div class="box box-solid">
                  <div class="box-body v_happy text-white grow mb-5">
                    <div class="media text-center">
                      <div class="media-body">
                        <img src="/images/smile/v_hpy.svg" class="media-object-center" style="width:60px">
                        <br><br>
                        <h4>Very Happy</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
          </div>
        </div> -->
            <!-- </div> -->
            <p></p>
            <div class="col-md4 m-3">
                <div class="btn-group smile" >
                    <button type="button button-primary" class="btn" style="border-radius: 10em 0px 0px 10em; cursor:default;pointer-events: none; ">
                        <span class="p-3 ">Powered By</span>
                    </button>
                    <button type="button" class="btn h-10 ">
                    <a href="https://era.tm.com.my" target="blank">
                    <!-- /images/smile/era.svg -->
                        <img src="/images/smile/era.png" width="45em" class="img img-responsive "></button>
                        </a>
                        <button type="button p-3" class="btn " style="cursor:default;pointer-events: none">
                        <img class="img-fluid" width="45em" src="/images/smile/floaticon.png">
                        </button>

                    <button type="button button-primary" class="btn p-3" style="border-radius: 0em 10em 10em 0em;">
                       
                    </button>


                </div>
            </div>


        </div>
    </div>
</div>

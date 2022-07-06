@extends(backpack_view('blank2'))

@section('content')
   
    <style>
    .helloKitty {
        background-image:url('/images/hell0k1tty/helloKitty.jpg');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: 50%; 
        background-blend-mode:color;
        background-color:rgba(249,249,249 ,0.9 )
        
    }

    .face
    {
        position: absolute;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        #background: #ffee00;
        background: #fefefe;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 2em;
      
    }
    .face::before
    {
        content: '';
        position: absolute;
        top: 65px;
        width: 50px;
        height: 25px;
        background: #ff0000;
        background: #fefefe;
        border-bottom-left-radius: 70px;
        border-bottom-right-radius: 70px;
        transition: 0.5s;
    }
    .face:hover::before
    {
        top: 65px;
        width: 50px;
        height: 10px;
        background: #b57700;
        background: #f0f0fe;
        border-bottom-left-radius: 0px;
        border-bottom-right-radius: 0px;
    }
    .eyes
    {
        position: relative;
        top: -10px;
        display: flex;
    }
    .eyes .eye
    {
        position: relative;
        width: 30px;
        height: 30px;
        display: block;
        background: #fff;
        margin: 0 5px;
        border-radius: 50%;
    }
    .eyes .eye::before
    {
        content: '';
        position: absolute;
        top: 50%;
        left: 18px;
        transform: translate(-50%,-50%);
        width: 18px;
        height: 18px;
        background: #f0f0fd;
        border-radius: 50%;
    }

    .eyes .eye:hover
    {

        background: #f0f0fd;

    }
</style>


<div class="row master">
    <div class="col-md-5 login login-x">


    </div>
    <div class="col-md-7  ">
        <div class="login-logo">
            <img src="/images/tmlogo-bw.png">
        </div>
        <div class="login-text helloKitty col-lg-11">
            
            <form action="{{ route('login.offline', [], false) }}" method="post">
                {{ csrf_field() }}
                <h1>Offline Login</h1>
                <br>
                <p>Your Staff ID</p>
                <div class="form-group has-feedback {{ $errors->has('username') ? 'has-error' : '' }}">
                    <input type="text" id="staffno" name="staff_no" class="form-control" value="{{ old('staff_no') }}"
                           placeholder="Eg: TM52025">
                    @if ($errors->has('staff_no'))
                        <span class="help-block">
                            <strong>{{ $errors->first('staff_no') }}</strong>
                        </span>
                    @endif
                </div>
                <div class='eye'></div>
                <div class='eye' style="margin-left: 85px !important"></div>
                <p>Your Password</p>
                <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
                <input type="password" name="password" class="form-control input-sm" placeholder="password">
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            <br class="d-none">
                <div class="login-flex">
                    <button type="submit" class="btn btn-primary btn-black">
                            Sign In
                    </button>
                    
     
                    
                </div>
                <div class="face">
                        <div class="eyes">
                            <div class="eye"></div>
                            <div class="eye"></div>
                        </div>
                    </div>
            </form>
            <br class="d-none"><br class="d-none">
            🤬 WARNING! THIS IS STRICTLY FOR DEVELOPER'S USE ONLY! 🤬
        </div>
    </div>
</div>

<input type="hidden" id="text" value="Warning! This is strictly for developer's use only!" />
<input type="hidden" id="rate" value="0.9" />
<input type="hidden" id="pitch" value="1" />
<script type="text/javascript">
    setTimeout(greetUser, 1000);
    
    function greetUser() {
    
    var message = new SpeechSynthesisUtterance($("#text").val());
    var voices = speechSynthesis.getVoices();
    
    speechSynthesis.speak(message);    
    
    // Hack around voices bug
    var interval = setInterval(function () {
        voices = speechSynthesis.getVoices();
        if (voices.length) clearInterval(interval); else return;
    
        for (var i = 0; i < voices.length; i++) {
            $("select").append("<option value=\"" + i + "\">" + voices[i].name + "</option>");
        }
    }, 10);
    
    }

document.addEventListener('mousemove', eyeball);
	function eyeball(){
		var eye = document.querySelectorAll('.eye');
		eye.forEach(function(eye){
			let x = (eye.getBoundingClientRect().left) + (eye.clientWidth / 2);
			let y = (eye.getBoundingClientRect().top) + (eye.clientHeight / 2);
			let radian = Math.atan2(event.pageX - x, event.pageY - y);
			let rot = (radian * (180 / Math.PI) * -1) + 90;
			eye.style.transform = "rotate("+ rot +"deg)";
		})
	}   

    </script>
@stop




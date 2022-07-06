@extends(backpack_view('layouts.plain'))

@section('header')
<style>
    body {
        background-image: url('/img/double-exposure-image-business-finance.jpeg');
        background-attachment: fixed;
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover;
        margin: 0;
        padding: 0;
    }

    .my {

        margin: auto auto;

        border-radius: 50% !important;
        box-shadow: 0 0 10px #000;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;


    }
</style>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-4">

        <div class="container  shadow p-3 mb-5 rounded" style="background-color: rgba(65, 65, 65, 0.366); border-radius: 4%!important;">

            <h3 class="text-center mb-4">


                <img src="/img/LogoTRUST.png" class="rounded" alt="{{ trans('backpack::base.login') }}" width="80px;"  style="margin-top: 3px;">


            </h3>
            <div class="card">
                <div class="card-body">
                    <form class="col-md-12 p-t-10" role="form" method="POST" action="{{ route('backpack.auth.login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="control-label" for="{{ $username }}">{{ config('backpack.base.authentication_column_name') }}</label>

                            <div>
                                <input type="text" class="form-control{{ $errors->has($username) ? ' is-invalid' : '' }}" name="{{ $username }}" value="{{ old($username) }}" id="{{ $username }}">

                                @if ($errors->has($username))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first($username) }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="password">{{ trans('backpack::base.password') }}</label>

                            <div>
                                <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" id="password">

                                @if ($errors->has('password'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> {{ trans('backpack::base.remember_me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-block btn-primary">
                                    {{ trans('backpack::base.login') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if (backpack_users_have_email() && config('backpack.base.setup_password_recovery_routes', true))
            <div class="text-center"><a href="{{ route('backpack.auth.password.reset') }}">{{ trans('backpack::base.forgot_your_password') }}</a></div>
            @endif
            @if (config('backpack.base.registration_open'))
            <div class="text-center"><a href="{{ route('backpack.auth.register') }}">{{ trans('backpack::base.register') }}</a></div>
            @endif

            <div class="text-center">
                <p style="margin-top: -20px; margin-bottom: 2px;"><b>Powered by</b></p>

            </div>
            <div class="text-center">
                <img src="/img/GITD.png" class="rounded" alt="..." width="75px;">
            </div>
        </div><!-- container-my-->



    </div>
</div>
@endsection
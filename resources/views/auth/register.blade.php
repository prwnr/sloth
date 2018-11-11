@extends('layouts.auth')

@section('content')
    <div>
        <h2 class="text-center">Create your account</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <div class="form-row">
                    <div class="col-md-6">
                        <input id="firstname" type="text" class="form-control{{ $errors->has('firstname') ? ' is-invalid' : '' }}"
                               name="firstname" value="{{ old('firstname') }}" required autofocus placeholder="{{ __('First name') }}">
                    </div>
                    <div class="col-md-6">
                        <input id="lastname" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                               name="lastname" value="{{ old('lastname') }}" required autofocus placeholder="{{ __('Last name') }}">
                    </div>

                    @if ($errors->has('firstname'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('firstname') }}</strong></span>
                    @endif
                    @if ($errors->has('lastname'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('lastname') }}</strong></span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <input id="team_name" type="text" class="form-control{{ $errors->has('team_name') ? ' is-invalid' : '' }}"
                       name="team_name" value="{{ old('team_name') }}" required placeholder="{{ __('Team name') }}">

                @if ($errors->has('team_name'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('team_name') }}</strong></span>
                @endif
            </div>

            <div class="form-group">
                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ old('email') }}" required placeholder="{{ __('E-mail address') }}">

                @if ($errors->has('email'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col-md-6">
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                               name="password" required placeholder="{{ __('Password') }}">

                        @if ($errors->has('password'))
                            <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="{{ __('Confirm password') }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block float-right">Register</button>
        </form>
        <div class="copy-text">
            <a class="d-block" href="{{ route('login') }}">{{ __('Already have account? Log in!') }}</a>
            <a class="d-block" href="{{ route('password.request') }}">{{ __('Forgot Password?') }}</a>
        </div>
    </div>
@endsection

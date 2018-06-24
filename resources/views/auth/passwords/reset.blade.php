@extends('layouts.auth')

@section('content')
    <div class="card card-register mx-auto mt-5">
        <div class="card-header text-center"><h4>{{ __('Reset Password') }}</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.request') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="email">{{ __('E-Mail Address') }}</label>
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ $email ?? old('email') }}" required autofocus placeholder="{{ __('E-Mail Address') }}">

                    @if ($errors->has('email'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           name="password" required placeholder="{{ __('Password') }}">

                    @if ($errors->has('password'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                    @endif

                </div>

                <div class="form-group">
                    <label for="password-confirm">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                           name="password_confirmation" required placeholder="{{ __('Confirm Password') }}">
                    @if ($errors->has('password_confirmation'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('password_confirmation') }}</strong></span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-block">{{ __('Reset Password') }}</button>
            </form>

            <div class="text-center">
                <a class="d-block small mt-3" href="{{ route('login') }}">{{ __('Login Page') }}</a>
            </div>
        </div>
    </div>

@endsection

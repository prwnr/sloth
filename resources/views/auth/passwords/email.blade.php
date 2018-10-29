@extends('layouts.auth')

@section('content')
    <div>
        <h2 class="text-center">Reset password</h2>
        <form method="POST" action="{{ route('password.email') }}">
            <div class="text-center mt-4 mb-3">
                <h4>{{ __('Forgot your password?') }}</h4>
                <p>{{ __('Enter your email address and we will send you instructions on how to reset your password.') }}</p>
            </div>

            @csrf
            <div class="form-group">
                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ old('email') }}" required placeholder="{{ __('E-Mail Address') }}">

                @if ($errors->has('email'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
            <button type="submit" class="btn btn-success btn-block">{{ __('Send Password Reset Link') }}</button>
        </form>
        <div class="copy-text">
            <div class="d-block">
                <a class="mt-3" href="{{ route('register') }}">{{ __('Dont have account yet?') }}</a>
            </div>
            <div class="d-block">
                <a class="d-block" href="{{ route('login') }}">{{ __('Already have account? Log in!') }}</a>
            </div>
        </div>
    </div>
@endsection

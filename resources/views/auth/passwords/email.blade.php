@extends('layouts.auth')

@section('content')
    <div class="card card-login mx-auto mt-5">
        <div class="card-header text-center"><h4>{{ __('Reset Password') }}</h4></div>
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <div class="text-center mt-4 mb-5">
                <h4>{{ __('Forgot your password?') }}</h4>
                <p>{{ __('Enter your email address and we will send you instructions on how to reset your password.') }}</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ old('email') }}" required placeholder="{{ __('E-Mail Address') }}">

                    @if ($errors->has('email'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary btn-block">{{ __('Send Password Reset Link') }}</button>
            </form>
            <div class="text-center">
                <a class="d-block small mt-3" href="{{ route('register') }}">{{ __('Register an Account') }}</a>
                <a class="d-block small" href="{{ route('login') }}">{{ __('Login Page') }}</a>
            </div>
        </div>
    </div>
@endsection

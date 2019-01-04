@extends('layouts.app')

@section('content')
    <div>
        <h2 class="text-center">Sign in!</h2>
        <form method="POST" class="login-form" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                       name="email" value="{{ old('email') }}" required autofocus placeholder="E-mail">
                @if ($errors->has('email'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
            <div class="form-group">
                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                       name="password" required placeholder="Password">
                @if ($errors->has('password'))
                    <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </div>

            <div class="form-check mb-3">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    {{ __('Remember Me') }}
                </label>
            </div>
            <button type="submit" class="btn btn-success btn-block">Login</button>
        </form>

        <div class="copy-text">
            <div class="d-block">
                <a class="mt-3" href="{{ route('register') }}">{{ __('Dont have account yet?') }}</a>
            </div>
            <div class="d-block">
                <a class="" href="{{ route('password.request') }}">{{ __('Forgot Password?') }}</a>
            </div>
        </div>
    </div>
@endsection

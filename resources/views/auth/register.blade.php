@extends('layouts.auth')

@section('content')
    <div class="card card-register mx-auto mt-5">
        <div class="card-header text-center"><h4>{{ __('Register') }}</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="firstname">{{ __('First name') }}</label>
                            <input id="firstname" type="text" class="form-control{{ $errors->has('firstname') ? ' is-invalid' : '' }}"
                                   name="firstname" value="{{ old('firstname') }}" required autofocus placeholder="{{ __('First name') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="lastname">{{ __('Last name') }}</label>
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
                    <label for="team_name">{{ __('Team name') }}</label>
                    <input id="team_name" type="text" class="form-control{{ $errors->has('team_name') ? ' is-invalid' : '' }}"
                           name="team_name" value="{{ old('team_name') }}" required placeholder="{{ __('Team name') }}">

                    @if ($errors->has('team_name'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('team_name') }}</strong></span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="email">{{ __('E-mail address') }}</label>
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ old('email') }}" required placeholder="{{ __('E-mail address') }}">

                    @if ($errors->has('email'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                </div>
                <div class="form-group">
                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="password">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   name="password" required placeholder="{{ __('Password') }}">

                            @if ($errors->has('password'))
                                <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="password-confirm">{{ __('Confirm password') }}</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="{{ __('Confirm password') }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">{{ __('Register') }}</button>
            </form>
            <div class="text-center">
                <a class="d-block small mt-3" href="{{ route('login') }}">{{ __('Login Page') }}</a>
                <a class="d-block small" href="{{ route('password.request') }}">{{ __('Forgot Password?') }}</a>
            </div>
        </div>
    </div>
@endsection

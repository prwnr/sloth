@extends('layouts.auth')

@section('content')
    <div class="card card-login mx-auto mt-5">
        <div class="card-header text-center">
            <h4>{{ __('Your first login!') }}</h4>
        </div>
        <div class="card-body">
            <p class="text-center">{{ __('This is your first login on this account.') }}</p>
            <p class="text-center">{{ __('Please change your password to more secure before you will move forward to Bison Tracker.') }}</p>
            <form method="POST" action="{{ route('password.change') }}">
                @csrf
                <div class="form-group">
                    <label for="password">{{ __('New Password') }}</label>
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           name="password" value="{{ old('password') }}" required autofocus placeholder="{{ __('New Password') }}">
                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                           name="password_confirmation" required placeholder="{{ __('Confirm password') }}">
                    @if ($errors->has('password_confirmation'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                        </span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-block">{{ __('Change') }}</button>
            </form>
        </div>
    </div>
@endsection

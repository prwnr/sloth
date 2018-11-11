@extends('layouts.auth')

@section('content')
    <div>
        <h2 class="text-center">Your first login!</h2>
        <form method="POST" action="{{ route('password.change') }}">
            @csrf
            <p class="text-center">{{ __('This is your first login on this account.') }}</p>
            <p class="text-center">{{ __('Please change your password to more secure before you will start your slothful work.') }}</p>

            <div class="form-group">
                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                       name="password" value="{{ old('password') }}" required autofocus placeholder="{{ __('New Password') }}">
                @if ($errors->has('password'))
                    <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                @endif
            </div>
            <div class="form-group">
                <input id="password_confirmation" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                       name="password_confirmation" required placeholder="{{ __('Confirm password') }}">
                @if ($errors->has('password_confirmation'))
                    <span class="invalid-feedback">
                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                        </span>
                @endif
            </div>

            <button type="submit" class="btn btn-success btn-block">{{ __('Change') }}</button>
        </form>
    </div>
@endsection

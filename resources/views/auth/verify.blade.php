@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }},

                    <form class="d-inline" method="POST" action="{{ url('/email/verification-notification') }}">
                        @csrf
                        @if(Auth::check())
                            <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                        @else
                            <div class="mt-3">
                                <input type="email" name="email" class="form-control form-control-sm d-inline-block w-auto" placeholder="Your email address" value="{{ old('email', $email ?? '') }}" required>
                                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('request another') }}</button>.
                            </div>
                        @endif
                    </form>

                    @if(!Auth::check())
                        <div class="mt-3">
                            <a href="{{ url('/login') }}">{{ __('Login to your account') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

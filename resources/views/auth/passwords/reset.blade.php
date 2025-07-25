@extends('layouts/basic')

@push('ff-top')
<link rel="stylesheet" href="{{ url(mix('css/build/ff.css')) }}">
@endpush

@section('login-logo')
@endsection

{{-- Page content --}}
@section('content')
    <div class="row login">
        <div class="col-lg-4 login__info">
            <div class="login__info__copy">
                <div class="copy-hero-ff">Streamlining Asset Management Across Phintraco Group</div>
                <div class="copyright-ff">Copyright © 2025 PhinVentory</div>
            </div>
        </div>

        <div class="col-lg-8 login__container">
            <div class="login__container__box">
                <div class="login__container__box__header">
                    <img src="/img/phinventory-logo.webp" alt="PhinVentory logo" height="130">
                    <h1 class="header-title">Reset Password PhinVentory</h1>
                </div>
                <div class="login__container__box__content">
                    <form role="form" method="POST" action="{{ url('/password/reset') }}">
                        {!! csrf_field() !!}
                        <div>
                            <div>
                                <!-- Notifications -->
                                @include('notifications')
                                
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                    <label class="label-style-ff" style="margin-bottom: 5px" for="username">
                                        {{ trans('admin/users/table.username') }}
                                    </label>
                                    <input type="text" class="form-control radius input-style-ff" id="username" name="username" value="{{ old('username') }}" placeholder="{{ trans('admin/users/table.username') }}" aria-label="username">
                                    {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                </div>
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label class="label-style-ff" style="margin-bottom: 5px" for="password">
                                        {{-- <x-icon type="password" /> --}}
                                        {{ trans('admin/users/table.password')  }}
                                    </label>
                                    <div class="form-control radius input-style-ff" style="display: flex; align-items: center; position: relative;">
                                        <input style="border: none; outline-style: none; flex-grow: 1;" placeholder="{{ trans('admin/users/table.password')  }}" name="password" aria-label="password" type="password" id="password">
                                        <div style="cursor: pointer; display: flex; align-items: center;">
                                            <svg id="eyeClose" width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5798 0.758855C17.8971 0.894827 18.044 1.26225 17.9081 1.57952L17.3336 1.33332C17.9081 1.57952 17.9081 1.57937 17.9081 1.57952L17.9075 1.58092L17.9066 1.58299L17.9039 1.58912L17.895 1.60919C17.8875 1.62597 17.8769 1.64948 17.863 1.67922C17.8353 1.73868 17.7947 1.82311 17.741 1.92845C17.6336 2.13899 17.4733 2.43389 17.2574 2.78037C16.9027 3.34957 16.394 4.0647 15.7186 4.77514L16.5255 5.58208C16.7696 5.82616 16.7696 6.22188 16.5255 6.46596C16.2815 6.71004 15.8857 6.71004 15.6417 6.46596L14.8078 5.63214C14.2581 6.09272 13.6277 6.52501 12.9128 6.88018L13.6908 8.07579C13.879 8.36511 13.7971 8.75226 13.5078 8.94052C13.2185 9.12877 12.8313 9.04684 12.6431 8.75752L11.7354 7.36261C11.086 7.5747 10.3828 7.71883 9.62526 7.7705V9.24999C9.62526 9.59517 9.34544 9.87499 9.00026 9.87499C8.65508 9.87499 8.37526 9.59517 8.37526 9.24999V7.7705C7.64107 7.72042 6.9579 7.5835 6.32518 7.38199L5.43012 8.75758C5.24186 9.0469 4.85471 9.12883 4.56539 8.94058C4.27606 8.75232 4.19413 8.36517 4.38239 8.07584L5.14273 6.90729C4.42241 6.55553 3.78684 6.12513 3.23234 5.6652L2.43153 6.46602C2.18745 6.71009 1.79172 6.71009 1.54764 6.46602C1.30357 6.22194 1.30357 5.82621 1.54764 5.58213L2.31745 4.81233C1.63032 4.09592 1.11303 3.37212 0.752633 2.79554C0.533528 2.44502 0.370821 2.14636 0.261894 1.93306C0.207393 1.82634 0.166245 1.74078 0.138134 1.68052C0.124075 1.65038 0.113267 1.62656 0.105671 1.60956L0.0966765 1.58923L0.0939741 1.58303L0.0930699 1.58093L0.0927294 1.58014C0.0926659 1.57999 0.092463 1.57952 0.666929 1.33332L0.0927294 1.58014C-0.0432429 1.26287 0.103461 0.894827 0.420729 0.758855C0.737737 0.622995 1.10482 0.769611 1.24106 1.08634C1.24101 1.08623 1.24111 1.08645 1.24106 1.08634L1.24185 1.08816L1.24691 1.09959C1.25183 1.11059 1.25982 1.12824 1.27093 1.15205C1.29315 1.19969 1.32779 1.27186 1.37513 1.36455C1.46987 1.55007 1.61501 1.81688 1.8126 2.13299C2.20893 2.76705 2.80997 3.58923 3.63 4.35131C4.35158 5.02191 5.23582 5.63962 6.29336 6.04649C7.08877 6.35251 7.98813 6.54165 9.00026 6.54165C10.035 6.54165 10.952 6.34396 11.7607 6.02567C12.8118 5.61192 13.69 4.99019 14.4059 4.31831C15.2138 3.55996 15.806 2.74595 16.1965 2.11932C16.3911 1.80691 16.5341 1.54354 16.6274 1.36054C16.6741 1.26912 16.7082 1.19796 16.7301 1.15103C16.741 1.12756 16.7489 1.11018 16.7537 1.09935L16.7587 1.08812L16.7591 1.08712C16.7591 1.0872 16.7592 1.08704 16.7591 1.08712M17.5798 0.758855C17.2626 0.622918 16.8952 0.770022 16.7591 1.08712L17.5798 0.758855ZM1.24106 1.08634C1.24101 1.08623 1.24111 1.08645 1.24106 1.08634V1.08634Z" fill="#5D6878"/>
                                            </svg>
                                            <svg style="display: none" id="eyeOpen" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00033 4.37501C7.27444 4.37501 5.87533 5.77412 5.87533 7.50001C5.87533 9.2259 7.27444 10.625 9.00033 10.625C10.7262 10.625 12.1253 9.2259 12.1253 7.50001C12.1253 5.77412 10.7262 4.37501 9.00033 4.37501ZM7.12533 7.50001C7.12533 6.46448 7.96479 5.62501 9.00033 5.62501C10.0359 5.62501 10.8753 6.46448 10.8753 7.50001C10.8753 8.53554 10.0359 9.37501 9.00033 9.37501C7.96479 9.37501 7.12533 8.53554 7.12533 7.50001Z" fill="#5D6878"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00033 0.208344C5.23855 0.208344 2.70474 2.46184 1.23413 4.3724L1.20761 4.40684C0.875024 4.83879 0.568709 5.23661 0.360898 5.70701C0.138363 6.21074 0.0419922 6.75975 0.0419922 7.50001C0.0419922 8.24027 0.138363 8.78928 0.360898 9.29301C0.56871 9.76341 0.875026 10.1612 1.20761 10.5932L1.23413 10.6276C2.70474 12.5382 5.23855 14.7917 9.00033 14.7917C12.7621 14.7917 15.2959 12.5382 16.7665 10.6276L16.793 10.5932C17.1256 10.1612 17.4319 9.76342 17.6398 9.29301C17.8623 8.78928 17.9587 8.24027 17.9587 7.50001C17.9587 6.75975 17.8623 6.21074 17.6398 5.70701C17.4319 5.2366 17.1256 4.83877 16.793 4.40682L16.7665 4.3724C15.2959 2.46184 12.7621 0.208344 9.00033 0.208344ZM2.22467 5.13484C3.58252 3.37078 5.79229 1.45834 9.00033 1.45834C12.2084 1.45834 14.4181 3.37078 15.776 5.13484C16.1415 5.60967 16.3555 5.89339 16.4964 6.21213C16.628 6.51002 16.7087 6.87412 16.7087 7.50001C16.7087 8.1259 16.628 8.49 16.4964 8.78789C16.3555 9.10663 16.1415 9.39035 15.776 9.86518C14.4181 11.6292 12.2084 13.5417 9.00033 13.5417C5.79229 13.5417 3.58252 11.6292 2.22467 9.86518C1.85919 9.39035 1.64511 9.10663 1.50429 8.78789C1.37269 8.49 1.29199 8.1259 1.29199 7.50001C1.29199 6.87412 1.37269 6.51002 1.50429 6.21213C1.6451 5.89339 1.85919 5.60967 2.22467 5.13484Z" fill="#5D6878"/>
                                            </svg>
                                        </div>
                                    </div>
                                    {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label class="label-style-ff" style="margin-bottom: 5px" for="password">
                                        {{-- <x-icon type="password" /> --}}
                                        {{ trans('admin/users/table.password_confirm')  }}
                                    </label>
                                    <div class="form-control radius input-style-ff" style="display: flex; align-items: center; position: relative;">
                                        <input style="border: none; outline-style: none; flex-grow: 1;" placeholder="{{ trans('admin/users/table.password_confirm')  }}" name="password_confirmation" aria-label="password_confirmation" type="password" id="passwordConfirm">
                                        <div style="cursor: pointer; display: flex; align-items: center;">
                                            <svg id="eyeCloseConfirm" width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.5798 0.758855C17.8971 0.894827 18.044 1.26225 17.9081 1.57952L17.3336 1.33332C17.9081 1.57952 17.9081 1.57937 17.9081 1.57952L17.9075 1.58092L17.9066 1.58299L17.9039 1.58912L17.895 1.60919C17.8875 1.62597 17.8769 1.64948 17.863 1.67922C17.8353 1.73868 17.7947 1.82311 17.741 1.92845C17.6336 2.13899 17.4733 2.43389 17.2574 2.78037C16.9027 3.34957 16.394 4.0647 15.7186 4.77514L16.5255 5.58208C16.7696 5.82616 16.7696 6.22188 16.5255 6.46596C16.2815 6.71004 15.8857 6.71004 15.6417 6.46596L14.8078 5.63214C14.2581 6.09272 13.6277 6.52501 12.9128 6.88018L13.6908 8.07579C13.879 8.36511 13.7971 8.75226 13.5078 8.94052C13.2185 9.12877 12.8313 9.04684 12.6431 8.75752L11.7354 7.36261C11.086 7.5747 10.3828 7.71883 9.62526 7.7705V9.24999C9.62526 9.59517 9.34544 9.87499 9.00026 9.87499C8.65508 9.87499 8.37526 9.59517 8.37526 9.24999V7.7705C7.64107 7.72042 6.9579 7.5835 6.32518 7.38199L5.43012 8.75758C5.24186 9.0469 4.85471 9.12883 4.56539 8.94058C4.27606 8.75232 4.19413 8.36517 4.38239 8.07584L5.14273 6.90729C4.42241 6.55553 3.78684 6.12513 3.23234 5.6652L2.43153 6.46602C2.18745 6.71009 1.79172 6.71009 1.54764 6.46602C1.30357 6.22194 1.30357 5.82621 1.54764 5.58213L2.31745 4.81233C1.63032 4.09592 1.11303 3.37212 0.752633 2.79554C0.533528 2.44502 0.370821 2.14636 0.261894 1.93306C0.207393 1.82634 0.166245 1.74078 0.138134 1.68052C0.124075 1.65038 0.113267 1.62656 0.105671 1.60956L0.0966765 1.58923L0.0939741 1.58303L0.0930699 1.58093L0.0927294 1.58014C0.0926659 1.57999 0.092463 1.57952 0.666929 1.33332L0.0927294 1.58014C-0.0432429 1.26287 0.103461 0.894827 0.420729 0.758855C0.737737 0.622995 1.10482 0.769611 1.24106 1.08634C1.24101 1.08623 1.24111 1.08645 1.24106 1.08634L1.24185 1.08816L1.24691 1.09959C1.25183 1.11059 1.25982 1.12824 1.27093 1.15205C1.29315 1.19969 1.32779 1.27186 1.37513 1.36455C1.46987 1.55007 1.61501 1.81688 1.8126 2.13299C2.20893 2.76705 2.80997 3.58923 3.63 4.35131C4.35158 5.02191 5.23582 5.63962 6.29336 6.04649C7.08877 6.35251 7.98813 6.54165 9.00026 6.54165C10.035 6.54165 10.952 6.34396 11.7607 6.02567C12.8118 5.61192 13.69 4.99019 14.4059 4.31831C15.2138 3.55996 15.806 2.74595 16.1965 2.11932C16.3911 1.80691 16.5341 1.54354 16.6274 1.36054C16.6741 1.26912 16.7082 1.19796 16.7301 1.15103C16.741 1.12756 16.7489 1.11018 16.7537 1.09935L16.7587 1.08812L16.7591 1.08712C16.7591 1.0872 16.7592 1.08704 16.7591 1.08712M17.5798 0.758855C17.2626 0.622918 16.8952 0.770022 16.7591 1.08712L17.5798 0.758855ZM1.24106 1.08634C1.24101 1.08623 1.24111 1.08645 1.24106 1.08634V1.08634Z" fill="#5D6878"/>
                                            </svg>
                                            <svg style="display: none" id="eyeOpenConfirm" width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00033 4.37501C7.27444 4.37501 5.87533 5.77412 5.87533 7.50001C5.87533 9.2259 7.27444 10.625 9.00033 10.625C10.7262 10.625 12.1253 9.2259 12.1253 7.50001C12.1253 5.77412 10.7262 4.37501 9.00033 4.37501ZM7.12533 7.50001C7.12533 6.46448 7.96479 5.62501 9.00033 5.62501C10.0359 5.62501 10.8753 6.46448 10.8753 7.50001C10.8753 8.53554 10.0359 9.37501 9.00033 9.37501C7.96479 9.37501 7.12533 8.53554 7.12533 7.50001Z" fill="#5D6878"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00033 0.208344C5.23855 0.208344 2.70474 2.46184 1.23413 4.3724L1.20761 4.40684C0.875024 4.83879 0.568709 5.23661 0.360898 5.70701C0.138363 6.21074 0.0419922 6.75975 0.0419922 7.50001C0.0419922 8.24027 0.138363 8.78928 0.360898 9.29301C0.56871 9.76341 0.875026 10.1612 1.20761 10.5932L1.23413 10.6276C2.70474 12.5382 5.23855 14.7917 9.00033 14.7917C12.7621 14.7917 15.2959 12.5382 16.7665 10.6276L16.793 10.5932C17.1256 10.1612 17.4319 9.76342 17.6398 9.29301C17.8623 8.78928 17.9587 8.24027 17.9587 7.50001C17.9587 6.75975 17.8623 6.21074 17.6398 5.70701C17.4319 5.2366 17.1256 4.83877 16.793 4.40682L16.7665 4.3724C15.2959 2.46184 12.7621 0.208344 9.00033 0.208344ZM2.22467 5.13484C3.58252 3.37078 5.79229 1.45834 9.00033 1.45834C12.2084 1.45834 14.4181 3.37078 15.776 5.13484C16.1415 5.60967 16.3555 5.89339 16.4964 6.21213C16.628 6.51002 16.7087 6.87412 16.7087 7.50001C16.7087 8.1259 16.628 8.49 16.4964 8.78789C16.3555 9.10663 16.1415 9.39035 15.776 9.86518C14.4181 11.6292 12.2084 13.5417 9.00033 13.5417C5.79229 13.5417 3.58252 11.6292 2.22467 9.86518C1.85919 9.39035 1.64511 9.10663 1.50429 8.78789C1.37269 8.49 1.29199 8.1259 1.29199 7.50001C1.29199 6.87412 1.37269 6.51002 1.50429 6.21213C1.6451 5.89339 1.85919 5.60967 2.22467 5.13484Z" fill="#5D6878"/>
                                            </svg>
                                        </div>
                                    </div>
                                    {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="login__container__box__footer" style="margin-top: 40px">
                            <button disabled class="btn btn-block btn-style-ff" type="submit" id="submit">
                                {{ trans('auth/general.reset_password')  }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        console.log('p');
        $(document).ready(function() {
            const $userInput = $('#username');
            const $passwordInput = $('#password');
            const $passwordConfirmInput = $('#passwordConfirm');
            const $submitBtn = $('#submit');

            const $eyeClose  = $('#eyeClose');
            const $eyeOpen   = $('#eyeOpen');
            const $eyeCloseConfirm  = $('#eyeCloseConfirm');
            const $eyeOpenConfirm  = $('#eyeOpenConfirm');

            function toggleSubmit() {
                const valid = $.trim($userInput.val()).length > 0 && $.trim($passwordInput.val()).length > 0 && $.trim($passwordConfirmInput.val()).length > 0;

                $submitBtn.prop('disabled', !valid);

                if (valid) {
                    $submitBtn.addClass('ff-bg-dark-grey');
                } else {
                    $submitBtn.removeClass('ff-bg-dark-grey');
                }
            }

            $userInput.on('input', toggleSubmit);
            $passwordInput.on('input', toggleSubmit);
            $passwordConfirmInput.on('input', toggleSubmit);

            $eyeClose.on('click', function () {
                $passwordInput.attr('type', 'text');
                $eyeClose.hide();
                $eyeOpen.show();
            });
            $eyeOpen.on('click', function () {
                $passwordInput.attr('type', 'password');
                $eyeOpen.hide();
                $eyeClose.show();
            });
            $eyeCloseConfirm.on('click', function () {
                $passwordConfirmInput.attr('type', 'text');
                $eyeCloseConfirm.hide();
                $eyeOpenConfirm.show();
            });
            $eyeOpenConfirm.on('click', function () {
                $passwordConfirmInput.attr('type', 'password');
                $eyeOpenConfirm.hide();
                $eyeCloseConfirm.show();
            });
        });
    </script>
@endpush

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
                    @if ($snipeSettings->custom_forgot_pass_url)
                    <!--  The admin settings specify an LDAP password reset URL to let's send them there -->
                    <div>
                        <div class="text-center box box-header">
                            <h3 class="box-title">
                                <a href="{{ $snipeSettings->custom_forgot_pass_url  }}" rel="noopener">
                                    {{ trans('auth/general.ldap_reset_password')  }}
                                </a>
                            </h3>
                        </div>
                    </div>
                    @else
                    <form class="form" role="form" method="POST" action="{{ url('/password/email') }}">
                        {!! csrf_field() !!}
                        <div>
                            <div class="alert ff-bg-dark-grey radius" style="color: white;">
                                <x-icon type="info-circle" />
                                {!! trans('auth/general.username_help_top') !!}
                            </div>
                            <div class="row">
                                <!-- Notifications -->
                                @include('notifications')
                                <div class="col-md-12 form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                    <label class="label-style-ff" style="margin-bottom: 5px" for="username">
                                        {{-- <x-icon type="user" /> --}}
                                        {{ trans('admin/users/table.username') }}
                                    </label>
                                    <input type="text" class="form-control radius input-style-ff" id="username" name="username" value="{{ old('username') }}" placeholder="{{ trans('admin/users/table.username') }}" aria-label="username">
                                    {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                </div>
                            </div>

                            <div>
                                <div class="remember-forget-ff">
                                    <div>
                                        <!-- show help text toggle -->
                                        <a href="#" id="show">
                                            <x-icon type="caret-right" />
                                            <span class="label-style-ff">{{ trans('general.show_help') }}</span>
                                        </a>
                                        <!-- hide help text toggle -->
                                        <a href="#" id="hide" style="display:none">
                                            <x-icon type="caret-up" />
                                            <span class="label-style-ff">{{ trans('general.hide_help') }}</span>
                                        </a>
                                    </div>
                                    <div class="forget">
                                        <a href="{{ route('login') }}">Sign In to Your Account</a>
                                    </div>
                                </div>

                                <!-- help text  -->
                                <p class="help-block" id="help-text" style="display:none">
                                    {!! trans('auth/general.username_help_bottom') !!}
                                </p>
                            </div>
                        </div>

                        <div class="login__container__box__footer" style="margin-top: 20px">
                            <button disabled class="btn btn-block btn-style-ff" type="submit" id="submit">
                                {{ trans('auth/general.email_reset_password')  }}
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
    </div>
@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function() {
            const $userInput = $('#username');
            const $submitBtn = $('#submit');

            function toggleSubmit() {
                const valid = $.trim($userInput.val()).length > 0;

                $submitBtn.prop('disabled', !valid);

                if (valid) {
                    $submitBtn.addClass('ff-bg-dark-grey');
                } else {
                    $submitBtn.removeClass('ff-bg-dark-grey');
                }
            }

            $userInput.on('input', toggleSubmit);
        });
    </script>
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function () {
            $("#show").click(function(){
                $("#help-text").fadeIn(500);
                $("#show").hide();
                $("#hide").show();
            });

            $("#hide").click(function(){
                $("#help-text").fadeOut(300);
                $("#show").show();
                $("#hide").hide();
            });
        });
    </script>
@endpush


@extends('layouts/basic')

@push('ff-top')
<link rel="stylesheet" href="{{ url(mix('css/build/ff.css')) }}">
@endpush

{{-- Page content --}}
@section('content')
    <form role="form" action="{{ url('/login') }}" method="POST" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <!-- this is a hack to prevent Chrome from trying to autocomplete fields -->
        <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" aria-hidden="true">
        <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" aria-hidden="true">

        <div class="row login">
            <div class="col-md-4 login__info">
                <div class="login__info__copy">
                    <div class="copy-hero-ff">Streamlining Asset Management Across Phintraco Group</div>
                    <div class="copyright-ff">Copyright © 2025 PhinVentory</div>
                </div>
            </div>

            <div class="col-md-8 login__container">
                <div class="login__container__box">
                    <div class="login__container__box__header">
                        <img src="/img/phinventory-logo.webp" alt="PhinVentory logo" height="130">
                        <h1 class="header-title">Login to PhinVentory</h1>
                        <p class="header-description">The centralized asset management platform for Phintraco Group</p>
                    </div>

                    <div class="login__container__box__content">
                        <div class="row">
                            @if ($snipeSettings->login_note)
                                <div class="col-md-12">
                                    <div class="alert ff-bg-dark-grey radius" style="color: white;">
                                        {!!  Helper::parseEscapedMarkedown($snipeSettings->login_note)  !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Notifications -->
                            @include('notifications')

                            @if (!config('app.require_saml'))
                            <div class="col-md-12">
                                <fieldset name="login" aria-label="login">
                                    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                        <label class="label-style-ff" style="margin-bottom: 5px" for="username">
                                            {{-- <x-icon type="user" /> --}}
                                            {{ trans('admin/users/table.username')  }}
                                        </label>
                                        <input class="form-control radius input-style-ff" placeholder="{{ trans('admin/users/table.username')  }}" name="username" type="text" id="username" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}" autofocus>
                                        {!! $errors->first('username', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    </div>
                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label class="label-style-ff" style="margin-bottom: 5px" for="password">
                                            {{-- <x-icon type="password" /> --}}
                                            {{ trans('admin/users/table.password')  }}
                                        </label>
                                        <input class="form-control radius input-style-ff" placeholder="{{ trans('admin/users/table.password')  }}" name="password" type="password" id="password" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}">
                                        {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    </div>
                                    <div class="form-group">
                                        <div class="remember-forget-ff">
                                            <label class="checkbox-container">
                                                <input class="checkbox-style-ff" name="remember" type="checkbox" value="1" id="remember"> <span class="label-style-ff">{{ trans('auth/general.remember_me')  }}</span>
                                            </label>
                                            <div class="forget">
                                                @if ($snipeSettings->custom_forgot_pass_url)
                                                    <a href="{{ $snipeSettings->custom_forgot_pass_url }}" rel="noopener">{{ trans('auth/general.forgot_password') }}</a>
                                                @elseif (!config('app.require_saml'))
                                                    <a href="{{ route('password.request') }}">{{ trans('auth/general.forgot_password') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div> <!-- end col-md-12 -->
                            @endif
                        </div> <!-- end row -->
                    </div>
                    <div class="login__container__box__footer">
                        @if (config('app.require_saml'))
                            <a class="btn btn-primary btn-block btn-style-ff" href="{{ route('saml.login')  }}">{{ trans('auth/general.saml_login')  }}</a>
                        @else
                            <button disabled class="btn btn-block btn-style-ff" type="submit" id="submit">
                                {{ trans('auth/general.login')  }}
                            </button>
                        @endif

                        @if (($snipeSettings->google_login=='1') && ($snipeSettings->google_client_id!='') && ($snipeSettings->google_client_secret!=''))
                            <div class="separator separator-style-ff" style="padding: 10px 0;">{{ strtoupper(trans('general.or')) }}</div>
                            <a href="{{ route('google.redirect')  }}" class="btn btn-block btn-social btn-google btn-social-style-ff">
                                <i class="fa-brands fa-google"></i>{{ trans('auth/general.google_login') }}
                            </a>
                        @endif
                    </div>

                    @if (!config('app.require_saml') && $snipeSettings->saml_enabled)
                    <div class="row">
                        <div class="text-center col-md-12">
                            <a href="{{ route('saml.login')  }}">{{ trans('auth/general.saml_login')  }}</a>
                        </div>
                    </div>
                    @endif
                </div> <!-- end login box -->
            </div> <!-- col-md-4 -->
        </div> <!-- end row -->
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userInput = document.getElementById('username');
            const passInput = document.getElementById('password');
            const submitBtn = document.getElementById('submit');

            function toggleSubmit() {
            const valid = userInput.value.trim().length > 0
                        && passInput.value.trim().length > 0;

            submitBtn.disabled = !valid;

            if (valid) {
                submitBtn.classList.add('ff-bg-dark-grey');
            } else {
                submitBtn.classList.remove('ff-bg-dark-grey');
            }
            }

            userInput.addEventListener('input', toggleSubmit);
            passInput.addEventListener('input', toggleSubmit);
        });
    </script>
@stop
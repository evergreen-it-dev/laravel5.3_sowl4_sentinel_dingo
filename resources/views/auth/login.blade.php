@extends('layouts.master')
@section('body')
    <div class="container">
        <div class="omb_login">
            <h3 class="omb_authTitle">Войти или <a href="{{URL::to('/register')}}">зарегистрироваться</a></h3>
            @include('auth.social_buttons')
            <div class="row omb_row-sm-offset-3 omb_loginOr">
                <div class="col-xs-12 col-sm-6">
                    <hr class="omb_hrOr">
                    <span class="omb_spanOr">или</span>
                </div>
            </div>

            <div class="row omb_row-sm-offset-3">
                <div class="col-xs-12 col-sm-6">
                    {!! Form::open(['class' => 'omb_loginForm']) !!}
                        @include('errors.errmsg')
                        @include('widgets.form._formitem_text', ['name' => 'email', 'placeholder' => 'Email', 'fa_icon_class' => 'fa-user' ])
                        @include('widgets.form._formitem_password', ['name' => 'password', 'placeholder' => 'Пароль', 'fa_icon_class' => 'fa-lock' ])
                        @include('widgets.form._formitem_btn_submit', ['title' => 'Войти'])
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="row omb_row-sm-offset-3">
                <div class="col-xs-12 col-sm-6">
                    <p class="omb_forgotPwd">
                        <a href="{{URL::to('/reset')}}">Забыли пароль?</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
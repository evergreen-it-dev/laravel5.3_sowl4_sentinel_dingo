@extends('layouts.master')
@section('body')
    <div class="container">
        <div class="omb_login">
            <h3 class="omb_authTitle">Обновление пароля</h3>
            <div class="row omb_row-sm-offset-3">
                <div class="col-xs-12 col-sm-6">
                    {!! Form::open(['class' => 'omb_loginForm']) !!}
                    @include('errors.errmsg')
                    @include('widgets.form._formitem_password', ['name' => 'password', 'placeholder' => 'Новый пароль', 'fa_icon_class' => 'fa-lock' ])
                    @include('widgets.form._formitem_password', ['name' => 'password_confirm', 'placeholder' => 'Подтверждение нового пароля', 'fa_icon_class' => 'fa-lock' ])
                    @include('widgets.form._formitem_btn_submit', ['title' => 'Подтвердить обновление пароля'])
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
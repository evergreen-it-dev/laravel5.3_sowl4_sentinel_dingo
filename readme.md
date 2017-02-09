<h1>Админ-пакет для Laravel 5.3 + Sleeping Owl4 + Sentinel + Dingo</h1>

<p>Папкет на базе SleepingOwlAdmin 4-ой версии и добавили к ней управление пользователями, ролями и правами через пакет Sentinel. А также добавили регистрацию и авторизацию через Facebook, Google+, VKontakte и реализовали заготовку под REST/RESTful API с помощью пакета Dingo.</p>
<p>Вопросы пишите на <a href="mailto:support@evergreens.com.ua">support@evergreens.com.ua</a></p>

<h2>Инструкция по установке и настройке</h2> <p>Мы не будем рассматривать вопросы настройки окружения, установки и первоначальной настройки фреймворка. Начнем с той точки, когда у нас установлен laravel версии 5.3.* и подключен к БД. В .env указан URL приложения, например http://mysite.com и домен под API:</p>
<pre><code>APP_URL=http://mysite.com
API_DOMAIN=api.mysite.com</code></pre>
<p>На момент написания материала, последняя стабильная версия фреймворка - 5.3.16.</p>
<p>Для начала установим все необходимые пакеты, используемые в сборке, а после приступим к их настройке.</p>

<p>Склонировав и развернув проект, в .env добавляем следующие строки и задаем свои значения:</p>
<pre><code>FACEBOOK_KEY=yourkeyfortheservice
FACEBOOK_SECRET=yoursecretfortheservice
FACEBOOK_REDIRECT_URI=http://mysite.com/callback?network=facebook 

GOOGLE_KEY=yourkeyfortheservice
GOOGLE_SECRET=yoursecretfortheservice
GOOGLE_REDIRECT_URI=http://mysite.com/callback?network=google 

VKONTAKTE_KEY=yourkeyfortheservice
VKONTAKTE_SECRET=yoursecretfortheservice
VKONTAKTE_REDIRECT_URI=http://mysite.com/callback?network=vkontakte

APP_URL=http://mysite.com
API_DOMAIN=api.mysite.com
API_STANDARDS_TREE=vnd
API_PREFIX=v1
API_NAME=mysite
API_DEBUG=true</code></pre>
<p>В терминале последовательно запускаем команды:</p>
<pre><code>php artisan jwt:generate</code></pre>
<pre><code>php artisan migrate</code></pre>
<pre><code>php artisan db:seed</code></pre>
<pre><code>php artisan config:cache</code></pre>
<p>Открываем для редактирования файл composer.json</p>
<p>В секцию "require" добавляем следующие строки:</p>
<pre><code>"dingo/api": "1.0.x@dev",
"laravelrus/sleepingowl": "4.*@dev",
"cartalyst/sentinel": "2.0.*",
"laravelcollective/html": "^5.3.0",
"socialiteproviders/google": "^2.0",
"socialiteproviders/vkontakte": "^2.0",
"laravel/socialite": "^2.0",
"tymon/jwt-auth": "0.5.*",
"fzaninotto/faker": "^1.6",
"nesbot/carbon": "^1.21"</code></pre>
<p>Запускаем в терминале команду</p>
<pre><code>composer update</code></pre>
<p>В config/app.php, в массив $providers добавляем (до Application Service Providers):</p>
<pre><code data-language="php">/*
 * Package Service Providers...
 */

SleepingOwl\Admin\Providers\SleepingOwlServiceProvider::class,
Dingo\Api\Provider\LaravelServiceProvider::class,
Cartalyst\Sentinel\Laravel\SentinelServiceProvider::class,
Collective\Html\HtmlServiceProvider::class,
\SocialiteProviders\Manager\ServiceProvider::class,
Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,</code></pre>
<p>в массив $aliases добавляем</p>
<pre><code data-language="php">'Activation' =&gt; Cartalyst\Sentinel\Laravel\Facades\Activation::class,
'Reminder'   =&gt; Cartalyst\Sentinel\Laravel\Facades\Reminder::class,
'Sentinel'   =&gt; Cartalyst\Sentinel\Laravel\Facades\Sentinel::class,
'Form' =&gt; Collective\Html\FormFacade::class,
'Html' =&gt; Collective\Html\HtmlFacade::class,
'Socialite' =&gt; Laravel\Socialite\Facades\Socialite::class,
'JWTAuth' =&gt; Tymon\JWTAuth\Facades\JWTAuth::class,
'JWTFactory' =&gt; Tymon\JWTAuth\Facades\JWTFactory::class,</code></pre>
<p>Там же правим значение для локали приложения:</p>
<pre><code>'locale' =&gt; 'ru',</code></pre>
<p>Запускаем в терминале команду, устанавливаем административный интерфейс для Laravel - SleepingOwlAdmin:</p>
<pre><code>php artisan sleepingowl:install</code></pre>
<p>Последовательно запускаем в терминале команды:</p>
<pre><code>php artisan vendor:publish --provider="Dingo\Api\Provider\LaravelServiceProvider"
php artisan vendor:publish --provider="Cartalyst\Sentinel\Laravel\SentinelServiceProvider"
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"</code></pre>
<p>Генерируем ключ для jwt:</p>
<pre><code>php artisan jwt:generate</code></pre>
<p>Во избежание коллизий, сразу удалим дефолтные миграции Laravel:<br /> database/migrations/2014_10_12_000000_create_users_table.php<br /> database/migrations/2014_10_12_100000_create_password_resets_table.php</p>
<p>Итак, все необходимые пакеты установлены. Приступаем к их настройке.</p>
<h3>Настройка</h3>
<p>Открываем для редактирования файл миграции database/migrations/2014_07_02_230147_migration_cartalyst_sentinel.php</p>
<p>В секцию с созданием таблицы users, добавляем поля:</p>
<pre><code data-language="php">$table-&gt;string('facebook_id')-&gt;nullable();
$table-&gt;string('google_id')-&gt;nullable();
$table-&gt;string('vkontakte_id')-&gt;nullable();</code></pre>
<p>Создаем модель Post и миграцию под нее</p>
<pre><code>php artisan make:model -m Post</code></pre>
<p>Добавляем в миграцию 2 поля: title и content</p>
<pre><code data-language="php">public function up()
{
	Schema::create('posts', function (Blueprint $table) {
		$table-&gt;increments('id');
		$table-&gt;string('title');
		$table-&gt;text('content');
		$table-&gt;timestamps();
	});
}</code></pre>
<p>Запускаем миграции:</p>
<pre><code>php artisan migrate</code></pre>
<p>Выполняем команду</p>
<pre><code>php artisan make:seeder RoleSeeder</code></pre>
<p>Редактируем файл database/seeds/RoleSeeder.php<br /> Закладываем основу для 3-х ролей пользователей и создаем пользователя администратора.</p>
<pre><code data-language="php">&lt;?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            'email'    =&gt; 'admin@admin.com',
            'password' =&gt; 'adminadmin',
        ];
        $adminUser = Sentinel::registerAndActivate($admin);
        $role = [
            'name' =&gt; 'Администратор',
            'slug' =&gt; 'admin',
            'permissions' =&gt; [
                'admin' =&gt; true,
            ]
        ];
        $adminRole = Sentinel::getRoleRepository()-&gt;createModel()-&gt;fill($role)-&gt;save();
        $adminUser-&gt;roles()-&gt;attach($adminRole);
        $role = [
            'name' =&gt; 'Пользователь',
            'slug' =&gt; 'user',
        ];
        $userRole = Sentinel::getRoleRepository()-&gt;createModel()-&gt;fill($role)-&gt;save();
        $role = [
            'name' =&gt; 'Забанен',
            'slug' =&gt; 'banned',
        ];
        $banRole = Sentinel::getRoleRepository()-&gt;createModel()-&gt;fill($role)-&gt;save();
    }
}</code></pre>
<p>Выполняем команду</p>
<pre><code>php artisan make:seeder PostSeeder</code></pre>
<p>Редактируем файл database/seeds/PostSeeder.php<br /> Создаем болванки публикаций с фейковым наполнением:</p>
<pre><code data-language="php">&lt;?php
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        for ($i=0; $i&lt;10; $i++){
            DB::table('posts')-&gt;insert([
                'title' =&gt; $faker-&gt;text(10),
                'content' =&gt; $faker-&gt;text(200),
                'created_at' =&gt; Carbon::now(),
                'updated_at' =&gt; Carbon::now(),
            ]);
        }
    }
}</code></pre>
<p>Редактируем файл database/seeds/DatabaseSeeder.php</p>
<pre><code data-language="php">&lt;?php
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this-&gt;call(RoleSeeder::class);
        $this-&gt;call(PostSeeder::class);
    }
}</code></pre>
<p>Запускаем команду:</p>
<pre><code>php artisan db:seed</code></pre>
<p>Удаляем директорию app/Http/Controllers/Auth за ненадобностью</p>
<p>Создаем AuthController</p>
<pre><code>php artisan make:controller AuthController</code></pre>
<p>Добавляем роуты в routes\web.php</p>
<pre><code data-language="php">/**
 * Route for auth system
 */
// Вызов страницы регистрации пользователя
Route::get('register', 'AuthController@register');
// Пользователь заполнил форму регистрации и отправил
Route::post('register', 'AuthController@registerProcess');
// Пользователь получил письмо для активации аккаунта со ссылкой сюда
Route::get('activate/{id}/{code}', 'AuthController@activate');
// Вызов страницы авторизации
Route::get('login', 'AuthController@login');
// Пользователь заполнил форму авторизации и отправил
Route::post('login', 'AuthController@loginProcess');
// Выход пользователя из системы
Route::get('logout', 'AuthController@logoutuser');
// Пользователь забыл пароль и запросил сброс пароля. Это начало процесса -
// Страница с запросом E-Mail пользователя
Route::get('reset', 'AuthController@resetOrder');
// Пользователь заполнил и отправил форму с E-Mail в запросе на сброс пароля
Route::post('reset', 'AuthController@resetOrderProcess');
// Пользователю пришло письмо со ссылкой на эту страницу для ввода нового пароля
Route::get('reset/{id}/{code}', 'AuthController@resetComplete');
// Пользователь ввел новый пароль и отправил.
Route::post('reset/{id}/{code}', 'AuthController@resetCompleteProcess');
// Сервисная страничка, показываем после заполнения рег формы, формы сброса и т.
// о том, что письмо отправлено и надо заглянуть в почтовый ящик.
Route::get('wait', 'AuthController@wait');
// Пользователь получил письмо после регистрации в приложении для активации аккаунта со ссылкой
Route::get('activate_app/{id}/{code}', 'AuthController@activateForAppUser');
//Авторизация через соцсети
Route::get('signin', 'AuthController@signin');
//Коллбэк после авторизации через соцсети
Route::get('callback', 'AuthController@callbackSignin');
</code></pre>
<p>В директории resources/views создаем следующие поддиректории: auth, footer, header, layouts, mail</p>
<p>В директории resources/views/layouts создаем файл master.blade.php</p>
<pre><code data-language="html">&lt;!DOCTYPE html&gt;
&lt;!--[if lt IE 7]&gt;      &lt;html class="no-js lt-ie9 lt-ie8 lt-ie7"&gt; &lt;![endif]--&gt;
&lt;!--[if IE 7]&gt;         &lt;html class="no-js lt-ie9 lt-ie8"&gt; &lt;![endif]--&gt;
&lt;!--[if IE 8]&gt;         &lt;html class="no-js lt-ie9"&gt; &lt;![endif]--&gt;
&lt;!--[if gt IE 8]&gt;&lt;!--&gt; &lt;html class="no-js"&gt; &lt;!--&lt;![endif]--&gt;
&lt;head&gt;
    @include('header.head')
&lt;/head&gt;
&lt;body&gt;
&lt;!--[if lt IE 7]&gt;</code></pre>
<p class="browsehappy">Вы используете <strong>слишком старый</strong> браузер. Пожалуйста <a href="http://browsehappy.com/">обновите ваш браузер</a> для нормального серфинга по современным сайтам.</p>
<pre><code data-language="html">
&lt;![endif]--&gt;
&lt;header id="header" class=""&gt;
    @include('header.header')
&lt;/header&gt;
&lt;section&gt;
    @yield('body')
&lt;/section&gt;
@include('footer.footer')
@include('footer.foot_script')
&lt;/body&gt;
&lt;/html&gt;</code></pre>
<p>Создаем пустые файлы:</p>
<p>resources/views/header/head.blade.php<br /> resources/views/header/header.blade.php<br /> resources/views/footer/footer.blade.php<br /> resources/views/footer/foot_script.blade.php</p>
<p>В resources/views/header/head.blade.php добавляем</p>
<pre><code>&lt;link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"&gt;
&lt;link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet"&gt;
&lt;link href="/css/auth.css" rel="stylesheet"&gt;
</code></pre>
<p>Создаем файл public/css/auth.css:</p>
<pre><code data-language="css">@media (min-width: 768px) {
    .omb_row-sm-offset-3 div:first-child[class*="col-"] {
        margin-left: 25%;
    }
}

.omb_login .omb_authTitle {
    text-align: center;
	line-height: 300%;
}
	
.omb_login .omb_socialButtons a {
	color: white; // In yourUse @body-bg 
	opacity:0.9;
}
.omb_login .omb_socialButtons a:hover {
    color: white;
	opacity:1;    	
}
.omb_login .omb_socialButtons .omb_btn-facebook {background: #3b5998;}
.omb_login .omb_socialButtons .omb_btn-vkontakte {background: #00aced;}
.omb_login .omb_socialButtons .omb_btn-google {background: #c32f10;}


.omb_login .omb_loginOr {
	position: relative;
	font-size: 1.5em;
	color: #aaa;
	margin-top: 1em;
	margin-bottom: 1em;
	padding-top: 0.5em;
	padding-bottom: 0.5em;
}
.omb_login .omb_loginOr .omb_hrOr {
	background-color: #cdcdcd;
	height: 1px;
	margin-top: 0px !important;
	margin-bottom: 0px !important;
}
.omb_login .omb_loginOr .omb_spanOr {
	display: block;
	position: absolute;
	left: 50%;
	top: -0.6em;
	margin-left: -1.5em;
	background-color: white;
	width: 3em;
	text-align: center;
}			

.omb_login .omb_loginForm .input-group.i {
	width: 2em;
}
.omb_login .omb_loginForm  .help-block {
    color: red;
}

	
@media (min-width: 768px) {
    .omb_login .omb_forgotPwd {
        text-align: right;
		margin-top:10px;
 	}		
}

.btn-group-lg&gt;.btn, .btn-lg {
	padding: 10px 7px;
}</code></pre>
<p>Создаем resources/views/errors/errmsg.blade.php :</p>
<pre><code data-language="php">@if ($errors-&gt;any())</code></pre>
<div class="alert alert-danger alert-block">{{-- <button type="button" class="close" data-dismiss="alert"><i class="fa fa-minus-square"></i></button> --}} <strong>Ошибка:</strong> @if ($message = $errors-&gt;first(0, ':message')) {{ $message }} @else Пожалуйста проверьте правильность заполнения формы @endif</div>
<pre><code data-language="php">
@endif

@if ($message = Session::get('success'))</code></pre>
<div class="alert alert-success alert-block"><button type="button" class="close" data-dismiss="alert"><i class="fa fa-minus-square"></i></button> <strong>Поздравляем!</strong> {{ $message }}</div>
<pre><code data-language="php">
@endif</code></pre>
<p>Создаем шаблоны элементов форм:<br /> resources/views/widgets/form/_formitem_text.blade.php :</p>
<pre><code data-language="php">&lt;?php
if(! isset($value)) $value = null;
$error_class = $errors-&gt;has($name) ? ' has-error' : '';
?&gt;</code></pre>
<div class="input-group"><span class="input-group-addon"><i class="fa {!! $fa_icon_class !!}"></i></span> {!! Form::text($name, $value, array('placeholder' =&gt; $placeholder, 'class' =&gt; 'form-control' . $error_class )) !!}</div>
<pre><code data-language="php">
<span class="help-block">{!! $errors-&gt;first($name) !!}</span></code></pre>
<p>resources/views/widgets/form/_formitem_password.blade.php :</p>
<pre><code data-language="php">&lt;php $error_class = $errors-&gt;has($name) ? ' has-error' : ''; ?&gt;</code></pre>
<div class="input-group"><span class="input-group-addon"><i class="fa {!! $fa_icon_class !!}"></i></span> {!! Form::password($name, array('placeholder' =&gt; $placeholder, 'class' =&gt; 'form-control' . $error_class )) !!}</div>
<pre><code data-language="php">
<span class="help-block">{!! $errors-&gt;first($name) !!}</span></code></pre>
<p>resources/views/widgets/form/_formitem_btn_submit.blade.php :</p>
<pre><code data-language="php">{!! Form::submit($title, array('class' =&gt; 'btn btn-lg btn-primary btn-block')) !!}</code></pre>
<p>Создаем resources/views/auth/login.blade.php :</p>
<pre><code data-language="php">@extends('layouts.master')
@section('body')</code></pre>
<div class="container">
<div class="omb_login">
<h3 class="omb_authTitle">Войти или <a href="{{URL::to('/register')}}">зарегистрироваться</a></h3>
@include('auth.social_buttons')
<div class="row omb_row-sm-offset-3 omb_loginOr">
<div class="col-xs-12 col-sm-6"><hr class="omb_hrOr" /><span class="omb_spanOr">или</span></div>
</div>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">{!! Form::open(['class' =&gt; 'omb_loginForm']) !!} @include('errors.errmsg') @include('widgets.form._formitem_text', ['name' =&gt; 'email', 'placeholder' =&gt; 'Email', 'fa_icon_class' =&gt; 'fa-user' ]) @include('widgets.form._formitem_password', ['name' =&gt; 'password', 'placeholder' =&gt; 'Пароль', 'fa_icon_class' =&gt; 'fa-lock' ]) @include('widgets.form._formitem_btn_submit', ['title' =&gt; 'Войти']) {!! Form::close() !!}</div>
</div>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">
<p class="omb_forgotPwd"><a href="{{URL::to('/reset')}}">Забыли пароль?</a></p>
</div>
</div>
</div>
</div>
<pre><code data-language="php">
@stop</code></pre>
<p>Создаем resources/views/auth/register.blade.php :</p>
<pre><code data-language="php">@extends('layouts.master')
@section('body')</code></pre>
<div class="container">
<div class="omb_login">
<h3 class="omb_authTitle">Зарегистрироваться или <a href="{{URL::to('/login')}}">войти</a></h3>
@include('auth.social_buttons')
<div class="row omb_row-sm-offset-3 omb_loginOr">
<div class="col-xs-12 col-sm-6"><hr class="omb_hrOr" /><span class="omb_spanOr">или</span></div>
</div>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">{!! Form::open(['class' =&gt; 'omb_loginForm']) !!} @include('errors.errmsg') @include('widgets.form._formitem_text', ['value' =&gt; $email, 'name' =&gt; 'email', 'placeholder' =&gt; 'Email', 'fa_icon_class' =&gt; 'fa-user' ]) @include('widgets.form._formitem_password', ['name' =&gt; 'password', 'placeholder' =&gt; 'Пароль', 'fa_icon_class' =&gt; 'fa-lock' ]) @include('widgets.form._formitem_password', ['name' =&gt; 'password_confirm', 'placeholder' =&gt; 'Подтверждение пароля', 'fa_icon_class' =&gt; 'fa-lock' ]) @include('widgets.form._formitem_btn_submit', ['title' =&gt; 'Зарегистрироваться']) {!! Form::close() !!}</div>
</div>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">
<p class="omb_forgotPwd"><a href="{{URL::to('/reset')}}">Забыли пароль?</a></p>
</div>
</div>
</div>
</div>
<pre><code data-language="php">
@stop</code></pre>
<p>Создаем resources/views/auth/reset_order.blade.php :</p>
<pre><code data-language="php">@extends('layouts.master')
@section('body')</code></pre>
<div class="container">
<div class="omb_login">
<h3 class="omb_authTitle">Сброс пароля</h3>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">{!! Form::open(['class' =&gt; 'omb_loginForm']) !!} @include('errors.errmsg') @include('widgets.form._formitem_text', ['name' =&gt; 'email', 'placeholder' =&gt; 'Email', 'fa_icon_class' =&gt; 'fa-user' ]) @include('widgets.form._formitem_btn_submit', ['title' =&gt; 'Сбросить пароль']) {!! Form::close() !!}</div>
</div>
</div>
</div>
<pre><code data-language="php">
@stop</code></pre>
<p>Создаем resources/views/auth/reset_complete.blade.php :</p>
<pre><code data-language="php">@extends('layouts.master')
@section('body')</code></pre>
<div class="container">
<div class="omb_login">
<h3 class="omb_authTitle">Обновление пароля</h3>
<div class="row omb_row-sm-offset-3">
<div class="col-xs-12 col-sm-6">{!! Form::open(['class' =&gt; 'omb_loginForm']) !!} @include('errors.errmsg') @include('widgets.form._formitem_password', ['name' =&gt; 'password', 'placeholder' =&gt; 'Новый пароль', 'fa_icon_class' =&gt; 'fa-lock' ]) @include('widgets.form._formitem_password', ['name' =&gt; 'password_confirm', 'placeholder' =&gt; 'Подтверждение нового пароля', 'fa_icon_class' =&gt; 'fa-lock' ]) @include('widgets.form._formitem_btn_submit', ['title' =&gt; 'Подтвердить обновление пароля']) {!! Form::close() !!}</div>
</div>
</div>
</div>
<pre><code data-language="php">
@stop</code></pre>
<p>Создаем resources/views/auth/wait.blade.php :</p>
<pre><code data-language="php">@extends('layouts.master')
@section('body')</code></pre>
<h3>Через несколько минут, вам на почту придет письмо с дальнейшими инструкциями.</h3>
<pre><code data-language="php">
@stop</code></pre>
<p>Создаем resources/views/auth/social_buttons.blade.php :</p>
<div class="row omb_row-sm-offset-3 omb_socialButtons">
<div class="col-xs-4 col-sm-2"><a href="{{URL::to('/signin?network=facebook')}}" class="btn btn-lg btn-block omb_btn-facebook"> <i class="fa fa-facebook visible-xs"></i> <span class="hidden-xs">Facebook</span> </a></div>
<div class="col-xs-4 col-sm-2"><a href="{{URL::to('/signin?network=google')}}" class="btn btn-lg btn-block omb_btn-google"> <i class="fa fa-google-plus visible-xs"></i> <span class="hidden-xs">Google+</span> </a></div>
<div class="col-xs-4 col-sm-2"><a href="{{URL::to('/signin?network=vkontakte')}}" class="btn btn-lg btn-block omb_btn-vkontakte"> <i class="fa fa-vk visible-xs"></i> <span class="hidden-xs">VKontakte</span> </a></div>
</div>
<p>Готовим представления E-Mail:</p>
<p>resources/views/mail/account_activate.blade.php :</p>
<pre><code data-language="php">Для активации аккаунта пройдите по <a href="{{ URL::to(" activate="" sentuser-="">getUserId()}/{$code}") }}"&gt;ссылке</a></code></pre>
<p>resources/views/mail/account_reminder.blade :</p>
<pre><code data-language="php">Для активации аккаунта пройдите по <a href="{{ URL::to(" activate_app="" sentuser-="">getUserId()}/{$code}") }}"&gt;ссылке</a></code></pre>
<p>resources/views/mail/account_activate_app.blade.php :</p>
<pre><code data-language="php">Для создания нового пароля пройдите по <a href="{{ URL::to(" reset="" sentuser-="">getUserId()}/{$code}") }}"&gt;ссылке</a></code></pre>
<p>В resources/views/welcome.blade.php произвльно, в любом месте добавим временную болванку:</p>
<pre><code data-language="php">&lt;?php
if ($user = Sentinel::getUser())
{
    echo 'Привет, ' . $user-&gt;email;
}else{
    echo 'Привет, гость';
}
?&gt;</code></pre>
<p>Наполняем app/Http/Controllers/AuthController.php :</p>
<pre><code data-language="php">&lt;?php

namespace App\Http\Controllers;

use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;

use Illuminate\Http\Request;
use App\Http\Requests;
use Redirect;
use Sentinel;
use Activation;
use Reminder;
use Validator;
use Mail;
use Storage;
use CurlHttp;
use Illuminate\Support\Facades\Input;
use Socialite;
use Session;

class AuthController extends Controller
{

    public $networks = ['vkontakte', 'facebook', 'google'];

    /**
     * Show login page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Show Register page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function register()
    {
        $email = Session::get('network_user') != '' ? Session::get('network_user')-&gt;getEmail() : '';
        return view('auth.register', ['email' =&gt; $email]);
    }


    /**
     * Show wait page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wait()
    {
        return view('auth.wait');
    }


    /**
     * Process login users
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function loginProcess(Request $request)
    {
        try
        {
            $messages = [
                'email.required'            =&gt; 'Введите email',
                'email.email'               =&gt; 'Похоже, что email введен с ошибкой',
                'password.required'         =&gt; 'Введидите пароль',
                'password.between'          =&gt; 'Минимальная длина пароля - 8 символов',
            ];
            $this-&gt;validate($request, [
                'email' =&gt; 'required|email',
                'password' =&gt; 'required|between:8,100',
            ], $messages);
            if (Sentinel::authenticate($request-&gt;all()))
            {
                if(Sentinel::inRole('admin')) return redirect('admin');
                return Redirect::intended('/');
            }
            $errors = 'Неправильный логин или пароль.';
            return Redirect::back()
                -&gt;withInput()
                -&gt;withErrors($errors);
        }
        catch (NotActivatedException $e)
        {
            $sentuser= $e-&gt;getUser();
            $activation = Activation::create($sentuser);
            $code = $activation-&gt;code;
            $sent = Mail::send('mail.account_activate', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m-&gt;from('noreplay@mysite.com', 'MySite');
                $m-&gt;to($sentuser-&gt;email)-&gt;subject('Активация аккаунта');
            });

            if ($sent === 0)
            {
                return Redirect::to('login')
                    -&gt;withErrors('Ошибка отправки письма активации.');
            }
            $errors = 'Ваш аккаунт не ативирован! Поищите в своем почтовом ящике письмо со ссылкой для активации (Вам отправлено повторное письмо). ';
            return view('auth.login')-&gt;withErrors($errors);
        }
        catch (ThrottlingException $e)
        {
            $delay = $e-&gt;getDelay();
            $errors = "Ваш аккаунт блокирован на {$delay} секунд.";
        }
        return Redirect::back()
            -&gt;withInput()
            -&gt;withErrors($errors);
    }


    /**
     * Process register user from site
     *
     * @param Request $request
     * @return $this
     */
    public function registerProcess(Request $request)
    {
        $messages = [
            'email.required'            =&gt; 'Введите email',
            'email.email'               =&gt; 'Похоже, что email введен с ошибкой',
            'password.required'         =&gt; 'Введидите пароль',
            'password_confirm.required' =&gt; 'Введидите подтверждение пароля',
            'password_confirm.same'     =&gt; 'Введенные пароли не одинаковы',
            'password.between'          =&gt; 'Минимальная длина пароля - 8 символов',
        ];
        $this-&gt;validate($request, [
            'email' =&gt; 'required|email',
            'password' =&gt; 'required|between:8,100',
            'password_confirm' =&gt; 'required|same:password',
        ], $messages);
        $input = $request-&gt;all();

        if (Session::get('network_user') &amp;&amp; Session::get('network_user')-&gt;id) {

            $input[Session::get('network') . '_id'] = Session::get('network_user')-&gt;id;

        }

        $credentials = [ 'email' =&gt; $request-&gt;email ];
        if($user = Sentinel::findByCredentials($credentials))
        {
            return Redirect::to('register')
                -&gt;withErrors('Такой Email уже зарегистрирован.');
        }

        if ($sentuser = Sentinel::register($input))
        {
            $activation = Activation::create($sentuser);
            $code = $activation-&gt;code;
            $sent = Mail::send('mail.account_activate', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m-&gt;from('noreplay@mysite.com', 'MySite');
                $m-&gt;to($sentuser-&gt;email)-&gt;subject('Активация аккаунта');
            });
            if ($sent === 0)
            {
                return Redirect::to('register')
                    -&gt;withErrors('Ошибка отправки письма активации.');
            }

            $role = Sentinel::findRoleBySlug('user');
            $role-&gt;users()-&gt;attach($sentuser);

            Session::forget('network_user');
            Session::forget('network');

            return Redirect::to('login')
                -&gt;withSuccess('Ваш аккаунт создан. Проверьте Email для активации.')
                -&gt;with('userId', $sentuser-&gt;getUserId());
        }
        return Redirect::to('register')
            -&gt;withInput()
            -&gt;withErrors('Failed to register.');
    }


    /**
     *  Activate user account by user id and activation code
     *
     * @param $id
     * @param $code
     * @return $this
     */
    public function activate($id, $code)
    {
        $sentuser = Sentinel::findById($id);

        if ( ! Activation::complete($sentuser, $code))
        {
            return Redirect::to("login")
                -&gt;withErrors('Неверный или просроченный код активации.');
        }

        return Redirect::to('login')
            -&gt;withSuccess('Аккаунт активирован.');
    }

    /**
     *  Activate user account by user id and activation code
     *
     * @param $id
     * @param $code
     * @return $this
     */
    public function activateForAppUser($id, $code)
    {
        $sentuser = Sentinel::findById($id);

        if ( ! Activation::complete($sentuser, $code))
        {
            return view('auth.activate_result', ['result' =&gt; 'Invalid or expired activation code.']);
        }

        Sentinel::update($sentuser, ['email_confirmed' =&gt; 1]);

        return view('auth.activate_result', ['result' =&gt; 'Account activated.']);
    }


    /**
     * Show form for begin process reset password
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resetOrder()
    {
        return view('auth.reset_order');
    }


    /**
     * Begin process reset password by email
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function resetOrderProcess(Request $request)
    {
        $messages = [
            'email.required'            =&gt; 'Введите email',
            'email.email'               =&gt; 'Похоже, что email введен с ошибкой',
        ];
        $this-&gt;validate($request, [
            'email' =&gt; 'required|email',
        ], $messages);
        $email = $request-&gt;email;
        $sentuser = Sentinel::findByCredentials(compact('email'));
        if ( ! $sentuser)
        {
            return Redirect::back()
                -&gt;withInput()
                -&gt;withErrors('Пользователь с таким E-Mail в системе не найден.');
        }
        $reminder = Reminder::exists($sentuser) ?: Reminder::create($sentuser);
        $code = $reminder-&gt;code;

        $sent = Mail::send('mail.account_reminder', compact('sentuser', 'code'), function($m) use ($sentuser)
        {
            $m-&gt;from('noreplay@mysite.com', 'MySite');
            $m-&gt;to($sentuser-&gt;email)-&gt;subject('Сброс пароля');
        });
        if ($sent === 0)
        {
            return Redirect::to('reset')
                -&gt;withErrors('Ошибка отправки email.');
        }
        return Redirect::to('wait');
    }

    /**
     * Show form for complete reset password
     *
     * @param $id
     * @param $code
     * @return mixed
     */
    public function resetComplete($id, $code)
    {
        $user = Sentinel::findById($id);
        return view('auth.reset_complete');
    }


    /**
     * Complete reset password
     *
     * @param Request $request
     * @param $id
     * @param $code
     * @return $this
     */
    public function resetCompleteProcess(Request $request, $id, $code)
    {
        $messages = [
            'password.required'         =&gt; 'Введидите новый пароль',
            'password_confirm.required' =&gt; 'Введидите подтверждение нового пароля',
            'password_confirm.same'     =&gt; 'Введенные пароли не одинаковы',
            'password.between'          =&gt; 'Минимальная длина пароля - 8 символов',
        ];
        $this-&gt;validate($request, [
            'password' =&gt; 'required|between:8,100',
            'password_confirm' =&gt; 'required|same:password',
        ], $messages);
        $user = Sentinel::findById($id);
        if ( ! $user)
        {
            return Redirect::back()
                -&gt;withInput()
                -&gt;withErrors('Такого пользователя не существует.');
        }
        if ( ! Reminder::complete($user, $code, $request-&gt;password))
        {
            return Redirect::to('login')
                -&gt;withErrors('Неверный или просроченный код сброса пароля.');
        }
        return Redirect::to('login')
            -&gt;withSuccess("Пароль сброшен.");
    }

    /**
     * @return mixed
     */
    public function logoutuser()
    {
        Sentinel::logout();
        return Redirect::intended('/');
    }

    /**
     * @return mixed
     */
    public function signin()
    {
        if (Input::get('network') &amp;&amp; in_array(Input::get('network'), $this-&gt;networks)) {
            return Socialite::with(Input::get('network'))-&gt;redirect();
        }
    }

    public function callbackSignin()
    {
        //проверяем, есть ли параметр network, и присутствует ли он в массиве доступных соц. сетей
        if (Input::get('network') &amp;&amp; in_array(Input::get('network'), $this-&gt;networks)) {
            //получили данные пользователя из соц. сети
            $network_user = Socialite::with(Input::get('network'))-&gt;stateless()-&gt;user();

            //проверяем, есть ли в полученных данных значение email
            if ($network_user-&gt;getEmail() != '') {
                //если email получен - проверяем, есть ли в системе пользователь с таким email

                $credentials = ['email' =&gt; $network_user-&gt;getEmail()];
                $user = Sentinel::findByCredentials($credentials);

                if (!$user) {
                    //если нет в системе пользователя с таким email,
                    //проверяем есть ли в системе пользователь с таким id в конкретной соцсети

                    $credentials = [Input::get('network') . '_id' =&gt; $network_user-&gt;getId()];

                    $user = Sentinel::findByCredentials($credentials);

                } else {
                    //если есть в системе пользователь с таким email,
                    //проверяем заполнен ли id сети

                    if ($user[Input::get('network') . '_id'] == '') {

                        $credentials = [Input::get('network') . '_id' =&gt; $network_user-&gt;getId()];
                        $user = Sentinel::update($user, $credentials);

                    }
                }
            } else {
                //в полученных данных нет email
                //проверяем есть ли в системе пользователь с таким id в конкретной соцсети

                $credentials = [Input::get('network') . '_id' =&gt; $network_user-&gt;getId()];
                $user = Sentinel::findByCredentials($credentials);

            }

            if (!$user) {
                //пользователь в системе не найден -
                //отправляем на страницу регистрации со всеми полученными из соц. сети данными

                Session::put('network_user', $network_user);
                Session::put('network', Input::get('network'));

                return Redirect::to('/register');

            } else {
                //если найден - логиним в систему и редиректим на главную

                if ($activation = Activation::completed($user)) {

                    Sentinel::login($user);
                    return Redirect::intended('/');

                } else {
                    return Redirect::to('login')
                        -&gt;withErrors('Ранее, на Вашу почту было отправлено письмо с дальнейшими инструкциями по активации аккаунта.');
                }

            }

        }

    }

}
</code></pre>
<p>Приводим модель app/User.php к виду:</p>
<pre><code data-language="php">&lt;?php
namespace App;

use Cartalyst\Sentinel\Users\EloquentUser as CartalystUser;
use Hash;

class User extends CartalystUser
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'facebook_id', 'google_id', 'vkontakte_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at'
    ];

    protected $loginNames = ['email', 'facebook_id', 'google_id', 'vkontakte_id'];

    public function roles()
    {
        return $this-&gt;belongsToMany('App\Role', 'role_users', 'user_id', 'role_id');
    }

    public function theroles()
    {
        return $this-&gt;belongsToMany('App\Role', 'role_users', 'user_id', 'role_id');
    }

    public function setTherolesAttribute($roles)
    {
        $this-&gt;theroles()-&gt;detach();
        if ( ! $roles) return;
        if ( ! $this-&gt;exists) $this-&gt;save();
        $this-&gt;theroles()-&gt;attach($roles);
    }

    public function getTherolesAttribute($roles)
    {
        return array_pluck($this-&gt;theroles()-&gt;get(['id'])-&gt;toArray(), 'id');
    }

}</code></pre>
<p>В конфиге config/cartalyst.sentinel.php</p>
<pre><code data-language="php">'users' =&gt; [

        'model' =&gt; 'Cartalyst\Sentinel\Users\EloquentUser',

    ],</code></pre>
<p>заменяем на</p>
<pre><code data-language="php">'users' =&gt; [

    'model' =&gt; 'App\User',

],</code></pre>
<p>Выполняем команду:</p>
<pre><code>php artisan config:cache</code></pre>
<p>Правим app/Providers/EventServiceProvider.php</p>
<pre><code data-language="php">protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class =&gt; [
        'SocialiteProviders\VKontakte\VKontakteExtendSocialite@handle',
        'SocialiteProviders\Google\GoogleExtendSocialite@handle',
    ],
];</code></pre>
<p>Заводим приложения в Facebook, Google, VKontakte</p>
<p>Добавляем в config/services.php :</p>
<pre><code data-language="php">'facebook' =&gt; [
        'client_id'  =&gt; env('FACEBOOK_KEY'),
        'client_secret'  =&gt;  env('FACEBOOK_SECRET'),
        'redirect' =&gt; env('FACEBOOK_REDIRECT_URI'),
    ],

    'google' =&gt; [
	    'client_id' =&gt; env('GOOGLE_KEY'),
	    'client_secret' =&gt; env('GOOGLE_SECRET'),
	    'redirect' =&gt; env('GOOGLE_REDIRECT_URI'),  
	], 

    'vkontakte' =&gt; [
	    'client_id' =&gt; env('VKONTAKTE_KEY'),
	    'client_secret' =&gt; env('VKONTAKTE_SECRET'),
	    'redirect' =&gt; env('VKONTAKTE_REDIRECT_URI'),  
	],</code></pre>
<p>В .env добавляем:</p>
<pre><code>FACEBOOK_KEY=yourkeyfortheservice
FACEBOOK_SECRET=yoursecretfortheservice
FACEBOOK_REDIRECT_URI=http://mysite.com/callback?network=facebook 

GOOGLE_KEY=yourkeyfortheservice
GOOGLE_SECRET=yoursecretfortheservice
GOOGLE_REDIRECT_URI=http://mysite.com/callback?network=google 

VKONTAKTE_KEY=yourkeyfortheservice
VKONTAKTE_SECRET=yoursecretfortheservice
VKONTAKTE_REDIRECT_URI=http://mysite.com/callback?network=vkontakte</code></pre>
<p>Значения yourkeyfortheservice и yoursecretfortheservice заменяем на значения из настроек приложений в указанных соц. сетях</p>
<p>С регистацией, логином, авторизацией закончили. Приступаем к настройке панели администратора.</p>
<p>В файле app\Http\Kernel.php изменяем значение $routeMiddleware :</p>
<pre><code data-language="php">protected $routeMiddleware = [
     //   'auth' =&gt; \Illuminate\Auth\Middleware\Authenticate::class,
     //   'auth.basic' =&gt; \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
     //   'bindings' =&gt; \Illuminate\Routing\Middleware\SubstituteBindings::class,
     //   'can' =&gt; \Illuminate\Auth\Middleware\Authorize::class,
     //   'guest' =&gt; \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' =&gt; \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'api.throttle' =&gt; \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'isadmin' =&gt; \App\Http\Middleware\AdminPanel::class,
        'jwt.auth' =&gt; 'Tymon\JWTAuth\Middleware\GetUserFromToken',
        'jwt.refresh' =&gt; 'Tymon\JWTAuth\Middleware\RefreshToken',
    ];</code></pre>
<p>В config\sleeping_owl.php :</p>
<pre><code>'middleware' =&gt; ['web'],</code></pre>
<p>заменяем на</p>
<pre><code>'middleware' =&gt; ['web', 'isadmin'],</code></pre>
<p>Создаем файл app/Http/Middleware/AdminPanel.php :</p>
<pre><code data-language="php">&lt;?php


namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Redirect;
use Request;
use Response;
use AdminSection;

class AdminPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Sentinel::guest()) return redirect('login');
        if(Sentinel::inRole('admin')) return $next($request);

        $arrSlugs=str_getcsv($request-&gt;path(), '/');
        $method=Request::method();
        $user = Sentinel::check();
        if($method == 'DELETE')
        {
            $permit = $arrSlugs[0] . '.' . $arrSlugs[1] . '.delete';
            if ($user-&gt;hasAccess([$permit])) return $next($request);
            if ($user-&gt;hasAccess(['admin']))
            {
                $content = 'Для удаления объекта необходимы установленные права <b>'.  $permit .'</b> Для получения прав обратитесь к администратору';
                return Response::make(AdminSection::view($content, 'Попытка несанкционированного доступа'), 403);
            }
            return Redirect::back();
        }
        $permit=$arrSlugs[0];
        if(isset($arrSlugs[1])) $permit = $permit . '.' . $arrSlugs[1];
        if(isset($arrSlugs[2]) &amp;&amp; ($arrSlugs[2] == "create")) $permit = $permit . '.' . $arrSlugs[2];
        if(isset($arrSlugs[3])) $permit = $permit . '.' . $arrSlugs[3];
        if ($user-&gt;hasAccess([$permit])) return $next($request);
        if ($user-&gt;hasAccess(['admin']))
        {
            $content = 'Для данного действия необходимы установленные права <b>'. $permit .'</b> Для получения прав обратитесь к администратору';
            return Response::make(AdminSection::view($content, 'Попытка несанкционированного доступа'), 403);

        }
        return Redirect::back();
    }
}</code></pre>
<p>Создаем модель app/Role.php :</p>
<pre><code>php artisan make:model Role</code></pre>
<pre><code data-language="php">&lt;?php
namespace App;

use Cartalyst\Sentinel\Roles\EloquentRole;

class Role extends EloquentRole
{

    public function permits()
    {
        return $this-&gt;belongsToMany('App\Permit');
    }

}</code></pre>
<p>Создаем модель и миграцию для сущности Permit :</p>
<pre><code>php artisan make:model -m Permit</code></pre>
<p>в файле миграции:</p>
<pre><code data-language="php">public function up()
    {
        Schema::create('permits', function (Blueprint $table) {
            $table-&gt;increments('id');
            $table-&gt;string('name');
            $table-&gt;string('slug');
            $table-&gt;timestamps();
        });
    }

</code></pre>
<p>Далее:</p>
<pre><code>php artisan make:migration --create="permit_role" create_pivot_permit_role</code></pre>
<p>в файле миграции:</p>
<pre><code data-language="php">Schema::create('permit_role', function (Blueprint $table) {
            $table-&gt;increments('id');
            $table-&gt;integer('permit_id');
            $table-&gt;integer('role_id');
            $table-&gt;timestamps();
        });</code></pre>
<p>В модели Permit.php:</p>
<pre><code data-language="php">public function roles()
    {
        return $this-&gt;belongsToMany('App\Role');
    }
</code></pre>
<p>В терминале:</p>
<pre><code>php artisan migrate</code></pre>
<p>Создаем файлы конфигураций моделей для админки:</p>
<p>app/Admin/User.php :</p>
<pre><code data-language="php">&lt;?php
use App\User;
use App\Role;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(User::class, function (ModelConfiguration $model) {
    $model-&gt;setTitle('Пользователи');
    $model-&gt;onDisplay(function () {
        $display = AdminDisplay::table()-&gt;setColumns([
            AdminColumn::link('email')-&gt;setLabel('email')-&gt;setWidth('400px'),
            AdminColumn::text('first_name')-&gt;setLabel('Имя'),
            AdminColumn::text('last_name')-&gt;setLabel('Фамилия'),
        ]);
        $display-&gt;paginate(15);
        return $display;
    });
    $model-&gt;onEdit(function () {
        $form = AdminForm::panel()-&gt;addBody(
            AdminFormElement::text('first_name', 'Имя'),
            AdminFormElement::text('last_name', 'Фамилия'),
            AdminFormElement::text('facebook_id', 'Facebook аккаунт'),
            AdminFormElement::text('google_id', 'Google+ аккаунт'),
            AdminFormElement::text('vkontakte_id', 'VKontakte аккаунт'),
            AdminFormElement::text('email', 'Email')-&gt;unique()-&gt;required()-&gt;addValidationRule('email'),
            AdminFormElement::multiselect('theroles', 'Роли')-&gt;setModelForOptions(new Role())-&gt;setDisplay('name')
        );
        return $form;
    });
});</code></pre>
<p>app/Admin/Role.php :</p>
<pre><code data-language="php">&lt;?php
use App\Role;
use App\Permit;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Role::class, function (ModelConfiguration $model) {
    $model-&gt;setTitle('Роли');
// Display
    $model-&gt;onDisplay(function () {
        $display = AdminDisplay::table()-&gt;setColumns([
            AdminColumn::text('name')-&gt;setLabel('Название роли'),
            AdminColumn::text('slug')-&gt;setLabel('Роль'),
        ]);
        $display-&gt;paginate(15);
        return $display;
    });
// Create And Edit

    $model-&gt;onCreate(function () {
        $form = AdminForm::panel()-&gt;addBody(
            AdminFormElement::text('name', 'Название роли')-&gt;required()-&gt;unique(),
            AdminFormElement::text('slug', 'Роль')-&gt;required()-&gt;unique(),
            AdminFormElement::multiselect('permits', 'Права доступа')-&gt;setModelForOptions(new Permit())-&gt;setDisplay('name')
        );
        return $form;
    });

    $model-&gt;onEdit(function () {
        $form = AdminForm::panel()-&gt;addBody(
            AdminFormElement::text('name', 'Название роли')-&gt;required()-&gt;unique(),//-&gt;setReadOnly(true)
            AdminFormElement::text('slug', 'Роль')-&gt;required()-&gt;unique(),//-&gt;setReadOnly(true)
            //AdminFormElement::multiselect('permissions', 'permissions')-&gt;setModelForOptions('App\Permit')-&gt;setDisplay('name')
           //AdminFormElement::multiselect('permissions', 'permissi', Role::getPermitsOptions())-&gt;setDefaultValue(array(0 =&gt; ''))-&gt;nullable()
            AdminFormElement::multiselect('permits', 'Права доступа')-&gt;setModelForOptions(new Permit())-&gt;setDisplay('name')
        );
        return $form;
    });
    $model-&gt;disableDeleting();
});
</code></pre>
<p>app/Admin/Permit.php :</p>
<pre><code data-language="php">&lt;?php
use App\Role;
use App\Permit;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Permit::class, function (ModelConfiguration $model) {
    $model-&gt;setTitle('Права');
// Display
    $model-&gt;onDisplay(function () {
        $display = AdminDisplay::table()-&gt;setColumns([
            AdminColumn::text('name')-&gt;setLabel('Название права'),
            AdminColumn::text('slug')-&gt;setLabel('Slug'),
        ]);
        $display-&gt;paginate(15);
        return $display;
    });
// Create And Edit

    $model-&gt;onCreateAndEdit(function () {
        $form = AdminForm::panel()-&gt;addBody(
            AdminFormElement::text('name', 'Название права')-&gt;required()-&gt;unique(),
            AdminFormElement::text('slug', 'Slug')-&gt;required()-&gt;unique()
        );
        return $form;
    });
});
</code></pre>
<p>app/Admin/Post.php :</p>
<pre><code data-language="php">&lt;?php
use App\Post;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Post::class, function (ModelConfiguration $model) {
    $model-&gt;setTitle('Публикации');
// Display
    $model-&gt;onDisplay(function () {
        $display = AdminDisplay::table()-&gt;setColumns([
            AdminColumn::text('title')-&gt;setLabel('Заголовок'),
        ]);
        $display-&gt;paginate(15);
        return $display;
    });
// Create And Edit

    $model-&gt;onCreateAndEdit(function () {
        $form = AdminForm::panel()-&gt;addBody(
            AdminFormElement::text('title', 'Заголовок')-&gt;required(),
            AdminFormElement::textarea('content', 'Содержимое')-&gt;required()
        );
        return $form;
    });
})
    -&gt;addMenuPage(Post::class, 0)
    -&gt;setIcon('fa fa-pencil');</code></pre>
<p>Вносим изменения в app\Providers\AppServiceProvider.php :</p>
<pre><code data-language="php">&lt;?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use App\Role;
use App\Permit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Role::saving(function ($role) {
            $permits = Request::get('permits');
            $role-&gt;setPermissions([]);
            if (isset($permits)){
                foreach($permits as $permitid)
                {
                    $permit = Permit::find($permitid);
                    $role-&gt;addPermission($permit-&gt;slug);
                }
            }
            if ( ! $permits) return;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}</code></pre>
<p>Поправим навигацию app/Admin/navigation.php:</p>
<pre><code data-language="php">&lt;?php	
use SleepingOwl\Admin\Navigation\Page;

return [
    [
        'title' =&gt; 'Dashboard',
        'icon'  =&gt; 'fa fa-dashboard',
        'url'   =&gt; route('admin.dashboard'),
    ],

    [
        'title' =&gt; 'Пользователи',
        'icon' =&gt; 'fa fa-group',
        'pages' =&gt; [
            [
                'title' =&gt; 'Пользователи',
                'icon'  =&gt; 'fa fa-group',
                'url'   =&gt; 'admin/users',
            ],
            [
                'title' =&gt; 'Роли',
                'icon'  =&gt; 'fa fa-graduation-cap',
                'url'   =&gt; 'admin/roles',
            ],
            [
                'title' =&gt; 'Права',
                'icon'  =&gt; 'fa fa-key',
                'url'   =&gt; 'admin/permits',
            ],
        ]
    ],

    [
        'title' =&gt; 'Выйти',
        'icon'  =&gt; 'fa fa-sign-out',
        'url'   =&gt; '/logout',
    ],

];</code></pre>
<p>Создаем через админку новую роль "Модератор" со slug`ом moderator mysite.com/admin/roles/create</p>
<p>После этого создаем права для работы с публикациями:</p>
<p>Просмотр админки admin<br /> Просмотр списка публикаций admin.posts<br /> Просмотр публикации admin.posts.edit <br /> Создание публикации admin.posts.create <br /> Удаление публикации admin.posts.delete</p>
<p>В итоге, список прав у нас выглядит так:</p>
<p><img src="http://image.prntscr.com/image/eb66368731ca48adbc97c7f5036ac20e.png" /></p>
<p>Добавим для роли "Модератор" созданные права:</p>
<p><img src="http://image.prntscr.com/image/06ca8e26d2e54bcb9c4d794ed5bf7a7d.png" /></p>
<p>Выполним в терминале</p>
<pre><code>php artisan config:cache</code></pre>
<p>В значениях slug'ов прав, с помощью точечной нотации указаны роуты, в отдельных случаях - последним элементом действие - create, edit, delete. Таким образом, пользователь с ролью "Модератор", сможет выполнять указанные действия в админке, в соответствии с роутами, указанными в правах. Если пользователь с ролью "Модератор" перейдет на страницу, без назначенных прав на этот роут, от увидит примерно следующее:</p>
<p><img src="http://image.prntscr.com/image/b01e001797e646e49ce64fc843d588ea.png" /></p>
<p>С первоначальной настройкой админки закончили.</p>
<p>Приступаем к настройке заготовки под API.</p>
<p>Создадим контроллер app\Http\Controllers\ApiAuthController.php :</p>
<pre><code>php artisan make:controller ApiAuthController</code></pre>
<pre><code data-language="php">&lt;?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Redirect;
use Sentinel;
use Activation;
use Reminder;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Mail;
use Storage;
use CurlHttp;
use Dingo\Api\Routing\Helpers;

class ApiAuthController extends Controller
{
    use Helpers;

    public function authenticate(Request $request)
    {
        $credentials = $request-&gt;only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()-&gt;json(['error' =&gt; 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()-&gt;json(['error' =&gt; 'could_not_create_token'], 500);
        }
        return response()-&gt;json(compact('token'), 200);
    }

    public function refreshToken(){
        $token = JWTAuth::getToken();

        if(!$token){
            return $this-&gt;response-&gt;error("Token is invalid")-&gt;setStatusCode(401);
        }

        try {
            $refreshed_token = JWTAuth::refresh($token);
        } catch (JWTException $ex) {
            return $this-&gt;response-&gt;error("Something went wrong with token")-&gt;setStatusCode(401);
        }

        return $this-&gt;response-&gt;array(compact('refreshed_token'))-&gt;setStatusCode(200);
    }

    public function register(Request $request)
    {
        $input = $request-&gt;all();
        $messages = [
            'email.required'            =&gt; 'Email required',
            'email.email'               =&gt; 'It appears that the email entered with error',
            'password.required'         =&gt; 'Enter password',
            'password.between'          =&gt; 'Minimum password length of 8 characters',
            'password_confirm.required' =&gt; 'Enter password',
            'password_confirm.same'     =&gt; 'The entered passwords do not match',
            'password_confirm.between'  =&gt; 'Minimum password length of 8 characters',
        ];
        $validator = Validator::make($input, [
            'email' =&gt; 'required|email',
            'password' =&gt; 'required|between:8,20',
            'password_confirm' =&gt; 'required|same:password|between:8,20',
        ], $messages);

        if(count($validator-&gt;errors()-&gt;all()) &gt; 0){
            return $this-&gt;response-&gt;array(['validation_error' =&gt; 1, 'error_msgs' =&gt; $validator-&gt;errors()-&gt;all()])-&gt;setStatusCode(422);
        }

        $credentials = [ 'email' =&gt; $request-&gt;email ];
        if($user = Sentinel::findByCredentials($credentials))
        {
            return $this-&gt;response-&gt;error("This Email is already registered", 200);
        }

        if ($sentuser = Sentinel::register($input))
        {
            $activation = Activation::create($sentuser);
            $code = $activation-&gt;code;
            $sent = Mail::send('mail.account_activate_app', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m-&gt;from('noreplqy@mysite.com', 'MySite');
                $m-&gt;to($sentuser-&gt;email)-&gt;subject('Account activation');
            });
            if ($sent === 0)
            {
                return $this-&gt;response-&gt;error("Activation email is not sending", 200);
            }

            $role = Sentinel::findRoleBySlug('user');
            $role-&gt;users()-&gt;attach($sentuser);

            return $this-&gt;response-&gt;array(['success' =&gt; 1, 'msg' =&gt; 'Your account has been created. Check Email to activate.'])-&gt;setStatusCode(200);
        }
        return $this-&gt;response-&gt;error("Failed to register", 200);
    }
}
</code></pre>
<p>Создаём контроллер app\Http\Controllers\PostController.php :</p>
<pre><code>php artisan make:controller PostController</code></pre>
<pre><code data-language="php">&lt;?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Post;
use Dingo\Api\Routing\Helpers;

class PostController extends Controller
{
    use Helpers;

    public function getPosts(){
        $user = JWTAuth::parseToken()-&gt;authenticate();

        if(!$user){
            return $this-&gt;response-&gt;errorNotFound("authenticate");
        }

        $data = Post::all();

        return $this-&gt;response-&gt;array(compact('data'))-&gt;setStatusCode(200);
    }
}</code></pre>
<p>Наполняем routes\api.php :</p>
<pre><code data-language="php">
$api = app('Dingo\Api\Routing\Router');

$api-&gt;version('v1', function ($api){
    $api-&gt;post('authenticate', 'App\Http\Controllers\ApiAuthController@authenticate');
    $api-&gt;post('register', 'App\Http\Controllers\ApiAuthController@register');
});

$api-&gt;version('v1', </code></pre>

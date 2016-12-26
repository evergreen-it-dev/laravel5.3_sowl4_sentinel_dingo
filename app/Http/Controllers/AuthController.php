<?php

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
        $email = Session::get('network_user') != '' ? Session::get('network_user')->getEmail() : '';
        return view('auth.register', ['email' => $email]);
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
                'email.required'            => 'Введите email',
                'email.email'               => 'Похоже, что email введен с ошибкой',
                'password.required'         => 'Введидите пароль',
                'password.between'          => 'Минимальная длина пароля - 8 символов',
            ];
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required|between:8,100',
            ], $messages);
            if (Sentinel::authenticate($request->all()))
            {
                if(Sentinel::inRole('admin')) return redirect('admin');
                return Redirect::intended('/');
            }
            $errors = 'Неправильный логин или пароль.';
            return Redirect::back()
                ->withInput()
                ->withErrors($errors);
        }
        catch (NotActivatedException $e)
        {
            $sentuser= $e->getUser();
            $activation = Activation::create($sentuser);
            $code = $activation->code;
            $sent = Mail::send('mail.account_activate', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m->from('noreplay@mysite.com', 'MySite');
                $m->to($sentuser->email)->subject('Активация аккаунта');
            });

            if ($sent === 0)
            {
                return Redirect::to('login')
                    ->withErrors('Ошибка отправки письма активации.');
            }
            $errors = 'Ваш аккаунт не ативирован! Поищите в своем почтовом ящике письмо со ссылкой для активации (Вам отправлено повторное письмо). ';
            return view('auth.login')->withErrors($errors);
        }
        catch (ThrottlingException $e)
        {
            $delay = $e->getDelay();
            $errors = "Ваш аккаунт блокирован на {$delay} секунд.";
        }
        return Redirect::back()
            ->withInput()
            ->withErrors($errors);
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
            'email.required'            => 'Введите email',
            'email.email'               => 'Похоже, что email введен с ошибкой',
            'password.required'         => 'Введидите пароль',
            'password_confirm.required' => 'Введидите подтверждение пароля',
            'password_confirm.same'     => 'Введенные пароли не одинаковы',
            'password.between'          => 'Минимальная длина пароля - 8 символов',
        ];
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|between:8,100',
            'password_confirm' => 'required|same:password',
        ], $messages);
        $input = $request->all();

        if (Session::get('network_user') && Session::get('network_user')->id) {

            $input[Session::get('network') . '_id'] = Session::get('network_user')->id;

        }

        $credentials = [ 'email' => $request->email ];
        if($user = Sentinel::findByCredentials($credentials))
        {
            return Redirect::to('register')
                ->withErrors('Такой Email уже зарегистрирован.');
        }

        if ($sentuser = Sentinel::register($input))
        {
            $activation = Activation::create($sentuser);
            $code = $activation->code;
            $sent = Mail::send('mail.account_activate', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m->from('noreplay@mysite.com', 'MySite');
                $m->to($sentuser->email)->subject('Активация аккаунта');
            });
            if ($sent === 0)
            {
                return Redirect::to('register')
                    ->withErrors('Ошибка отправки письма активации.');
            }

            $role = Sentinel::findRoleBySlug('user');
            $role->users()->attach($sentuser);

            Session::forget('network_user');
            Session::forget('network');

            return Redirect::to('login')
                ->withSuccess('Ваш аккаунт создан. Проверьте Email для активации.')
                ->with('userId', $sentuser->getUserId());
        }
        return Redirect::to('register')
            ->withInput()
            ->withErrors('Failed to register.');
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
                ->withErrors('Неверный или просроченный код активации.');
        }

        return Redirect::to('login')
            ->withSuccess('Аккаунт активирован.');
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
            return view('auth.activate_result', ['result' => 'Invalid or expired activation code.']);
        }

        Sentinel::update($sentuser, ['email_confirmed' => 1]);

        return view('auth.activate_result', ['result' => 'Account activated.']);
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
            'email.required'            => 'Введите email',
            'email.email'               => 'Похоже, что email введен с ошибкой',
        ];
        $this->validate($request, [
            'email' => 'required|email',
        ], $messages);
        $email = $request->email;
        $sentuser = Sentinel::findByCredentials(compact('email'));
        if ( ! $sentuser)
        {
            return Redirect::back()
                ->withInput()
                ->withErrors('Пользователь с таким E-Mail в системе не найден.');
        }
        $reminder = Reminder::exists($sentuser) ?: Reminder::create($sentuser);
        $code = $reminder->code;

        $sent = Mail::send('mail.account_reminder', compact('sentuser', 'code'), function($m) use ($sentuser)
        {
            $m->from('noreplay@mysite.com', 'MySite');
            $m->to($sentuser->email)->subject('Сброс пароля');
        });
        if ($sent === 0)
        {
            return Redirect::to('reset')
                ->withErrors('Ошибка отправки email.');
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
            'password.required'         => 'Введидите новый пароль',
            'password_confirm.required' => 'Введидите подтверждение нового пароля',
            'password_confirm.same'     => 'Введенные пароли не одинаковы',
            'password.between'          => 'Минимальная длина пароля - 8 символов',
        ];
        $this->validate($request, [
            'password' => 'required|between:8,100',
            'password_confirm' => 'required|same:password',
        ], $messages);
        $user = Sentinel::findById($id);
        if ( ! $user)
        {
            return Redirect::back()
                ->withInput()
                ->withErrors('Такого пользователя не существует.');
        }
        if ( ! Reminder::complete($user, $code, $request->password))
        {
            return Redirect::to('login')
                ->withErrors('Неверный или просроченный код сброса пароля.');
        }
        return Redirect::to('login')
            ->withSuccess("Пароль сброшен.");
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
        if (Input::get('network') && in_array(Input::get('network'), $this->networks)) {
            return Socialite::with(Input::get('network'))->redirect();
        }
    }

    public function callbackSignin()
    {
        //проверяем, есть ли параметр network, и присутствует ли он в массиве доступных соц. сетей
        if (Input::get('network') && in_array(Input::get('network'), $this->networks)) {
            //получили данные пользователя из соц. сети
            $network_user = Socialite::with(Input::get('network'))->stateless()->user();

            //проверяем, есть ли в полученных данных значение email
            if ($network_user->getEmail() != '') {
                //если email получен - проверяем, есть ли в системе пользователь с таким email

                $credentials = ['email' => $network_user->getEmail()];
                $user = Sentinel::findByCredentials($credentials);

                if (!$user) {
                    //если нет в системе пользователя с таким email,
                    //проверяем есть ли в системе пользователь с таким id в конкретной соцсети

                    $credentials = [Input::get('network') . '_id' => $network_user->getId()];

                    $user = Sentinel::findByCredentials($credentials);

                } else {
                    //если есть в системе пользователь с таким email,
                    //проверяем заполнен ли id сети

                    if ($user[Input::get('network') . '_id'] == '') {

                        $credentials = [Input::get('network') . '_id' => $network_user->getId()];
                        $user = Sentinel::update($user, $credentials);

                    }
                }
            } else {
                //в полученных данных нет email
                //проверяем есть ли в системе пользователь с таким id в конкретной соцсети

                $credentials = [Input::get('network') . '_id' => $network_user->getId()];
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
                        ->withErrors('Ранее, на Вашу почту было отправлено письмо с дальнейшими инструкциями по активации аккаунта.');
                }

            }

        }

    }

}
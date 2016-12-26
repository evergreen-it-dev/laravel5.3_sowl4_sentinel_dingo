<?php

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

        $arrSlugs=str_getcsv($request->path(), '/');
        $method=Request::method();
        $user = Sentinel::check();
        if($method == 'DELETE')
        {
            $permit = $arrSlugs[0] . '.' . $arrSlugs[1] . '.delete';
            if ($user->hasAccess([$permit])) return $next($request);
            if ($user->hasAccess(['admin']))
            {
                $content = 'Для удаления объекта необходимы установленные права <b>'.  $permit .'</b> Для получения прав обратитесь к администратору';
                return Response::make(AdminSection::view($content, 'Попытка несанкционированного доступа'), 403);
            }
            return Redirect::back();
        }
        $permit=$arrSlugs[0];
        if(isset($arrSlugs[1])) $permit = $permit . '.' . $arrSlugs[1];
        if(isset($arrSlugs[2]) && ($arrSlugs[2] == "create")) $permit = $permit . '.' . $arrSlugs[2];
        if(isset($arrSlugs[3])) $permit = $permit . '.' . $arrSlugs[3];
        if ($user->hasAccess([$permit])) return $next($request);
        if ($user->hasAccess(['admin']))
        {
            $content = 'Для данного действия необходимы установленные права <b>'. $permit .'</b> Для получения прав обратитесь к администратору';
            return Response::make(AdminSection::view($content, 'Попытка несанкционированного доступа'), 403);

        }
        return Redirect::back();
    }
}
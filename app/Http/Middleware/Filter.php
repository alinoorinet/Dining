<?php

namespace App\Http\Middleware;

use App\Facades\Filtering;
use Closure;
use Illuminate\Support\Facades\Auth;

class Filter
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
        $result = Filtering::auto_filter($request);
        switch ($request->method()) {
            case 'POST':
                if($request->wantsJson()) {
                    if($result == 2)
                        return response()->json(['status' => null]);
                    $request->json()->replace($result);
                }
                else {
                    if($result == 2)
                        return redirect('/force-logout');
                    foreach ($result as $k=>$abc)
                        $request->merge([
                            $k => $abc,
                        ]);
                }
                break;
            case 'GET':
                if ($result == 1 || $result == 2)
                    return redirect('/force-logout');
        }
        return $next($request);
    }
}

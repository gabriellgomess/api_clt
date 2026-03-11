<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwaggerBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = config('l5-swagger.defaults.auth.user');
        $password = config('l5-swagger.defaults.auth.password');

        if ($request->getUser() !== $user || $request->getPassword() !== $password) {
            return new Response('', 401, ['WWW-Authenticate' => 'Basic realm="Swagger"']);
        }

        return $next($request);
    }
}

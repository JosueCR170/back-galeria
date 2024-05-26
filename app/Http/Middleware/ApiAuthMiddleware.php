<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $jwt=new JwtAuth();
        $token=$request->header('bearertoken');
        $logged=$jwt->checkToken($token);
        if($logged){
            return $next($request);
        }else{
            $response=array(
                'status'=>401,
                'message'=>'No tiene privilegios para acceso al recurso'
            );
            return response()->json($response,401);
        }
    }
}

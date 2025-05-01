<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token =  explode(" ",request()->header('Authorization'))[1];
        $info = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if(!$info){
            return send_msg('Your token is expired! Please Log in again.', false, 403);
        }
        $user = User::find($info->tokenable_id);
        $shop_id = optional($user->shop)->id;
      
        if($user->shop && optional($user->shop)->valid_till){
            if($user->shop->valid_till < Carbon::now()->toDateTimeString()){
                return send_msg('Please, Confirm your payment to login', false, 300);
            }
        }

        if(!is_null($shop_id) && (request()->header('shop_id')==null)){
            return send_msg('Unauthorized Access', false, 403);
        }

        if(!is_null($shop_id) && (request()->header('shop_id')!=null)){
            if($shop_id != request()->header('shop_id')){
                return send_msg('Unauthorized Access', false, 403);
            }
        }

        return $next($request);
    }
}

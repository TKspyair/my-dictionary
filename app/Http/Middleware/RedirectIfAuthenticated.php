<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider; #認証後のリダイレクト先
use Closure;
use Illuminate\Http\Request; #HTTPリクエスト
use Illuminate\Support\Facades\Auth; #ユーザーの認証状態
use Symfony\Component\HttpFoundation\Response; #HTTPレスポンス

#「'guest'」(エイリアス)
class RedirectIfAuthenticated
/* Middlewareクラスを継承していない理由
Laravelのミドルウェアとしての必要な条件: handle()が存在し、指定された引数（$requestと$next）を受け取ること
[例]
public function handle(Request $request, Closure $next, ...): Response
*/
{
    public function handle(Request $request, Closure $next, string ...$guards): Response 
    # handle() : Middlewarクラスのメソッド、リクエストの検証
    # $guards : web、api等の認証ガードの種類を受け取る
    {
        $guards = empty($guards) ? [null] : $guards; 

        #ユーザーが認証済みなら、語句一覧ページにリダイレクトする
        # ルーティング先の定義: my-dictionary\app\Providers\RouteServiceProvider.php
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }
        
        #ユーザーが未認証なら、未認証ユーザー用のルーティングに移行　
        return $next($request);
        #$next():PHPのクロージャ(無名関数の一種)、引数を次の処理に渡す
    }
}

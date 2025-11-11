<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

# auth(エイリアス)
class Authenticate extends Middleware
{
    # 未認証のユーザーをpages.auth.register-loginページにリダイレクトする　※認証チェック自体は親クラスのMiddlewareに定義されている
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('register-login');
    }
}

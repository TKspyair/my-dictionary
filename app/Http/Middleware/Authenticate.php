<?php
/**
 * authミドルウェアがルートに適用されているときに、ユーザーが認証済みであるか確認する
 */
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('register');
    }
}

<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

// 「app/Http/Kernel.php」 : アプリケーション全体で適用するミドルウェアや、webやapiといったミドルウェアグループの順番を定義します。
class Kernel extends HttpKernel
{
    //アプリケーションへの全てのHTTPリクエストに対して、ルートやコントローラーの処理前に必ず実行される
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class 
        \App\Http\Middleware\TrustProxies::class, // ロードバランサーなどのプロキシサーバーを経由した場合に、正しいクライアント情報を取得
        \Illuminate\Http\Middleware\HandleCors::class, // CORS（Cross-Origin Resource Sharing）ヘッダーを処理し、異なるドメインからのリクエストを許可または拒否します。
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class, // アプリケーションがメンテナンスモードのときに、メンテナンス中であることを示すレスポンスを返します。
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // POSTリクエストのサイズが設定値を超えていないか検証します
        \App\Http\Middleware\TrimStrings::class, // リクエストに含まれるすべての文字列データの先頭と末尾の空白を自動的に除去します。
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // リクエストに含まれる空の文字列（""）を自動的にnullに変換します。
    ];

    //特定のルート群に一括適用するためのミドルウェアのグループを定義
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, //クッキーの暗号化
            \Illuminate\Session\Middleware\StartSession::class, //セッションの開始
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, //バリデーションエラーの共有
            \App\Http\Middleware\VerifyCsrfToken::class, //CSRF対策
            \Illuminate\Routing\Middleware\SubstituteBindings::class, //ルート定義でモデルのインスタンスを自動的に依存性の注入(DI)をする
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    //個々のミドルウェアクラスに対して、短くて覚えやすい別名（エイリアス）を定義
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

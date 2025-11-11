<?php
/** RouteServiceProvider: アプリの起動(ブートストラップ)時に実行
 * routesディレクトリ内のルート定義ファイル(web.phpやauth.phpなど)を実行し、ルートテーブルに登録する
 */

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    # authミドルウェアなどがログイン処理後にユーザーをリダイレクトさせるデフォルトのURL(ルート)
    public const HOME = '/word-index'; //語句一覧ページ

    public function boot(): void
    {
        # APIルートに対するレート制限の設定
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        # ルート定義
        $this->routes(function () {
            # APIルートの設定
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            # Webルートの設定(web.php内に'auth'や'guest'のルート設定を含む)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}

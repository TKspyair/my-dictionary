<?php

/** web.phpの処理の流れ
 ** ルートテーブル: LaravelFWの内部に存在する、アプリに登録されたすべてのルート定義を記憶・管理しているデータ構造
 * > URI(URLのパス)、HTTPメソッド、ルーティングの処理メソッド、ルート名('login'などのファイルのエイリアス)を持つ
 * 
 * 1 登録(マッピング): (Laravelアプリの起動時に一度だけ実行)ファイル内のコードを上から順番に実行され、ルートテーブルへの登録(構築)がされる　
 * 
 * ---- 一般的にルーティングと呼ばれる動作は以下の処理のこと(HTTPリクエストごとに実行される ------
 * 
 * 2 照合(マッチング): クライアントからのHTTPリクエストが来ると、リクエストの内容(URIとHTTPメソッド)と合致するエントリを探す
 * ※エントリ: ルートテーブルを構成する要素の単位のこと、該当のURLとそれに対して行う処理を定義したもの
 * 3 実行(ディスパッチ): エントリに紐づけれている処理を実行する
 */
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

# ルートURL '/' へのリクエストの振り分け
Route::get('/', function () {
    
    # ユーザーが認証済みかどうかをチェック
    if (Auth::check()) {
        # 認証済み
        return redirect()->route('word-index');
    } else {
        # 認証なし
        return redirect()->route('register-login');
    }
});

/** 
 * require(PHP) :  (指定したファイルの存在の有無) ? (ファイルを読み込み、PHPコードを実行) : (Fatal Errorを発生させ、プログラムを停止する)
 * __DIR__(PHPのマジック定数) : _DIR_が記述されているファイルが存在するディレクトリの絶対パスに置き換える(ファイルの一つ上の階層までのパスを生成する)
 *　例: web.php(このファイル) →　 C:\xampp\htdocs\my-dictionary\routes()
 */
# 認証に関するルート定義(認証済み・未認証の切り分け)
require __DIR__ . '/auth.php';




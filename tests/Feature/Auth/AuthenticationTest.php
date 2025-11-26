<?php

# 認証機能のテスト

/** 目次
 **　未認証関連
 * 1 ログイン画面の表示ができるか
 * 2 ログイン画面で認証ができるか
 * 3 間違ったログイン情報を入力したときに、バリデーションエラーが発生するか
 * 
 ** 認証済み関連
 * 4 認証済みユーザーがログインページにアクセスしたときに、認証後ページにリダイレクトされるか
 * 5 不正なパスワードを入力した場合、認証が失敗するか
 * 6 ログアウトできるか
 */

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    # テスト実行時に、データベースをリセットするトレイト
    /** トレイト(Trait): クラス間でのメソッドの再利用を可能する仕組み
     * PHPが単一継承(一つのクラスは一つの親クラスしか継承できない)という制約の中で、多重継承を部分的に実現するためにある
     * 
     * 使い方
     * 1 インポート: use Illuminate\Foundation\Testing\RefreshDatabase;
     * 2 トレイトの使用: use RefreshDatabase;
     */
    use RefreshDatabase;

    //-----------------------------------------------------
    // 未認証関連
    //-----------------------------------------------------
    #1 ログイン画面の表示ができるか
    public function test_login_screen_can_be_rendered(): void
    {
        # 指定のURLへのGETリクエストのレスポンスを取得
        /** GETリクエストへのHTTPレスポンスの内容
         * 1 ステータスライン
         * - HTTPバージョン
         * - ステータスコード: リクエストの結果(成功:200など)
         * - テキストフレーズ: ステータスコードの説明(200:OK)
         * 
         * 2 レスポンスヘッダー
         * - Content-Type: レスポンスボディのデータ形式
         * - Content-Length: レスポンスボディのサイズ(バイト数)
         * - Cashe-Control: コンテンツのキャッシュ方法を指示
         * - Date: レスポンスの生成日時
         * - Set-Cookie: Cookie情報
         * - Location: リダイレクト先のURI
         * - etc 
         * 
         * 3 レスポンスボディ
         * - HTML: ※WEBページのリクエストの場合
         * - JSON/XML: 部分的なデータ更新に必要なデータ　※基本的にはJSON形式
         * - バイナリデータ: Javascriptファイル、CSSファイル、画像など ※2進数で保存されたデータのこと(テキストデータではない)
         */
        $response = $this->get('/');

        /**
         * assert~() : アサーションと呼ばれるテストの成否を判定するメソッド  ※コンピューター分野において、「断言する、表明する」という意味
         * assertOK(): HTTPステータスコードが「200 OK」か検証する
         * assertSeeVolt('pages.[ファイルパス]'): Livewire Voltコンポーネントが描画されているか確認
         */
        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register-login');
    }

    

    #2 ログイン画面で認証ができるか

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        # テストユーザーを作成
        $user = User::factory()->create();
    
            # ログイン情報(メアド、パスワード)を入力
        /**
         * $component: Livewire Voltコンポーネントのテストインスタンス
         * Volt::test('コンポーネント名')
         * set(第一引数, 第二引数): 第一引数に第二引数を代入する
         */
        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        /** call('メソッド名'): publicで定義されたメソッドを実行 */
        $component->call('login');
    
            # 認証エラーとリダイレクト成否の検証
        /**
         * assertHasNoErrors(): エラーが無いことを検証(無: true, 有: false)
         * assertRedirect('リダイレクト先'): 指定先にリダイレクトできているかを検証
         * ※今回はlogin()内にredirect()が含まれているため、その結果を検証している
         */
        $component
            ->assertHasNoErrors()
            ->assertRedirect(RouteServiceProvider::HOME);

        # ユーザーが認証状態になっていることを検証
        $this->assertAuthenticated();
    }

    #3 ログイン時のバリデーションエラーを検証
    public function test_login_validation_errors_are_displayed(): void
    {
        $user = User::factory()->create();

        # 間違ったログイン情報を入力
        $component = Volt::test('pages.auth.login')
            ->set('form.email', '') 
            ->set('form.password', ''); 

        $component->call('login');

        # バリデーションエラーが発生したことを検証
        $component->assertHasErrors(['form.email', 'form.password']); // メールとパスワードのエラーがあることを確認

        $component->assertNoRedirect();

        $this->assertGuest();
    }

    //-----------------------------------------------------
    // 認証済み関連
    //-----------------------------------------------------
    
    #4 認証済みユーザーがログインページにアクセスした場合にリダイレクトされるか検証
    public function test_authenticated_users_are_redirected_from_login_page(): void
    {
        // 認証済みのユーザーを作成
        $user = User::factory()->create();

        // そのユーザーとしてログインした状態をシミュレート
        $this->actingAs($user);

        // ログインページにアクセス
        $response = $this->get('/login');

        // ダッシュボードまたはホームにリダイレクトされることを検証
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    #5 不正なパスワードでユーザーが認証できないか検証
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        /** Volt::test(): Livewire Voltコンポーネントを初期化し、テスト環境でレンダリングする
         */
        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        # 認証エラーが起き、どこにもリダイレクトがされないことを検証
        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        # ユーザーが認証状態にならないことを検証
        $this->assertGuest();
    }

    #6 ログアウトできるか
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        # 作成したユーザーとしてログインしている状態とする　※テストのたびに手動でログイン処理をする必要がない
        $this->actingAs($user);

        # logout()をもつVoltコンポーネントのテストインスタンスを作成
        $component = Volt::test('pages.menu.index');

        #　ログアウトする
        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }

}

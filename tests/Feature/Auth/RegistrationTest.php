<?php

# 新規登録機能のテスト

/** 目次
 * 1 新規登録画面が表示されるか
 * 2 新規ユーザーが登録できるか
 * 3 入力値が条件を満たしていない場合、バリデーションエラーが表示されるか
 */

/**  RegistrationTest: Livewire Voltで構築されたユーザー登録機能の検証(Laravelの機能テストファイルの一つ)
 * テストではVoltコンポーネント内のロジック部分のみを検証
 * > 「pages.auth.register」は「pages.auth.register－login」から呼び出される新規登録用の子コンポーネントとして扱われるが、
 *    ロジックはpages.auth.registerにあるため指定先は変更しなくてもよい
 */

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #1 新規登録画面が表示されるか
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    #2 新規登録できるか
    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(RouteServiceProvider::HOME);

        # 認証されているか検証
        $this->assertAuthenticated();
    }

    #3 入力値が条件を満たしていない場合、バリデーションエラーが表示されるか
    public function test_register_validation_errors_are_displayed(): void
    {

        # 入力フォームに形式に合わない情報を入力
        $component = Volt::test('pages.auth.register')
            ->set('email', '') 
            ->set('password', ''); 

        $component->call('register');

        # バリデーションエラーが発生したことを検証
        $component->assertHasErrors(['email', 'password']); 

        $component->assertNoRedirect();

        $this->assertGuest();
    }
}

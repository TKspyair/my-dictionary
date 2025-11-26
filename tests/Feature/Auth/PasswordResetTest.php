<?php

# パスワードリセット機能のテスト

/** 目次
 * 1 パスワードリセット要求画面が表示されるか
 * 2 パスワードリセットリンクが送信できるか
 * 3 パスワードリセット画面が表示されるか
 * 4 パスワードがリセットできるか
 */

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #1 パスワードリセットリンクが表示されるか
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertSeeVolt('pages.auth.forgot-password')
            ->assertStatus(200);
    }

    #2 パスワードリセットリンク要求ができるか
    public function test_reset_password_link_can_be_requested(): void
    {
        # Laravelの通知システム処理を偽装する
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        # パスワードリセット通知が送信されるか検証
        Notification::assertSentTo($user, ResetPassword::class);
    }

    #3 パスワードリセットページが表示されるか
    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        # ユーザーへパスワードリセット通知が送信されたか検証
        /** Notification::assertSentTo($user, 通知ロジック, 無名関数) 
         * 第三引数の無名関数は詳細な検証を行うためのロジックを記述する
         * ※無名関数の戻り値はtrueであることが期待される → falseの場合検証失敗(戻り値がnull)となる
         * 
         */
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) { //$notification: ここではResetPasswordのインスタンス

            /** $this->get(): リンクをクリックする動作を再現する  */
            $response = $this->get('reset-password/{token}'.$notification->token);

            $response
                ->assertSeeVolt('pages.auth.reset-password')
                ->assertStatus(200);

            # trueを返すとパスワードリセット通知が送信されたとみなす
            return true;
        });
    }

    #4 有効なトークンでパスワードリセットができるか
    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $component = Volt::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'password')
                ->set('password_confirmation', 'password');

            $component->call('resetPassword');

            $component
                ->assertRedirect('/')
                ->assertHasNoErrors();

            return true;
        });
    }
}

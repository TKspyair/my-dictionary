<?php

# パスワード確認機能のテスト

/** 目次
 * 1 パスワード確認画面が表示されるか
 * 2 正しいパスワードで確認が成功するか
 * 3 間違ったパスワードで確認が失敗するか
 */

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    #1 パスワード確認画面が表示されるか
    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.confirm-password');
    }

    #2 正しいパスワードで確認が成功するか
    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('pages.auth.confirm-password')
            ->set('password', 'password');

        $component->call('confirmPassword');

        $component
            ->assertHasNoErrors()
            ->assertRedirect();
    }

    #3 間違ったパスワードで確認が失敗するか
    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('pages.auth.confirm-password')
            ->set('password', 'wrong-password');

        $component->call('confirmPassword');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();
    }
}

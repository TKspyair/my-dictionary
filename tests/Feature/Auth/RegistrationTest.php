<?php
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

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticated();
    }
}

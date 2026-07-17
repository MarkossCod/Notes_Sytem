<?php

namespace Tests\Feature;

use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_pages_require_an_active_session(): void
    {
        $this->get(route('notes.index'))->assertRedirect(route('login'));
    }

    public function test_public_registration_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->createUser('Blocked', 'user', false);

        $this->post(route('login.store'), [
            'user_name' => 'Blocked',
            'password' => 'StrongPass1!',
        ])->assertSessionHasErrors('user_name');

        $this->assertGuestSession();
    }

    public function test_repeated_login_failures_are_rate_limited(): void
    {
        $this->createUser('RateUser');
        RateLimiter::clear('rateuser|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'user_name' => 'RateUser',
                'password' => 'WrongPassword1!',
            ]);
        }

        $this->post(route('login.store'), [
            'user_name' => 'RateUser',
            'password' => 'WrongPassword1!',
        ])->assertSessionHasErrors('password');

        $this->assertStringContainsString('Muitas tentativas', session('errors')->first('password'));
    }

    public function test_successful_login_stores_role_and_updates_last_access(): void
    {
        $user = $this->createUser('Administrator', 'admin');

        $this->post(route('login.store'), [
            'user_name' => 'Administrator',
            'password' => 'StrongPass1!',
        ])->assertRedirect(route('notes.index'))
            ->assertSessionHas('user_id', $user->id)
            ->assertSessionHas('user_role', 'admin');

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_regular_user_cannot_open_user_administration(): void
    {
        $user = $this->createUser('RegularUser');

        $this->withSession(['user_name' => $user->user_name])
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_a_user_with_a_strong_password(): void
    {
        $admin = $this->createUser('Administrator', 'admin');

        $this->withSession(['user_name' => $admin->user_name])
            ->post(route('admin.users.store'), [
                'user_name' => 'NewUser',
                'password' => 'NewStrong1!',
                'password_confirmation' => 'NewStrong1!',
                'role' => 'user',
                'secret_question' => 'Qual sua cidade natal?',
                'secret_answer' => 'Betim',
            ])->assertSessionHas('success');

        $createdUser = NoteUser::where('user_name', 'NewUser')->firstOrFail();
        $this->assertTrue(Hash::check('NewStrong1!', $createdUser->password));
        $this->assertTrue(Hash::check('betim', $createdUser->secret_answer));
        $this->assertTrue($createdUser->active);
    }

    public function test_weak_password_is_rejected_by_administration(): void
    {
        $admin = $this->createUser('Administrator', 'admin');

        $this->withSession(['user_name' => $admin->user_name])
            ->post(route('admin.users.store'), [
                'user_name' => 'WeakUser',
                'password' => '1234',
                'password_confirmation' => '1234',
                'role' => 'user',
                'secret_question' => 'Pergunta',
                'secret_answer' => 'Resposta',
            ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('note_users', ['user_name' => 'WeakUser']);
    }

    public function test_administrator_can_block_another_user(): void
    {
        $admin = $this->createUser('Administrator', 'admin');
        $user = $this->createUser('UserToBlock');

        $this->withSession(['user_name' => $admin->user_name])
            ->patch(route('admin.users.update', $user), [
                'role' => 'user',
                'active' => '0',
            ])->assertSessionHas('success');

        $this->assertFalse($user->fresh()->active);
    }

    private function createUser(string $name, string $role = 'user', bool $active = true): NoteUser
    {
        return NoteUser::create([
            'user_name' => $name,
            'password' => Hash::make('StrongPass1!'),
            'secret_question' => 'Pergunta de teste',
            'secret_answer' => Hash::make('resposta'),
            'role' => $role,
            'active' => $active,
        ]);
    }

    private function assertGuestSession(): void
    {
        $this->assertNull(session('user_id'));
        $this->assertNull(session('user_name'));
    }
}

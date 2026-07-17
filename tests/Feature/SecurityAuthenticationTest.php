<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
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

    public function test_new_user_name_from_login_opens_registration(): void
    {
        $this->post(route('login.store'), [
            'user_name' => 'NewPublicUser',
            'password' => '',
        ])->assertRedirect(route('register'))
            ->assertSessionHas('pending_user', 'NewPublicUser');

        $this->get(route('register'))->assertOk();
    }

    public function test_user_can_create_a_public_account_with_a_strong_password(): void
    {
        $this->post(route('register.store'), [
            'user_name' => 'PublicUser',
            'password' => 'PublicStrong1!',
            'password_confirmation' => 'PublicStrong1!',
            'secret_question' => 'Qual sua cidade natal?',
            'secret_answer' => 'Betim',
        ])->assertRedirect(route('notes.index'))
            ->assertSessionHas('user_role', 'user');

        $user = NoteUser::where('user_name', 'PublicUser')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertTrue($user->active);
        $this->assertTrue(Hash::check('PublicStrong1!', $user->password));
    }

    public function test_public_registration_rejects_a_weak_password(): void
    {
        $this->post(route('register.store'), [
            'user_name' => 'WeakPublicUser',
            'password' => '1234',
            'password_confirmation' => '1234',
            'secret_question' => 'Pergunta',
            'secret_answer' => 'Resposta',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('note_users', ['user_name' => 'WeakPublicUser']);
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
                'user_name' => $user->user_name,
                'role' => 'user',
                'active' => '0',
            ])->assertSessionHas('success');

        $this->assertFalse($user->fresh()->active);
    }

    public function test_administrator_can_rename_a_user_and_preserve_their_data(): void
    {
        $admin = $this->createUser('Administrator', 'admin');
        $user = $this->createUser('OldName');
        Note::create(['user_name' => 'OldName', 'title' => 'Nota', 'created_day' => now()->toDateString()]);
        Category::create(['user_name' => 'OldName', 'name' => 'Categoria']);

        $this->withSession(['user_name' => $admin->user_name])
            ->patch(route('admin.users.update', $user), [
                'user_name' => 'NewName',
                'role' => 'user',
                'active' => '1',
            ])->assertSessionHas('success');

        $this->assertDatabaseHas('note_users', ['id' => $user->id, 'user_name' => 'NewName']);
        $this->assertDatabaseHas('notes', ['user_name' => 'NewName']);
        $this->assertDatabaseHas('categories', ['user_name' => 'NewName']);
    }

    public function test_administrator_dashboard_exposes_user_performance_metrics(): void
    {
        $admin = $this->createUser('Markos', 'admin');
        $user = $this->createUser('PerformanceUser');
        Note::create(['user_name' => $user->user_name, 'title' => 'Concluída', 'created_day' => now()->toDateString(), 'status' => 'concluida']);
        Note::create(['user_name' => $user->user_name, 'title' => 'Em andamento', 'created_day' => now()->toDateString(), 'status' => 'em_andamento']);
        Category::create(['user_name' => $user->user_name, 'name' => 'Trabalho']);

        $response = $this->withSession(['user_name' => $admin->user_name])
            ->get(route('admin.users.index'));

        $response->assertOk()->assertViewHas('metrics', fn (array $metrics) => $metrics['total'] === 2 && $metrics['admins'] === 1);
        $performanceUser = $response->viewData('users')->firstWhere('user_name', 'PerformanceUser');
        $this->assertSame(2, (int) $performanceUser->notes_count);
        $this->assertSame(1, (int) $performanceUser->completed_notes_count);
        $this->assertSame(1, (int) $performanceUser->categories_count);
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

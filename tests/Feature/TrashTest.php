<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        NoteUser::create([
            'user_name' => 'Markos',
            'password' => Hash::make('StrongPass1!'),
            'secret_question' => 'Pergunta',
            'secret_answer' => Hash::make('resposta'),
            'role' => 'admin',
            'active' => true,
        ]);
    }

    /** Verifies that deleting a note moves it to the authenticated user's trash. */
    public function test_deleting_a_note_moves_it_to_trash(): void
    {
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Nota de teste',
            'created_day' => now()->toDateString(),
        ]);

        $this->withSession(['user_name' => 'Markos'])
            ->delete(route('notes.destroy', $note->id))
            ->assertRedirect(route('notes.index'))
            ->assertSessionHas('success', 'A nota "Nota de teste" foi movida para a lixeira.');

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    /** Verifies that a saved note can be marked as completed. */
    public function test_user_can_mark_a_note_as_completed(): void
    {
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Concluir tarefa',
            'created_day' => now()->toDateString(),
            'status' => 'em_andamento',
        ]);

        $this->withSession(['user_name' => 'Markos'])
            ->put(route('notes.update', $note->id), [
                'title' => $note->title,
                'created_day' => $note->created_day,
                'status' => 'concluida',
                'priority' => 'media',
            ])
            ->assertRedirect(route('notes.show', $note->id));

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'status' => 'concluida',
        ]);
    }

    /** Verifies that a deleted note can be restored by its owner. */
    public function test_user_can_restore_a_deleted_note(): void
    {
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Nota restaurável',
            'created_day' => now()->toDateString(),
        ]);
        $note->delete();

        $this->withSession(['user_name' => 'Markos'])
            ->patch(route('trash.restore', $note->id))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted('notes', ['id' => $note->id]);
    }

    /** Verifies that permanent deletion cannot affect another user's note. */
    public function test_user_cannot_permanently_delete_another_users_note(): void
    {
        $note = Note::create([
            'user_name' => 'Outro usuário',
            'title' => 'Nota protegida',
            'created_day' => now()->toDateString(),
        ]);
        $note->delete();

        $this->withSession(['user_name' => 'Markos'])
            ->delete(route('trash.destroy', $note->id))
            ->assertSessionHas('error');

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    /** Verifies that the owner can permanently delete one trashed note. */
    public function test_user_can_permanently_delete_a_trashed_note(): void
    {
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Excluir definitivamente',
            'created_day' => now()->toDateString(),
        ]);
        $note->delete();

        $this->withSession(['user_name' => 'Markos'])
            ->delete(route('trash.destroy', $note->id))
            ->assertSessionHas('success', 'Nota excluída permanentemente.');

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    /** Verifies that emptying the trash permanently deletes only the owner's notes. */
    public function test_user_can_empty_their_trash(): void
    {
        $firstNote = Note::create([
            'user_name' => 'Markos',
            'title' => 'Primeira nota',
            'created_day' => now()->toDateString(),
        ]);
        $secondNote = Note::create([
            'user_name' => 'Markos',
            'title' => 'Segunda nota',
            'created_day' => now()->toDateString(),
        ]);
        $otherUsersNote = Note::create([
            'user_name' => 'Outro usuário',
            'title' => 'Nota protegida',
            'created_day' => now()->toDateString(),
        ]);
        $firstNote->delete();
        $secondNote->delete();
        $otherUsersNote->delete();

        $this->withSession(['user_name' => 'Markos'])
            ->delete(route('trash.empty'))
            ->assertSessionHas('success', 'Lixeira esvaziada com sucesso.');

        $this->assertDatabaseMissing('notes', ['id' => $firstNote->id]);
        $this->assertDatabaseMissing('notes', ['id' => $secondNote->id]);
        $this->assertSoftDeleted('notes', ['id' => $otherUsersNote->id]);
    }
}

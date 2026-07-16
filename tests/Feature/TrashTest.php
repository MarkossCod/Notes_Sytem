<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertRedirect(route('notes.index'));

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
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
}

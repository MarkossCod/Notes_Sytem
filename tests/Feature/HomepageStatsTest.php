<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomepageStatsTest extends TestCase
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

    /** Confirms that homepage counters include only the authenticated user's data. */
    public function test_homepage_displays_category_and_trash_totals(): void
    {
        Category::create(['user_name' => 'Markos', 'name' => 'Trabalho']);
        Category::create(['user_name' => 'Markos', 'name' => 'Pessoal']);
        Category::create(['user_name' => 'Outro usuário', 'name' => 'Privada']);

        $deletedNote = Note::create([
            'user_name' => 'Markos',
            'title' => 'Nota excluída',
            'created_day' => now()->toDateString(),
        ]);
        $deletedNote->delete();

        $response = $this->withSession(['user_name' => 'Markos'])
            ->get(route('notes.index'));

        $response
            ->assertOk()
            ->assertViewHas('categoriesCount', 2)
            ->assertViewHas('trashNotesCount', 1);
    }
}

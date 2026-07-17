<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageStatsTest extends TestCase
{
    use RefreshDatabase;

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

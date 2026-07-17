<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Note;
use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryMetricsTest extends TestCase
{
    use RefreshDatabase;

    /** Counts only active notes whose selected category and status match. */
    public function test_category_counts_notes_and_completed_status_correctly(): void
    {
        NoteUser::create([
            'user_name' => 'Markos',
            'password' => Hash::make('StrongPass1!'),
            'secret_question' => 'Pergunta',
            'secret_answer' => Hash::make('resposta'),
            'role' => 'admin',
            'active' => true,
        ]);

        $work = Category::create(['user_name' => 'Markos', 'name' => 'Trabalho']);
        $personal = Category::create(['user_name' => 'Markos', 'name' => 'Pessoal']);

        Note::create(['user_name' => 'Markos', 'title' => 'Finalizada', 'created_day' => now()->toDateString(), 'category_id' => $work->id, 'status' => 'concluida']);
        Note::create(['user_name' => 'Markos', 'title' => 'Aberta', 'created_day' => now()->toDateString(), 'category_id' => $work->id, 'status' => 'em_andamento']);
        Note::create(['user_name' => 'Markos', 'title' => 'Pessoal pronta', 'created_day' => now()->toDateString(), 'category_id' => $personal->id, 'status' => 'concluida']);
        $deleted = Note::create(['user_name' => 'Markos', 'title' => 'Excluída', 'created_day' => now()->toDateString(), 'category_id' => $work->id, 'status' => 'concluida']);
        $deleted->delete();

        $response = $this->withSession(['user_name' => 'Markos'])
            ->get(route('categories.index'))
            ->assertOk()
            ->assertViewHas('notesCategorized', 3)
            ->assertViewHas('notesConcluded', 2);

        $categories = $response->viewData('categories')->keyBy('name');
        $this->assertSame(2, $categories['Trabalho']->notes_count);
        $this->assertSame(1, $categories['Trabalho']->completed_notes_count);
        $this->assertSame(1, $categories['Pessoal']->completed_notes_count);
    }
}

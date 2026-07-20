<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Note;
use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanelDashboardTest extends TestCase
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

    /** Confirms that the dashboard exposes only metrics owned by the signed-in user. */
    public function test_panel_displays_only_the_current_users_metrics_and_movements(): void
    {
        Category::create(['user_name' => 'Markos', 'name' => 'Trabalho']);
        Category::create(['user_name' => 'Outra pessoa', 'name' => 'Privada']);

        Note::create([
            'user_name' => 'Markos',
            'title' => 'Nota concluída',
            'created_day' => now()->toDateString(),
            'status' => 'concluida',
        ]);
        Note::create([
            'user_name' => 'Outra pessoa',
            'title' => 'Nota externa',
            'created_day' => now()->toDateString(),
        ]);

        Activity::create([
            'user_name' => 'Markos',
            'action' => 'note_created',
            'description' => 'Criou uma nota do painel.',
        ]);
        Activity::create([
            'user_name' => 'Outra pessoa',
            'action' => 'note_created',
            'description' => 'Movimentação privada de outra conta.',
        ]);

        $response = $this->withSession(['user_name' => 'Markos'])->get(route('panel.index'));

        $response
            ->assertOk()
            ->assertViewHas('summary', fn (array $summary): bool => $summary['notes'] === 1
                && $summary['completed'] === 1
                && $summary['categories'] === 1
                && $summary['completion_rate'] === 100
                && $summary['movements'] === 1)
            ->assertSee('Criou uma nota do painel.')
            ->assertDontSee('Movimentação privada de outra conta.');
    }

    /** Confirms that important note changes are recorded for the interactive timeline. */
    public function test_creating_and_completing_a_note_records_user_movements(): void
    {
        $this->withSession(['user_name' => 'Markos'])->post(route('notes.store'), [
            'title' => 'Acompanhar atividade',
            'created_day' => now()->toDateString(),
            'status' => 'em_andamento',
        ])->assertRedirect();

        $note = Note::where('user_name', 'Markos')->firstOrFail();

        $this->withSession(['user_name' => 'Markos'])->put(route('notes.update', $note->id), [
            'title' => $note->title,
            'created_day' => $note->created_day,
            'status' => 'concluida',
            'priority' => 'media',
        ])->assertRedirect(route('notes.show', $note->id));

        $this->assertDatabaseHas('activities', ['user_name' => 'Markos', 'action' => 'note_created']);
        $this->assertDatabaseHas('activities', ['user_name' => 'Markos', 'action' => 'note_completed']);
    }

    /** Confirms unsupported periods safely fall back to the documented 30-day range. */
    public function test_panel_rejects_unsupported_period_values(): void
    {
        $this->withSession(['user_name' => 'Markos'])
            ->get(route('panel.index', ['period' => 365]))
            ->assertOk()
            ->assertViewHas('period', 30);
    }

    /** Confirms the 90-day chart is grouped into readable weekly points and exposes all formats. */
    public function test_ninety_day_chart_uses_weekly_groups_and_format_controls(): void
    {
        Activity::create([
            'user_name' => 'Markos',
            'action' => 'note_created',
            'description' => 'Movimentação do período longo.',
        ]);

        $this->withSession(['user_name' => 'Markos'])
            ->get(route('panel.index', ['period' => 90]))
            ->assertOk()
            ->assertViewHas('chartGranularity', 'weekly')
            ->assertViewHas('chart', fn (Collection $chart): bool => $chart->count() === 13
                && $chart->sum('total') === 1)
            ->assertSee('data-chart-type="bars"', false)
            ->assertSee('data-chart-type="line"', false)
            ->assertSee('data-chart-type="area"', false);
    }
}

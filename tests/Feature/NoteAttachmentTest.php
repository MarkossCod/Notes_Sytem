<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\NoteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoteAttachmentTest extends TestCase
{
    use RefreshDatabase;

    /** Cria uma conta ativa e usa um disco falso para isolar os arquivos de teste. */
    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.default' => 'local']);
        Storage::fake('local');

        NoteUser::create([
            'user_name' => 'Markos',
            'password' => Hash::make('StrongPass1!'),
            'secret_question' => 'Pergunta',
            'secret_answer' => Hash::make('resposta'),
            'role' => 'admin',
            'active' => true,
        ]);
    }

    /** Confirma que o formulário salva o arquivo e permite sua abertura pelo proprietário. */
    public function test_user_can_upload_and_view_an_attachment(): void
    {
        $response = $this->withSession(['user_name' => 'Markos'])
            ->post(route('notes.store'), [
                'title' => 'Nota com anexo',
                'created_day' => now()->toDateString(),
                'status' => 'em_andamento',
                'priority' => 'media',
                'tags' => json_encode(['Projeto']),
                'attachments' => [UploadedFile::fake()->create('manual.pdf', 12, 'application/pdf')],
            ]);

        $note = Note::where('title', 'Nota com anexo')->firstOrFail();
        $response->assertRedirect(route('notes.show', $note->id));

        $this->assertCount(1, $note->attachments);
        $this->assertSame('manual.pdf', $note->attachments[0]['name']);
        Storage::disk('local')->assertExists($note->attachments[0]['path']);

        $this->withSession(['user_name' => 'Markos'])
            ->get(route('notes.attachments.show', [$note->id, 0]))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /** Impede que uma conta abra anexos pertencentes a outra conta. */
    public function test_user_cannot_view_another_users_attachment(): void
    {
        NoteUser::create([
            'user_name' => 'Outra pessoa',
            'password' => Hash::make('StrongPass1!'),
            'secret_question' => 'Pergunta',
            'secret_answer' => Hash::make('resposta'),
            'role' => 'user',
            'active' => true,
        ]);

        Storage::disk('local')->put('notes/1/private.txt', 'privado');
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Nota privada',
            'created_day' => now()->toDateString(),
            'attachments' => [[
                'name' => 'private.txt',
                'path' => 'notes/1/private.txt',
                'disk' => 'local',
                'mime' => 'text/plain',
                'size' => 7,
            ]],
        ]);

        $this->withSession(['user_name' => 'Outra pessoa'])
            ->get(route('notes.attachments.show', [$note->id, 0]))
            ->assertNotFound();
    }

    /** Remove o arquivo físico quando a nota é apagada definitivamente da Lixeira. */
    public function test_permanent_note_deletion_removes_its_attachment(): void
    {
        Storage::disk('local')->put('notes/1/delete-me.txt', 'arquivo');
        $note = Note::create([
            'user_name' => 'Markos',
            'title' => 'Excluir anexo',
            'created_day' => now()->toDateString(),
            'attachments' => [[
                'name' => 'delete-me.txt',
                'path' => 'notes/1/delete-me.txt',
                'disk' => 'local',
                'mime' => 'text/plain',
                'size' => 7,
            ]],
        ]);
        $note->delete();

        $this->withSession(['user_name' => 'Markos'])
            ->delete(route('trash.destroy', $note->id))
            ->assertSessionHas('success');

        Storage::disk('local')->assertMissing('notes/1/delete-me.txt');
    }

    /** Garante que espaços HTML antigos não apareçam literalmente no popup. */
    public function test_homepage_preview_normalizes_html_spaces_and_line_breaks(): void
    {
        Note::create([
            'user_name' => 'Markos',
            'title' => 'Conteúdo antigo',
            'created_day' => now()->toDateString(),
            'content' => '<div>Primeira&nbsp;</div><div>Segunda&amp;nbsp;</div>',
        ]);

        $this->withSession(['user_name' => 'Markos'])
            ->get(route('notes.index'))
            ->assertOk()
            ->assertSee("Primeira\nSegunda", false)
            ->assertDontSee('&amp;nbsp;', false)
            ->assertDontSee('&nbsp;', false);
    }
}

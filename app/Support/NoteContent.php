<?php

namespace App\Support;

/**
 * Centraliza a limpeza do conteúdo digitado no editor de notas.
 *
 * O editor salva uma pequena quantidade de HTML para preservar parágrafos e
 * quebras de linha. Esta classe impede que espaços HTML, como &nbsp;, sejam
 * exibidos literalmente nas telas de visualização.
 */
final class NoteContent
{
    /** Substitui representações de espaço não separável por um espaço comum. */
    public static function normalizeHtml(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        return str_ireplace(
            ['&amp;nbsp;', '&amp;nbsp', '&nbsp;', '&nbsp', "\u{00A0}"],
            ' ',
            $content
        );
    }

    /**
     * Converte o HTML do editor em texto legível para cards e popups.
     * As tags de bloco são transformadas em quebras de linha antes da remoção.
     */
    public static function toPlainText(?string $content): string
    {
        $content = self::normalizeHtml($content) ?? '';
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content) ?? $content;
        $content = preg_replace('/<\/(div|p|li|h[1-6])\s*>/i', "\n", $content) ?? $content;
        $content = strip_tags($content);

        // Duas passagens também corrigem conteúdo antigo salvo como &amp;nbsp;.
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = self::normalizeHtml($content) ?? '';
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace("/[ \t]+\n/", "\n", $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return trim($content);
    }
}

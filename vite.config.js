import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Compila os recursos globais, a fonte da interface e as classes Tailwind.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/style.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    // Evita recompilacoes causadas pelo cache de views gerado pelo Laravel.
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

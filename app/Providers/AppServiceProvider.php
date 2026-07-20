<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /** Reserva o ponto de registro para futuros servicos da aplicacao. */
    public function register(): void
    {
        //
    }
    /** Forca URLs HTTPS quando o endereco configurado para a aplicacao e seguro. */
    public function boot(): void
    {
        if (str_contains(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }
    }
}
<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $solicitud) {
            return Limit::perMinute(5)
                ->by(Str::lower((string) $solicitud->input('email')).'|'.$solicitud->ip())
                ->response(fn (Request $solicitud, array $cabeceras) => response()->json([
                    'message' => 'Demasiados intentos de inicio de sesión. Espera un minuto.',
                ], 429, $cabeceras));
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $documento) {
                $documento->secure(SecurityScheme::http('bearer'));
            });
    }
}

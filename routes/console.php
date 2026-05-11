<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Importación automática de boletines por correo ──────────────────────────
// Los boletines del CENADA (PIMA) llegan entre las 11:00 a.m. y las 2:00 p.m.
// Se revisa el correo cada 30 minutos dentro de esa ventana, de lunes a viernes.
// El comando solo procesa correos UNSEEN, así que repetir es inocuo.
Schedule::command('simm:fetch-boletin')
    ->cron('0,30 11,12,13 * * 1-5')
    ->timezone('America/Costa_Rica')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/boletin-fetch.log'));

// Revisión extra a las 14:00 para capturar correos tardíos
Schedule::command('simm:fetch-boletin')
    ->cron('0 14 * * 1-5')
    ->timezone('America/Costa_Rica')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/boletin-fetch.log'));

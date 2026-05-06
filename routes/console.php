<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Importación automática de boletines por correo ──────────────────────────
//
// Horario oficial de envío del boletín CENADA (PIMA):
//   · Lunes, miércoles y viernes → 11:30 a.m.
//   · Martes y jueves            → 12:30 p.m.
//
// Se programan con 15 minutos de margen para darle tiempo al correo de llegar.

// Lunes (1), miércoles (3), viernes (5) a las 11:45
Schedule::command('simm:fetch-boletin')
    ->cron('45 11 * * 1,3,5')
    ->timezone('America/Costa_Rica')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/boletin-fetch.log'));

// Martes (2) y jueves (4) a las 12:45
Schedule::command('simm:fetch-boletin')
    ->cron('45 12 * * 2,4')
    ->timezone('America/Costa_Rica')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/boletin-fetch.log'));

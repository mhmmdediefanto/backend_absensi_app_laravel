<?php

use App\Http\Middleware\PreventSessionRegeneration;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->group('api-session', [
            EnsureFrontendRequestsAreStateful::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventSessionRegeneration::class,
        ]);
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\is_admin::class,
            'is_siswa' => \App\Http\Middleware\is_siswa::class,
            'is_guru' => \App\Http\Middleware\is_guru::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('temp:clean')->dailyAt('00:00');
        
        // Schedule untuk mencatat siswa yang alpa menggunakan Queue
        // Jalankan setiap hari jam 13:00 (setelah jam pulang)
        $schedule->job(new \App\Jobs\RecordAbsentStudentsJob())
            ->dailyAt('13:00')
            ->name('record-absent-students')
            ->withoutOverlapping();

        // Schedule untuk mencatat alpa di hari Sabtu (jam 12:30)
        $schedule->job(new \App\Jobs\RecordAbsentStudentsJob())
            ->saturdays()
            ->at('12:30')
            ->name('record-absent-students-saturday')
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

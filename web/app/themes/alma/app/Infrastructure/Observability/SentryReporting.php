<?php

namespace App\Infrastructure\Observability;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Sentry\Laravel\Integration;
use Sentry\SentrySdk;
use Throwable;

/**
 * Connect Sentry to every place an error can surface under Acorn. Does nothing without a DSN.
 *
 * sentry-laravel (auto-discovered by Acorn) initialises the SDK but deliberately removes the
 * SDK's own PHP error and fatal listeners, expecting Laravel's exception handler to report. Under
 * Acorn that handler only sees errors on routes Acorn serves and in the console. A fatal error on
 * a page WordPress renders, which is most pages, never reaches it: WordPress' own fatal handler
 * shows "There has been a critical error" and nothing is reported. So:
 *
 * - Acorn's exception handler reports to Sentry (Acorn routes, Livewire, console).
 * - A shutdown function reports fatal errors from every other request.
 */
class SentryReporting
{
    private const FATAL = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    public function register(): void
    {
        if (! self::enabled()) {
            return;
        }

        $handler = app(ExceptionHandler::class);

        if ($handler instanceof Handler) {
            $handler->reportable(static function (Throwable $e) {
                Integration::captureUnhandledException($e);
            });
        }

        register_shutdown_function([$this, 'captureFatal']);
    }

    public static function enabled(): bool
    {
        return filled(config('sentry.dsn')) && class_exists(Integration::class);
    }

    public function captureFatal(): void
    {
        $error = error_get_last();

        if ($error === null || ($error['type'] & self::FATAL) === 0) {
            return;
        }

        try {
            \Sentry\captureException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            SentrySdk::getCurrentHub()->getClient()?->flush(2);
        } catch (Throwable) {
            // Reporting must never add a second error to a dying request.
        }
    }
}

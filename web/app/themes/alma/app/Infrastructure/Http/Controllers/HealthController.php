<?php

namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController
{
    /**
     * Liveness: the application booted and can answer. Point uptime monitors here.
     */
    public function live(): JsonResponse
    {
        return $this->json(['status' => 'ok', 'version' => (string) config('alma.version'), 'time' => gmdate('c')]);
    }

    /**
     * Readiness: every check, with details only for authorized callers. 503 only when something
     * is critical (the database is down, cron has stalled), so a monitor can alert on status code.
     */
    public function checks(Request $request, HealthChecker $checker): JsonResponse
    {
        $results = $checker->run();
        $authorized = $this->authorized($request);
        $overall = HealthChecker::overall($results);

        $checks = [];

        foreach ($results as $name => $result) {
            $checks[$name] = $authorized
                ? ['status' => $result->status, 'summary' => $result->summary, 'details' => $result->details]
                : $result->status;
        }

        return $this->json([
            'status' => $overall,
            'version' => (string) config('alma.version'),
            'checks' => $checks,
        ], $overall === CheckResult::CRITICAL ? 503 : 200);
    }

    /**
     * The X-Health-Token header (or ?token=) matching HEALTH_TOKEN, or a logged-in administrator.
     */
    protected function authorized(Request $request): bool
    {
        $expected = (string) config('health.token');
        $given = (string) ($request->header('X-Health-Token') ?: $request->query('token', ''));

        if ($expected !== '' && $given !== '' && hash_equals($expected, $given)) {
            return true;
        }

        return function_exists('current_user_can') && current_user_can('manage_options');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status, [
            'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}

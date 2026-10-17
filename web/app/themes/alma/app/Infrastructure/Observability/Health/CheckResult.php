<?php

namespace App\Infrastructure\Observability\Health;

/**
 * The outcome of one health check. $summary and $details are only shown to authorized callers:
 * they can name hosts, counts and timings an attacker has no business knowing.
 */
final class CheckResult
{
    public const OK = 'ok';

    public const WARNING = 'warning';

    public const CRITICAL = 'critical';

    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public readonly string $status,
        public readonly string $summary,
        public readonly array $details = [],
    ) {}

    /**
     * @param  array<string, mixed>  $details
     */
    public static function ok(string $summary, array $details = []): self
    {
        return new self(self::OK, $summary, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function warning(string $summary, array $details = []): self
    {
        return new self(self::WARNING, $summary, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function critical(string $summary, array $details = []): self
    {
        return new self(self::CRITICAL, $summary, $details);
    }
}

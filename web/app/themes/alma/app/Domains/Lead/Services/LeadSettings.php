<?php

namespace App\Domains\Lead\Services;

/**
 * Lead settings, editable on Leads → Settings and overridable per environment.
 *
 * One precedence rule for every key: a value in config/leads.php (normally from .env) wins,
 * otherwise the value saved in wp-admin, otherwise the default below. A key that config pins is
 * shown locked on the settings screen, and save() leaves it alone.
 */
class LeadSettings
{
    public const OPTION = 'alma_lead_settings';

    /** Leads are kept at least this long, whatever is configured. */
    public const MINIMUM_RETENTION_DAYS = 30;

    public const KEYS = ['recipients', 'webhook_url', 'webhook_secret', 'retention_days', 'store_spam', 'blocklist', 'success_message'];

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'recipients' => array_filter([(string) get_option('admin_email')]),
            'webhook_url' => '',
            'webhook_secret' => '',
            'retention_days' => 365,
            'store_spam' => false,
            'blocklist' => [],
            'success_message' => __('Thanks! We have your message and will be in touch shortly.', 'alma'),
        ];
    }

    public function get(string $key): mixed
    {
        if ($this->lockedByConfig($key)) {
            return $this->normalise($key, config("leads.{$key}"));
        }

        $saved = get_option(self::OPTION, []);

        if (is_array($saved) && array_key_exists($key, $saved)) {
            return $this->normalise($key, $saved[$key]);
        }

        return $this->defaults()[$key] ?? null;
    }

    /**
     * Whether a notification or webhook may be sent to the destination stored under $key.
     *
     * Outside production the database is usually a copy of production's (bin/pull), so the
     * recipients and webhook URL it holds are production's: a test lead on a laptop would land in
     * the real inbox and the real CRM. There, only destinations set in this environment's own
     * .env count, unless LEAD_DELIVERY=always. LEAD_DELIVERY=never silences every environment.
     */
    public function mayDeliverTo(string $key): bool
    {
        return match (config('leads.delivery') ?: 'auto') {
            'always' => true,
            'never' => false,
            default => wp_get_environment_type() === 'production' || $this->lockedByConfig($key),
        };
    }

    /**
     * Whether config/leads.php (an .env variable) sets this key, so wp-admin cannot change it.
     */
    public function lockedByConfig(string $key): bool
    {
        $value = config("leads.{$key}");

        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * Validate and store the settings submitted from wp-admin. Keys pinned by config are skipped,
     * and an empty webhook secret keeps the saved one (the field is write-only on screen).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed> The stored settings.
     */
    public function save(array $input): array
    {
        $saved = get_option(self::OPTION, []);
        $saved = is_array($saved) ? $saved : [];

        foreach (self::KEYS as $key) {
            if ($this->lockedByConfig($key) || ! array_key_exists($key, $input)) {
                continue;
            }

            if ($key === 'webhook_secret' && trim((string) $input[$key]) === '') {
                continue;
            }

            $saved[$key] = $this->normalise($key, $input[$key]);
        }

        update_option(self::OPTION, $saved, false);

        return $saved;
    }

    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        return $this->get('recipients');
    }

    public function webhookUrl(): string
    {
        return $this->get('webhook_url');
    }

    public function webhookSecret(): string
    {
        return $this->get('webhook_secret');
    }

    public function retentionDays(): int
    {
        return $this->get('retention_days');
    }

    public function storeSpam(): bool
    {
        return $this->get('store_spam');
    }

    /**
     * @return list<string>
     */
    public function blocklist(): array
    {
        return $this->get('blocklist');
    }

    public function successMessage(): string
    {
        return $this->get('success_message');
    }

    private function normalise(string $key, mixed $value): mixed
    {
        return match ($key) {
            'recipients' => array_values(array_filter(
                array_map('sanitize_email', $this->toList($value)),
                fn (string $email) => is_email($email) !== false,
            )),
            'blocklist' => array_values(array_unique(array_map(
                fn (string $entry) => strtolower(ltrim($entry, '@')),
                $this->toList($value),
            ))),
            'webhook_url' => $this->url($value),
            'webhook_secret' => trim((string) $value),
            'retention_days' => max(self::MINIMUM_RETENTION_DAYS, (int) $value),
            'store_spam' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'success_message' => sanitize_textarea_field((string) $value),
            default => $value,
        };
    }

    /**
     * @return list<string>
     */
    private function toList(mixed $value): array
    {
        $items = is_array($value) ? $value : preg_split('/[\s,]+/', (string) $value);

        return array_values(array_filter(array_map(fn ($item) => trim((string) $item), $items ?: [])));
    }

    private function url(mixed $value): string
    {
        $url = trim((string) $value);

        return preg_match('#^https?://#i', $url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
}

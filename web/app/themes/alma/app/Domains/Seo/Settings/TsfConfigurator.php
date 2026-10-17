<?php

namespace App\Domains\Seo\Settings;

use Closure;
use The_SEO_Framework\Data\Plugin\Setup;

/**
 * Applies config/seo.php's TSF settings to The SEO Framework's site options.
 *
 * Only the listed keys are written; everything else stays as an editor left it. Unknown keys are
 * refused and reported rather than written, so a typo cannot pollute TSF's option. Running it
 * twice changes nothing the second time.
 */
class TsfConfigurator
{
    public const OPTION = 'autodescription-site-settings';

    /** @var Closure(): (array<string, mixed>|null) */
    private Closure $defaults;

    /**
     * @param  (Closure(): (array<string, mixed>|null))|null  $defaults  TSF's default options, which define the valid keys.
     *                                                                   Null when TSF is not active.
     */
    public function __construct(?Closure $defaults = null)
    {
        $this->defaults = $defaults ?? static fn (): ?array => class_exists(Setup::class)
            ? Setup::get_default_options()
            : null;
    }

    public function available(): bool
    {
        return ($this->defaults)() !== null;
    }

    /**
     * What applying the configuration would do.
     *
     * @param  array<string, mixed>|null  $settings  Defaults to config('seo.tsf.settings').
     * @return array{available: bool, changes: array<string, array{from: mixed, to: mixed}>, unknown: list<string>}
     */
    public function plan(?array $settings = null): array
    {
        $defaults = ($this->defaults)();

        if ($defaults === null) {
            return ['available' => false, 'changes' => [], 'unknown' => []];
        }

        $settings ??= (array) config('seo.tsf.settings', []);
        $current = (array) get_option(self::OPTION, []);
        $changes = [];
        $unknown = [];

        foreach ($settings as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                $unknown[] = (string) $key;

                continue;
            }

            $from = $current[$key] ?? $defaults[$key];

            if ($this->normalize($from) !== $this->normalize($value)) {
                $changes[$key] = ['from' => $from, 'to' => $value];
            }
        }

        return ['available' => true, 'changes' => $changes, 'unknown' => $unknown];
    }

    /**
     * Write the planned changes. Returns the plan that was applied.
     *
     * @param  array<string, mixed>|null  $settings
     * @return array{available: bool, changes: array<string, array{from: mixed, to: mixed}>, unknown: list<string>}
     */
    public function apply(?array $settings = null): array
    {
        $plan = $this->plan($settings);

        if ($plan['changes'] === []) {
            return $plan;
        }

        // Start from the stored option, or TSF's defaults on a site that never saved it, so keys
        // this configuration does not list keep their values.
        $current = get_option(self::OPTION);
        $option = is_array($current) ? $current : (array) ($this->defaults)();

        foreach ($plan['changes'] as $key => $change) {
            $option[$key] = $change['to'];
        }

        update_option(self::OPTION, $option, true);

        return $plan;
    }

    /**
     * TSF stores checkboxes as 1/0 and sometimes as "1"/"0"; compare by value, not by type.
     */
    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            ksort($value);

            return array_map(fn ($item) => $this->normalize($item), $value);
        }

        return is_bool($value) || is_int($value) || (is_string($value) && is_numeric($value)) ? (string) (int) $value : $value;
    }
}

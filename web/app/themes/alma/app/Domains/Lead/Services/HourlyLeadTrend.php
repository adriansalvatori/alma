<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Leads per hour of the day over a date range, so the hours campaigns underperform stand out.
 *
 * ## The site's clock
 *
 * `alma_leads.created_at` is UTC. The hours here are the site's timezone (Settings → General), the
 * clock the team reads their inbox and their ad accounts in, so 2pm on this chart is the 2pm
 * somebody is looking at. The ranges use that calendar too: "This month" starts at midnight on
 * the 1st there.
 *
 * ## Grouped in SQL by UTC hour, moved to the site's zone in PHP
 *
 * MySQL cannot do the conversion reliably. `CONVERT_TZ()` with a named zone needs the server's
 * timezone tables, which shared hosts rarely load. A fixed offset is wrong for half the year, and
 * wrong *inside* any range that crosses a DST change. So the query groups by UTC calendar hour, at
 * most 24 x 31 rows whatever the lead volume, and PHP moves each one to the site's zone.
 *
 * `SUBSTR(created_at, 1, 13)` is the hour because it is the one expression MySQL and the test
 * suite's SQLite both read as `Y-m-d H`, so the tests run the SQL production runs.
 *
 * ## Averaged per day, because the hours did not all get the same days
 *
 * Month to date includes today so far. On the 2nd at 10am, midnight to 9am have had two days and
 * the rest of the clock one, so a raw count shows the afternoon at half the morning for reasons
 * that have nothing to do with campaigns. So each hour carries `days`, the number of times it
 * started inside the range, and `average`, its leads per day. The chart is drawn from the average
 * and the busiest and quietest hours are picked by it. The raw count stays alongside for the
 * tooltip and the total. It also makes ranges comparable: 3.1 a day at 2pm this month against 4.0
 * last month means something; 40 against 120 does not.
 *
 * Ported from Remote Leverage's dashboard (WR-637).
 */
final class HourlyLeadTrend
{
    public const DEFAULT_RANGE = 'month';

    /**
     * Slug => label, in the order the filter shows them.
     *
     * "Last 7 days" is a rolling 168 hours rather than today plus six calendar days, so every
     * hour of the clock is counted exactly seven times.
     *
     * @return array<string, string>
     */
    public static function ranges(): array
    {
        return [
            'month' => __('This month', 'alma'),
            'last_month' => __('Last month', 'alma'),
            '7d' => __('Last 7 days', 'alma'),
        ];
    }

    /** A known range slug, or the default. A stale or hand-typed one shows this month. */
    public static function normalise(mixed $range): string
    {
        return is_string($range) && array_key_exists($range, self::ranges()) ? $range : self::DEFAULT_RANGE;
    }

    /** The zone the hours are read in: the site's. */
    public static function timezone(): string
    {
        $zone = function_exists('wp_timezone_string') ? wp_timezone_string() : 'UTC';

        return $zone !== '' ? $zone : 'UTC';
    }

    /** How the chart names that zone: its abbreviation right now (EDT, CET), or the offset. */
    public static function zoneLabel(): string
    {
        $zone = self::timezone();

        if ($zone === 'UTC' || preg_match('/^[+-]\d{2}:\d{2}$/', $zone)) {
            return $zone === 'UTC' ? 'UTC' : 'UTC'.$zone;
        }

        $abbreviation = CarbonImmutable::now($zone)->format('T');

        return preg_match('/^[+-]/', $abbreviation) ? $zone : $abbreviation;
    }

    /** `12am`, `1am` … `11pm`. */
    public static function hourLabel(int $hour): string
    {
        return ($hour % 12 ?: 12).($hour < 12 ? 'am' : 'pm');
    }

    /**
     * The range in the site's zone: start included, end excluded.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public static function window(string $range, ?CarbonImmutable $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone(self::timezone());

        return match (self::normalise($range)) {
            'last_month' => [$now->startOfMonth()->subMonth(), $now->startOfMonth()],
            '7d' => [$now->subDays(7), $now],
            default => [$now->startOfMonth(), $now],
        };
    }

    /**
     * Count a lead query by hour of the day over a range. The caller scopes the query.
     *
     * @param  Builder<Lead>  $leads
     * @return array{range: string, label: string, timezone: string, from: string, to: string, total: int, busiest: ?int, quietest: ?int, hours: list<array{hour: int, count: int, days: int, average: float}>}
     */
    public static function for(Builder $leads, string $range, ?CarbonImmutable $now = null): array
    {
        [$from, $to] = self::window($range, $now);
        $zone = self::timezone();

        $rows = (clone $leads)
            ->where('created_at', '>=', $from->utc()->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $to->utc()->format('Y-m-d H:i:s'))
            ->toBase()
            ->selectRaw('SUBSTR(created_at, 1, 13) AS utc_hour, COUNT(*) AS leads')
            ->groupBy('utc_hour')
            ->pluck('leads', 'utc_hour');

        $counts = array_fill(0, 24, 0);

        foreach ($rows as $utcHour => $count) {
            $counts[CarbonImmutable::parse($utcHour.':00:00', 'UTC')->setTimezone($zone)->hour] += (int) $count;
        }

        return self::shape($range, $from, $to, $counts);
    }

    /**
     * The same shape with every hour at zero, for when the leads cannot be read.
     *
     * @return array{range: string, label: string, timezone: string, from: string, to: string, total: int, busiest: ?int, quietest: ?int, hours: list<array{hour: int, count: int, days: int, average: float}>}
     */
    public static function empty(string $range, ?CarbonImmutable $now = null): array
    {
        [$from, $to] = self::window($range, $now);

        return self::shape($range, $from, $to, array_fill(0, 24, 0));
    }

    /**
     * @param  array<int, int>  $counts  Leads per local hour, 0-23.
     * @return array{range: string, label: string, timezone: string, from: string, to: string, total: int, busiest: ?int, quietest: ?int, hours: list<array{hour: int, count: int, days: int, average: float}>}
     */
    private static function shape(string $range, CarbonImmutable $from, CarbonImmutable $to, array $counts): array
    {
        $range = self::normalise($range);
        $days = self::daysPerHour($from, $to);
        $total = array_sum($counts);
        $hours = [];

        foreach ($counts as $hour => $count) {
            $hours[] = [
                'hour' => $hour,
                'count' => $count,
                'days' => $days[$hour],
                'average' => $days[$hour] > 0 ? round($count / $days[$hour], 1) : 0.0,
            ];
        }

        // Only hours the range has reached can be the quietest; a 0.0 for 11pm at 10am is not a finding.
        $reached = array_values(array_filter($hours, static fn (array $hour): bool => $hour['days'] > 0));
        $busiest = $quietest = null;

        if ($total > 0) {
            foreach ($reached as $hour) {
                if ($busiest === null || $hour['average'] > $hours[$busiest]['average']) {
                    $busiest = $hour['hour'];
                }

                if ($quietest === null || $hour['average'] < $hours[$quietest]['average']) {
                    $quietest = $hour['hour'];
                }
            }
        }

        return [
            'range' => $range,
            'label' => self::ranges()[$range],
            'timezone' => self::timezone(),
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'total' => $total,
            'busiest' => $busiest,
            'quietest' => $quietest,
            'hours' => $hours,
        ];
    }

    /**
     * How many times each local hour started inside the range.
     *
     * Walked in UTC so a DST change is counted as it happened: the autumn night has two 1ams
     * and the spring one has no 2am.
     *
     * @return array<int, int>
     */
    private static function daysPerHour(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $days = array_fill(0, 24, 0);
        $zone = self::timezone();
        $cursor = $from->utc()->startOfHour();

        if ($cursor->lt($from)) {
            $cursor = $cursor->addHour();
        }

        for (; $cursor->lt($to); $cursor = $cursor->addHour()) {
            $days[$cursor->setTimezone($zone)->hour]++;
        }

        return $days;
    }
}

<?php

namespace App\Domains\Payment\Data;

use InvalidArgumentException;
use NumberFormatter;

/**
 * An amount of money in integer minor units (cents) of an ISO 4217 currency.
 *
 * Never a float: 0.1 + 0.2 is not 0.3 in floating point, and a payment that is off by a cent
 * fails reconciliation. Parse decimal strings with fromDecimal(), which works on the digits.
 */
final readonly class Money
{
    /**
     * Currencies whose minor unit is not 1/100. Everything else has two decimals.
     */
    private const EXPONENTS = [
        'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'ISK' => 0, 'JPY' => 0, 'KMF' => 0,
        'KRW' => 0, 'PYG' => 0, 'RWF' => 0, 'UGX' => 0, 'UYI' => 0, 'VND' => 0, 'VUV' => 0,
        'XAF' => 0, 'XOF' => 0, 'XPF' => 0,
        'BHD' => 3, 'IQD' => 3, 'JOD' => 3, 'KWD' => 3, 'LYD' => 3, 'OMR' => 3, 'TND' => 3,
    ];

    public string $currency;

    public function __construct(public int $minor, string $currency)
    {
        $currency = strtoupper($currency);

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException("\"{$currency}\" is not an ISO 4217 currency code.");
        }

        $this->currency = $currency;
    }

    public static function of(int $minor, string $currency): self
    {
        return new self($minor, $currency);
    }

    /**
     * Parse "12.50" into 1250 minor units without touching floating point.
     *
     * Strict by default: more decimals than the currency has is an error rather than a silent
     * rounding. Pass $round to round half up instead (for totals computed elsewhere).
     */
    public static function fromDecimal(string|int $amount, string $currency, bool $round = false): self
    {
        $amount = trim((string) $amount);

        if (! preg_match('/^(-)?(\d+)(?:\.(\d+))?$/', $amount, $parts)) {
            throw new InvalidArgumentException("\"{$amount}\" is not a decimal amount.");
        }

        [, $sign, $whole, $fraction] = $parts + [3 => ''];
        $exponent = self::exponent($currency);
        $extra = substr($fraction, $exponent);

        if ($extra !== '' && trim($extra, '0') !== '' && ! $round) {
            throw new InvalidArgumentException("{$amount} has more decimals than {$currency} allows ({$exponent}).");
        }

        $minor = (int) ($whole.str_pad(substr($fraction, 0, $exponent), $exponent, '0'));

        if ($extra !== '' && (int) $extra[0] >= 5) {
            $minor++;
        }

        return new self($sign === '-' ? -$minor : $minor, $currency);
    }

    public static function exponent(string $currency): int
    {
        return self::EXPONENTS[strtoupper($currency)] ?? 2;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor + $other->minor, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor - $other->minor, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->minor * $factor, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->minor === $other->minor;
    }

    public function isZero(): bool
    {
        return $this->minor === 0;
    }

    public function isPositive(): bool
    {
        return $this->minor > 0;
    }

    /**
     * "1250" USD → "12.50". The exact decimal string providers usually expect.
     */
    public function toDecimal(): string
    {
        $exponent = self::exponent($this->currency);
        $digits = str_pad((string) abs($this->minor), $exponent + 1, '0', STR_PAD_LEFT);
        $whole = $exponent === 0 ? $digits : substr($digits, 0, -$exponent);
        $decimal = $exponent === 0 ? $whole : $whole.'.'.substr($digits, -$exponent);

        return ($this->minor < 0 ? '-' : '').$decimal;
    }

    /**
     * Localised for display ("$12.50", "12,50 €"). Display only: the float exists for the
     * formatter alone and never feeds back into an amount.
     */
    public function format(?string $locale = null): string
    {
        $locale ??= function_exists('determine_locale') ? determine_locale() : 'en_US';

        if (class_exists(NumberFormatter::class)) {
            $formatted = (new NumberFormatter($locale, NumberFormatter::CURRENCY))
                ->formatCurrency((float) $this->toDecimal(), $this->currency);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $this->currency.' '.$this->toDecimal();
    }

    /**
     * @return array{minor: int, currency: string}
     */
    public function toArray(): array
    {
        return ['minor' => $this->minor, 'currency' => $this->currency];
    }

    private function assertSameCurrency(self $other): void
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException("Cannot combine {$this->currency} with {$other->currency}.");
        }
    }
}

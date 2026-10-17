<?php

use App\Domains\Payment\Data\Money;

it('parses decimal strings without floating point', function (string $amount, string $currency, int $minor) {
    expect(Money::fromDecimal($amount, $currency)->minor)->toBe($minor);
})->with([
    ['12.50', 'USD', 1250],
    ['12.5', 'USD', 1250],
    ['0.10', 'EUR', 10],
    ['100', 'COP', 10000],
    ['1500', 'JPY', 1500],
    ['1.234', 'KWD', 1234],
    ['-3.20', 'USD', -320],
]);

it('refuses more decimals than the currency has unless asked to round', function () {
    expect(fn () => Money::fromDecimal('1.005', 'USD'))->toThrow(InvalidArgumentException::class)
        ->and(Money::fromDecimal('1.005', 'USD', round: true)->minor)->toBe(101)
        ->and(Money::fromDecimal('1.004', 'USD', round: true)->minor)->toBe(100)
        // Trailing zeros are not extra precision.
        ->and(Money::fromDecimal('2.500', 'USD')->minor)->toBe(250);
});

it('rejects malformed input', function (string $amount) {
    expect(fn () => Money::fromDecimal($amount, 'USD'))->toThrow(InvalidArgumentException::class);
})->with(['1,50', 'abc', '1.2.3', '', '1e3']);

it('rejects an invalid currency code', function () {
    expect(fn () => Money::of(100, 'dollars'))->toThrow(InvalidArgumentException::class);
});

it('formats back to an exact decimal string', function () {
    expect(Money::of(1250, 'usd')->toDecimal())->toBe('12.50')
        ->and(Money::of(5, 'USD')->toDecimal())->toBe('0.05')
        ->and(Money::of(-5, 'USD')->toDecimal())->toBe('-0.05')
        ->and(Money::of(1500, 'JPY')->toDecimal())->toBe('1500')
        ->and(Money::of(1234, 'KWD')->toDecimal())->toBe('1.234');
});

it('does arithmetic only within one currency', function () {
    $total = Money::of(1000, 'USD')->add(Money::of(250, 'USD'))->subtract(Money::of(50, 'USD'))->multiply(2);

    expect($total->minor)->toBe(2400)
        ->and($total->equals(Money::of(2400, 'USD')))->toBeTrue()
        ->and(fn () => Money::of(1, 'USD')->add(Money::of(1, 'EUR')))->toThrow(InvalidArgumentException::class);
});

it('formats for display', function () {
    expect(Money::of(1250, 'USD')->format('en_US'))->toBe('$12.50');
});

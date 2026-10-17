<?php

use App\Domains\Commerce\Contracts\CookieJar;
use App\Domains\Commerce\Services\FavoritesService;

function favoritesCookies(): CookieJar
{
    return new class implements CookieJar
    {
        /** @var array<string, string> */
        public array $values = [];

        public function get(string $name): ?string
        {
            return $this->values[$name] ?? null;
        }

        public function set(string $name, string $value, int $minutes): void
        {
            $this->values[$name] = $value;
        }

        public function forget(string $name): void
        {
            unset($this->values[$name]);
        }
    };
}

it('keeps a guest\'s favorites in a signed cookie', function () {
    $cookies = favoritesCookies();
    $favorites = new FavoritesService($cookies);

    $favorites->add(12);
    $favorites->add(40);

    expect($cookies->values['alma_favorites'])->toStartWith('12,40.')
        // A fresh request reads the same list back from the cookie.
        ->and((new FavoritesService($cookies))->all())->toBe([12, 40]);
});

it('treats a tampered cookie as an empty list', function (string $cookie) {
    $cookies = favoritesCookies();
    $cookies->values['alma_favorites'] = $cookie;

    expect((new FavoritesService($cookies))->all())->toBe([]);
})->with([
    'unsigned' => '1,2,3',
    'wrong signature' => '1,2,3.'.str_repeat('a', 64),
    'not integers' => 'drop table.'.hash_hmac('sha256', 'drop table', 'x'),
    'oversized' => str_repeat('1,', 1500).'1.sig',
]);

it('rejects an edited list even when the old signature is kept', function () {
    $cookies = favoritesCookies();
    (new FavoritesService($cookies))->add(12);

    $cookies->values['alma_favorites'] = '99'.strstr($cookies->values['alma_favorites'], '.');

    expect((new FavoritesService($cookies))->all())->toBe([]);
});

it('toggles and removes', function () {
    $favorites = new FavoritesService(favoritesCookies());

    expect($favorites->toggle(5))->toBeTrue()
        ->and($favorites->has(5))->toBeTrue()
        ->and($favorites->toggle(5))->toBeFalse()
        ->and($favorites->all())->toBe([]);
});

it('ignores non-positive IDs and caps the list', function () {
    config(['commerce.favorites.max_items' => 3]);
    $favorites = new FavoritesService(favoritesCookies());

    $favorites->add(0);
    $favorites->add(-4);
    foreach ([1, 2, 3, 4] as $id) {
        $favorites->add($id);
    }

    // The oldest is dropped to make room.
    expect($favorites->all())->toBe([2, 3, 4]);
});

it('keeps a logged-in user\'s favorites in user meta', function () {
    $user = $this->actingAsWordPressUser();
    $cookies = favoritesCookies();

    (new FavoritesService($cookies))->add(7);

    expect(get_user_meta($user->ID, FavoritesService::META_KEY, true))->toBe([7])
        ->and($cookies->values)->toBe([]);
});

it('moves a guest\'s favorites onto their account after they log in', function () {
    $cookies = favoritesCookies();
    (new FavoritesService($cookies))->add(3);

    $user = $this->actingAsWordPressUser();
    update_user_meta($user->ID, FavoritesService::META_KEY, [1, 3]);

    expect((new FavoritesService($cookies))->all())->toBe([1, 3])
        ->and($cookies->values)->toBe([]);
});

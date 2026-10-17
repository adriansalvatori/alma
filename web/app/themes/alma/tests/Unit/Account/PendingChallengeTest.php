<?php

use App\Domains\Account\Services\PendingChallenge;
use App\Domains\Account\Support\Cookies;

beforeEach(function () {
    $_COOKIE = [];
    $this->challenges = app(PendingChallenge::class);
    $this->user = account_user();
});

it('sets an HttpOnly, SameSite=Lax, secure cookie holding only a random token', function () {
    $pending = $this->challenges->start($this->user, true);
    $cookie = collect(app(Cookies::class)->sent)->firstWhere('name', PendingChallenge::COOKIE);

    expect($cookie['httponly'])->toBeTrue()
        ->and($cookie['samesite'])->toBe('Lax')
        ->and($cookie['secure'])->toBeTrue()
        ->and($cookie['value'])->toBe($pending->token)
        ->and($cookie['value'])->toMatch('/^[a-f0-9]{64}$/');
});

it('reads back the pending login for this browser', function () {
    $this->challenges->start($this->user, true, 'https://example.test/wp/wp-admin/');

    $pending = $this->challenges->current();

    expect($pending->userId)->toBe($this->user->ID)
        ->and($pending->remember)->toBeTrue()
        ->and($pending->redirectTo)->toBe('https://example.test/wp/wp-admin/');
});

it('has nothing for a browser without the cookie or with a forged one', function () {
    $this->challenges->start($this->user, false);

    $_COOKIE[PendingChallenge::COOKIE] = str_repeat('a', 64);
    expect($this->challenges->current())->toBeNull();

    $_COOKIE[PendingChallenge::COOKIE] = '../../etc/passwd';
    expect($this->challenges->current())->toBeNull();

    unset($_COOKIE[PendingChallenge::COOKIE]);
    expect($this->challenges->current())->toBeNull();
});

it('is gone after five wrong codes', function () {
    $this->challenges->start($this->user, false);

    foreach ([4, 3, 2, 1, 0] as $left) {
        expect($this->challenges->recordFailure($this->challenges->current()))->toBe($left);
    }

    expect($this->challenges->current())->toBeNull();
});

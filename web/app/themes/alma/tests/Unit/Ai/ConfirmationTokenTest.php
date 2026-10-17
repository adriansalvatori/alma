<?php

use App\Domains\Ai\Safety\ConfirmationToken;

beforeEach(function () {
    $this->tokens = new ConfirmationToken;
});

it('confirms exactly the call it was issued for', function () {
    $token = $this->tokens->issue('alma/delete-redirect', 7, ['id' => 3]);

    expect($this->tokens->verify($token, 'alma/delete-redirect', 7, ['id' => 3]))->toBeTrue();
});

it('ignores the order of keys in the input', function () {
    $token = $this->tokens->issue('alma/x', 7, ['b' => 2, 'a' => ['d' => 4, 'c' => 3]]);

    expect($this->tokens->verify($token, 'alma/x', 7, ['a' => ['c' => 3, 'd' => 4], 'b' => 2]))->toBeTrue();
});

it('refuses a token for other input, another ability or another user', function () {
    $token = $this->tokens->issue('alma/delete-redirect', 7, ['id' => 3]);

    expect($this->tokens->verify($token, 'alma/delete-redirect', 7, ['id' => 4]))->toBeFalse()
        ->and($this->tokens->verify($token, 'alma/other', 7, ['id' => 3]))->toBeFalse()
        ->and($this->tokens->verify($token, 'alma/delete-redirect', 8, ['id' => 3]))->toBeFalse();
});

it('expires', function () {
    $token = $this->tokens->issue('alma/x', 7, [], now: 1_000);

    expect($this->tokens->verify($token, 'alma/x', 7, [], now: 1_000 + 599))->toBeTrue()
        ->and($this->tokens->verify($token, 'alma/x', 7, [], now: 1_000 + 601))->toBeFalse();
});

it('refuses a tampered or malformed token', function (string $token) {
    expect($this->tokens->verify($token, 'alma/x', 7, []))->toBeFalse();
})->with([
    'empty' => '',
    'garbage' => 'not-a-token',
    'pushed-out expiry' => fn () => '9999999999.'.explode('.', (new ConfirmationToken)->issue('alma/x', 7, []))[1],
    'wrong signature' => fn () => (time() + 600).'.'.str_repeat('a', 64),
]);

<?php

use Livewire\Livewire;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $this->user = account_user();
    wp_set_current_user($this->user->ID);
});

it('updates the name without a password', function () {
    Livewire::test('account.profile-form')
        ->assertSet('email', 'jane@example.test')
        ->set('name', 'Jane Doe')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    expect(get_userdata($this->user->ID)->display_name)->toBe('Jane Doe');
});

it('needs the current password to change the email', function () {
    Livewire::test('account.profile-form')
        ->set('email', 'new@example.test')
        ->call('save')
        ->assertHasErrors(['currentPassword']);

    expect(get_userdata($this->user->ID)->user_email)->toBe('jane@example.test');

    Livewire::test('account.profile-form')
        ->set('email', 'new@example.test')
        ->set('currentPassword', 'correct-horse-battery-1')
        ->call('save')
        ->assertHasNoErrors();

    expect(get_userdata($this->user->ID)->user_email)->toBe('new@example.test');
});

it('will not take another account\'s email', function () {
    WordPress::user(['user_email' => 'taken@example.test']);

    Livewire::test('account.profile-form')
        ->set('email', 'taken@example.test')
        ->set('currentPassword', 'correct-horse-battery-1')
        ->call('save')
        ->assertHasErrors(['email']);
});

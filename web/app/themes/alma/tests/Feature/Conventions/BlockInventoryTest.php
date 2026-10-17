<?php

/*
 * docs/block-inventory.md is generated; a block added without regenerating it fails here (and in
 * CI). Fix: wp acorn alma:blocks:inventory
 */

it('keeps docs/block-inventory.md current', function () {
    $this->artisan('alma:blocks:inventory', ['--check' => true])->assertSuccessful();
});

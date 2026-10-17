<?php

use Illuminate\Support\Facades\Route;

Route::get('/fixture', fn () => 'web')->name('fixture.web');

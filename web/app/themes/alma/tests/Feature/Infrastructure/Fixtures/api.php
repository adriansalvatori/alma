<?php

use Illuminate\Support\Facades\Route;

Route::get('/fixture', fn () => 'api')->name('fixture.api');

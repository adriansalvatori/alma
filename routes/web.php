<?php

use Illuminate\Support\Facades\Route;

Route::get('/welcome', function () {
    wp_head();
    return view('welcome');
    wp_footer();
});
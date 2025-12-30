<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/fix-storage', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');

    if (!file_exists($link)) {
        \Illuminate\Support\Facades\File::copyDirectory($target, $link);
        return "Public storage copied successfully!";
    }

    return "Storage already exists!";
});

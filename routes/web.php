<?php

use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Support\Facades\Route;

Route::middleware(TrackVisitor::class)->controller(PublicPageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/profil', 'profile')->name('profile');
    Route::get('/paket-umrah', 'packages')->name('packages');
    Route::get('/paket-umrah/{package}', 'packageDetail')->name('packages.show');
    Route::get('/jadwal', 'schedules')->name('schedules');
    Route::get('/galeri', 'galleries')->name('galleries');
    Route::get('/kontak', 'contact')->name('contact');
});

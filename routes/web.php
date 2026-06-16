<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Support\Facades\Route;

Route::redirect('/admin/site-settings', '/admin/settings/website');
Route::redirect('/admin/site-settings/create', '/admin/settings/website');
Route::redirect('/admin/site-settings/{record}/edit', '/admin/settings/website');
Route::redirect('/admin/users', '/admin/settings/users');
Route::redirect('/admin/roles', '/admin/settings/roles');
Route::redirect('/admin/permissions', '/admin/settings/permissions');
Route::redirect('/admin/company-profiles/create', '/admin/company-profiles');
Route::redirect('/admin/company-profiles/{record}/edit', '/admin/company-profiles');

Route::middleware(TrackVisitor::class)->controller(PublicPageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/profil', 'profile')->name('profile');
    Route::get('/paket-umrah', 'packages')->name('packages');
    Route::get('/paket-umrah/{package}', 'packageDetail')->name('packages.show');
    Route::get('/jadwal', 'schedules')->name('schedules');
    Route::get('/galeri', 'galleries')->name('galleries');
    Route::get('/kontak', 'contact')->name('contact');
});

Route::middleware(TrackVisitor::class)->controller(BookingController::class)->group(function (): void {
    Route::get('/booking', 'create')->name('bookings.create');
    Route::get('/booking/paket/{package:slug}', 'create')->name('bookings.package');
    Route::post('/booking', 'store')->middleware('throttle:5,10')->name('bookings.store');
    Route::post('/booking/status', 'lookup')->middleware('throttle:10,5')->name('bookings.status.lookup');
    Route::get('/booking/{booking:public_token}', 'show')->middleware('throttle:60,1')->name('bookings.show');
});

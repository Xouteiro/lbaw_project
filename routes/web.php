<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InviteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::redirect('/', '/login');
Route::controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->name('home');
});

// User
Route::controller(UserController::class)->group(function () {
    Route::get('/user/{id}', 'show')->name('user.show');
    Route::get('/user/{id}/edit', 'edit')->name('user.edit');
    Route::post('/user/{id}/edit', 'update')->name('user.update');
    Route::put('/api/user/manage-event/{id_event}', 'manageEvent')->name('user.manage-event');
});

// Event
Route::controller(EventController::class)->group(function () {
    Route::get('/event/create', 'create')->name('event.create');
    Route::post('/event/create', 'store')->name('event.store');
    Route::get('/event/{id}', 'show')->name('event.show');
    Route::get('/event/{id}/edit', 'edit')->name('event.edit');
    Route::post('/event/{id}/edit', 'update')->name('event.update');
    Route::get('/event/{id}/delete', 'deleteDummy');
    Route::post('/event/{id}/delete', 'delete')->name('event.delete');
    Route::post('/event/{id}/join', 'joinEvent')->name('event.join');
});

// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// Event
Route::controller(EventController::class)->group(function () {
    Route::get('/events', 'index')->name('events');
    Route::get('/events/search', 'eventsSearch')->name('events.search');
    Route::get('/api/events-ajax', 'indexAjax');
});

Route::controller(InviteController::class)->group(function(){
    Route::post('/api/send-invite', 'sendInvite')->name('invite.send');
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\RequestToJoinController;
use App\Models\Poll;

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
    Route::delete('/user/{id}/delete', 'delete')->name('user.delete');
    Route::put('/api/user/manage-event/{id_event}', 'manageEvent')->name('user.manage-event');
});

// Event
Route::controller(EventController::class)->group(function () {
    Route::get('/event/create', 'create')->name('event.create');
    Route::post('/event/create', 'store')->name('event.store');
    Route::get('/event/{id}', 'show')->name('event.show');
    Route::get('/event/{id}/edit', 'edit')->name('event.edit');
    Route::post('/event/{id}/edit', 'update')->name('event.update');
    Route::get('/event/{id}/participants', 'participants')->name('event.participants');
    Route::post('/event/{id}/participants/{id_p}/remove', 'removeparticipant')->name('event.removeparticipant');
    Route::get('/event/{id}/participants/{id_p}/remove', 'removeDummy');
    Route::get('/event/{id}/delete', 'deleteDummy');
    Route::delete('/event/{id}/delete', 'delete')->name('event.delete');
    Route::post('/event/{id}/join', 'joinEvent')->name('event.join');
});

// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
    Route::get('/forget-password', 'showForgetPassword')->name('forget.password');
    Route::post('/sendEmail', 'sendEmail')->name('send.email');
    Route::get('/password-recover/{token}', 'showPasswordRecover')->name('password.recover.show');
    Route::post('/password-recover/{token}', 'recoverPassword')->name('password.recover');
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

// Invite
Route::controller(InviteController::class)->group(function(){
    Route::post('/api/send-invite', 'sendInvite')->name('invite.send');
    Route::post('/api/accept-invite', 'acceptInvite')->name('invite.accept');
});

Route::controller(RequestToJoinController::class)->group(function(){
    Route::post('/api/send-request-to-join', 'sendRequestToJoin')->name('requestToJoin.send');
    Route::post('/api/accept-request-to-join', 'acceptRequestToJoin')->name('requestToJoin.accept');
    Route::post('/api/deny-request-to-join', 'denyRequestToJoin')->name('requestToJoin.deny');
});

// Comment
Route::controller(CommentController::class)->group(function(){
    Route::post('/comment', 'store')->name('comment.store');
    Route::put('/comment/{id}/update', 'update')->name('comment.update');
    Route::delete('/comment/{id}/delete', 'delete')->name('comment.delete');
    Route::post('/api/comment/like', 'likeComment')->name('comment.like');
    Route::post('/api/comment/dislike', 'dislikeComment')->name('comment.dislike');
});

// File
Route::controller(FileController::class)->group(function () {
    Route::post('/file/upload', 'upload')->name('file.upload');
    Route::post('/file/deleteProfilePicture', 'deleteProfilePicture')->name('file.deleteProfilePicture');
    Route::post('/file/deleteEventPicture', 'deleteEventPicture')->name('file.deleteEventPicture');
});

Route::controller(PollController::class)->group(function () {
    Route::post('api/poll/store', 'store')->name('poll.store');
    Route::delete('api/poll/delete', 'delete')->name('poll.delete');
});
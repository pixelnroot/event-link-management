<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::post('/events/{event}/links', [EventController::class, 'storeLinks'])->name('events.links.store');

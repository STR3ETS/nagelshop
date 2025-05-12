<?php

use Illuminate\Support\Facades\Route;

use App\Models\Product;

use App\Http\Controllers\InlogController;
use App\Http\Controllers\ProductenController;
use App\Http\Controllers\InstellingenController;
use App\Http\Controllers\WinkelwagenController;

Route::get('/', function () { return view('welcome'); });
Route::get('/producten', function () { $producten = Product::all(); return view('producten', compact('producten')); });

Route::post('/winkelwagen/toevoegen/{product}', [WinkelwagenController::class, 'toevoegen'])->name('winkelwagen.toevoegen');
Route::get('/winkelwagen', [WinkelwagenController::class, 'index'])->name('winkelwagen.index');
Route::post('/winkelwagen/verwijderen/{id}', [WinkelwagenController::class, 'verwijderen'])->name('winkelwagen.verwijderen');
Route::post('/winkelwagen/{id}/aantal', [WinkelwagenController::class, 'updateAantal'])->name('winkelwagen.aantal');
Route::get('/winkelwagen/contact', [WinkelwagenController::class, 'toonContactForm'])->name('winkelwagen.contact');
Route::post('/winkelwagen/contact', [WinkelwagenController::class, 'contactOpslaan'])->name('winkelwagen.contactOpslaan');
Route::get('/winkelwagen/betaling', [WinkelwagenController::class, 'toonBetaling'])->name('winkelwagen.betaling');
Route::post('/winkelwagen/afronden', [WinkelwagenController::class, 'afronden'])->name('winkelwagen.afronden');
Route::get('/bestelling/bedankt/{id}', [WinkelwagenController::class, 'bedankt'])->name('winkelwagen.bedankt');


// Authenticatie
Route::get('/inloggen', [InlogController::class, 'toonFormulier'])->name('inloggen');
Route::get('/inloggen', [InlogController::class, 'toonFormulier'])->name('login');
Route::post('/inloggen', [InlogController::class, 'verwerk'])->name('inloggen.verwerk');
Route::post('/uitloggen', [InlogController::class, 'uitloggen'])->name('uitloggen');

// Beveiligd adminpaneel
Route::middleware(['auth'])->prefix('beheer')->group(function () {
    Route::get('/', fn() => view('beheer.dashboard'))->name('beheer.dashboard');

    Route::get('/producten', [ProductenController::class, 'index'])->name('beheer.producten');
    Route::get('/producten/aanmaken', [ProductenController::class, 'create'])->name('producten.aanmaken');
    Route::post('/producten', [ProductenController::class, 'store'])->name('producten.opslaan');
    Route::get('/producten/{product}/bewerken', [ProductenController::class, 'edit'])->name('producten.bewerken');
    Route::put('/producten/{product}', [ProductenController::class, 'update'])->name('producten.bijwerken');
    Route::delete('/producten/{product}', [ProductenController::class, 'destroy'])->name('producten.verwijderen');

    Route::get('/voorraad', [ProductenController::class, 'voorraad'])->name('beheer.voorraad');
    Route::post('/voorraad/bijwerken', [ProductenController::class, 'updateVoorraad'])->name('beheer.voorraad.bijwerken');

    Route::get('/instellingen', [InstellingenController::class, 'index'])->name('beheer.instellingen');
    Route::post('/instellingen', [InstellingenController::class, 'opslaanAlgemeen'])->name('instellingen.algemeen');
    Route::post('/instellingen/kortingscodes', [InstellingenController::class, 'opslaanKortingscode'])->name('instellingen.kortingscode.aanmaken');
    Route::delete('/instellingen/kortingscodes/{kortingscode}', [InstellingenController::class, 'verwijderKortingscode'])->name('instellingen.kortingscode.verwijderen');
});


//
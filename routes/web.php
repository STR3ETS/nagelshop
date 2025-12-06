<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Bestelling;

use App\Http\Controllers\InlogController;
use App\Http\Controllers\ProductenController;
use App\Http\Controllers\BestellingenController;
use App\Http\Controllers\InstellingenController;
use App\Http\Controllers\WinkelwagenController;

Route::get('/', function (Illuminate\Http\Request $request) {
    $query = Product::visible()
        ->orderBy('naam'); // ← alfabetisch

    if ($request->filled('categorie')) {
        $query->whereIn('category_id', $request->categorie);
    }

    $producten      = $query->get();
    $alleCategories = Category::all();

    return view('welcome', compact('producten', 'alleCategories'));
});

Route::get('/producten', function (Request $request) {
    $cats = collect((array) $request->input('categorie', []))->filter()->map(fn($v)=>(int)$v)->unique()->all();
    $subs = collect((array) $request->input('subcategorie', []))->filter()->map(fn($v)=>(int)$v)->unique()->all();

    $query = Product::visible()
        ->orderBy('naam'); // ← alfabetisch

    if (!empty($subs)) {
        $query->whereIn('subcategory_id', $subs);
    } elseif (!empty($cats)) {
        $query->whereIn('category_id', $cats);
    }

    $producten      = $query->get();
    $alleCategories = Category::with('subcategories')->get();

    return view('producten', compact('producten', 'alleCategories'));
})->name('producten.index');
Route::get('/producten/{product}/{slug?}', [ProductenController::class, 'show'])->name('producten.show');
Route::get('/zoek/producten', [\App\Http\Controllers\SearchController::class, 'products'])->name('search.products');
Route::get('/faq', function () { return view('veelgestelde-vragen'); });

Route::post('/winkelwagen/toevoegen/{product}', [WinkelwagenController::class, 'toevoegen'])->name('winkelwagen.toevoegen');
Route::get('/winkelwagen', [WinkelwagenController::class, 'index'])->name('winkelwagen.index');
Route::post('/winkelwagen/verwijderen/{id}', [WinkelwagenController::class, 'verwijderen'])->name('winkelwagen.verwijderen');
Route::post('/winkelwagen/{id}/aantal', [WinkelwagenController::class, 'updateAantal'])->name('winkelwagen.aantal');
Route::get('/winkelwagen/contact', [WinkelwagenController::class, 'toonContactForm'])->name('winkelwagen.contact');
Route::post('/winkelwagen/contact', [WinkelwagenController::class, 'contactOpslaan'])->name('winkelwagen.contactOpslaan');
Route::get('/winkelwagen/betaling', [WinkelwagenController::class, 'toonBetaling'])->name('winkelwagen.betaling');
Route::post('/winkelwagen/afronden', [WinkelwagenController::class, 'afronden'])->name('winkelwagen.afronden');
Route::post('/winkelwagen/kortingscode', [WinkelwagenController::class, 'kortingscodeToepassen'])->name('winkelwagen.kortingscode.toepassen');
Route::post('/winkelwagen/kortingscode/verwijderen', [WinkelwagenController::class, 'kortingscodeVerwijderen'])->name('winkelwagen.kortingscode.verwijderen');
Route::get('/bestelling/bedankt/{id}', [WinkelwagenController::class, 'bedankt'])->name('winkelwagen.bedankt');
Route::get('/betaling/voltooid/{bestelling}', [WinkelwagenController::class, 'mollieCallback'])->name('mollie.callback');
Route::post('/webhooks/mollie', [WinkelwagenController::class, 'mollieWebhook'])->name('mollie.webhook');

// Authenticatie
Route::get('/inloggen', [InlogController::class, 'toonFormulier'])->name('inloggen');
Route::get('/inloggen', [InlogController::class, 'toonFormulier'])->name('login');
Route::post('/inloggen', [InlogController::class, 'verwerk'])->name('inloggen.verwerk');
Route::post('/uitloggen', [InlogController::class, 'uitloggen'])->name('uitloggen');

// Beveiligd adminpaneel
Route::middleware(['auth'])->prefix('beheer')->group(function () {
    Route::get('/', function () {
        $now = Carbon::now();
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $dag = $now->copy()->subDays($i);
            $labels[] = $dag->translatedFormat('j M');
            $data[] = Bestelling::whereDate('created_at', $dag)->count();
        }

        $omzet30Dagen = Bestelling::where('created_at', '>=', Carbon::now()->subDays(30))->sum('totaalprijs');
        $omzet7Dagen = Bestelling::where('created_at', '>=', Carbon::now()->subDays(7))->sum('totaalprijs');
        $productenVerkocht = DB::table('bestelling_product')
            ->join('bestellingen', 'bestelling_product.bestelling_id', '=', 'bestellingen.id')
            ->whereMonth('bestellingen.created_at', Carbon::now()->month)
            ->sum('aantal');
        $openBestellingen = Bestelling::where('status', 'open')->count();

        return view('beheer.dashboard', [
            'chartLabels' => $labels,
            'chartData' => $data,
            'omzet30Dagen' => $omzet30Dagen,
            'omzet7Dagen' => $omzet7Dagen,
            'productenVerkocht' => $productenVerkocht,
            'openBestellingen' => $openBestellingen
        ]);
    })->name('beheer.dashboard');

    Route::get('/producten', [ProductenController::class, 'index'])->name('beheer.producten');
    Route::get('/producten/aanmaken', [ProductenController::class, 'create'])->name('producten.aanmaken');
    Route::post('/producten', [ProductenController::class, 'store'])->name('producten.opslaan');
    Route::get('/producten/{product}/bewerken', [ProductenController::class, 'edit'])->name('producten.bewerken');
    Route::put('/producten/{product}', [ProductenController::class, 'update'])->name('producten.bijwerken');
    Route::delete('/producten/{product}', [ProductenController::class, 'destroy'])->name('producten.verwijderen');
    Route::patch('/producten/{product}/toggle-visibility', [ProductenController::class, 'toggleVisibility'])->name('producten.toggleVisibility');
    Route::get('/api/subcategories', function (Illuminate\Http\Request $request) {
        $categoryId = (int) $request->get('category_id');
        return \App\Models\Subcategory::where('category_id', $categoryId)
            ->orderBy('naam')
            ->get(['id','naam']);
    })->name('beheer.api.subcategories');

    Route::get('/bestellingen', [BestellingenController::class, 'index'])->name('beheer.bestellingen');
    Route::get('/bestellingen/{bestelling}/inzien', [BestellingenController::class, 'inzien'])->name('bestellingen.inzien');
    Route::put('/bestellingen/{bestelling}/status', [BestellingenController::class, 'updateStatus'])->name('bestellingen.status');
    Route::put('/bestellingen/{bestelling}/verzendgegevens', [BestellingenController::class, 'updateVerzendgegevens'])->name('bestellingen.verzendgegevens');
    Route::put('/bestellingen/{bestelling}/tracktrace', [BestellingenController::class, 'updateTrackTrace'])->name('bestellingen.tracktrace');
    Route::get('/bestellingen/{bestelling}/factuur-download', [BestellingenController::class, 'downloadFactuur'])->name('bestellingen.factuur.download');

    Route::get('/voorraad', [ProductenController::class, 'voorraad'])->name('beheer.voorraad');
    Route::post('/voorraad/bijwerken', [ProductenController::class, 'updateVoorraad'])->name('beheer.voorraad.bijwerken');

    Route::get('/instellingen', [InstellingenController::class, 'index'])->name('beheer.instellingen');
    Route::post('/instellingen', [InstellingenController::class, 'opslaanAlgemeen'])->name('instellingen.algemeen');
    Route::post('/instellingen/kortingscodes', [InstellingenController::class, 'opslaanKortingscode'])->name('instellingen.kortingscode.aanmaken');
    Route::delete('/instellingen/kortingscodes/{kortingscode}', [InstellingenController::class, 'verwijderKortingscode'])->name('instellingen.kortingscode.verwijderen');
});

// Downloads: Algemene voorwaarden & Privacybeleid
Route::get('/download/algemene-voorwaarden', function () {
    $path = resource_path('download/algemene_voorwaarden.pdf');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'algemene-voorwaarden.pdf', [
        'Content-Type' => 'application/pdf',
    ]);
})->name('download.algemene_voorwaarden');

Route::get('/download/privacybeleid', function () {
    $path = resource_path('download/privacy_beleid.pdf');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'privacybeleid.pdf', [
        'Content-Type' => 'application/pdf',
    ]);
})->name('download.privacybeleid');
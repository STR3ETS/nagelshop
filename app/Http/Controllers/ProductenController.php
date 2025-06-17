<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductenController extends Controller
{
    public function index()
    {
        $producten = Product::latest()->paginate(10);
        return view('beheer.producten.index', compact('producten'));
    }

    public function create()
    {
        $categories = Category::all(); // Haal alle categorieën op
        return view('beheer.producten.aanmaken', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',
            'prijs' => 'required|numeric|min:0',
            'voorraad' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id', // ✅ toegevoegd
        ]);

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('producten', 'public');
            $data['foto'] = basename($foto);
        }

        Product::create($data);

        return redirect()->route('beheer.producten')->with('success', 'Product toegevoegd.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('beheer.producten.bewerken', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'beschrijving' => 'nullable|string',
            'prijs' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id', // ✅ toevoegen!
        ]);

        if ($request->hasFile('foto')) {
            // Oude foto verwijderen indien gewenst
            if ($product->foto && file_exists(storage_path('app/public/producten/' . $product->foto))) {
                unlink(storage_path('app/public/producten/' . $product->foto));
            }
        
            $foto = $request->file('foto')->store('producten', 'public');
            $data['foto'] = basename($foto);
        }

        $product->update($data);
        return redirect()->route('beheer.producten')->with('success', 'Product bijgewerkt.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('beheer.producten')->with('success', 'Product verwijderd.');
    }

    public function voorraad()
    {
        $producten = Product::orderBy('voorraad', 'asc')->get();
        return view('beheer.voorraad.index', compact('producten'));
    }

    public function updateVoorraad(Request $request)
    {
        $data = $request->validate([
            'voorraad' => 'required|array',
            'voorraad.*' => 'required|integer|min:0',
        ]);

        foreach ($data['voorraad'] as $productId => $aantal) {
            Product::where('id', $productId)->update(['voorraad' => $aantal]);
        }

        return back()->with('success', 'Voorraad succesvol bijgewerkt.');
    }
}

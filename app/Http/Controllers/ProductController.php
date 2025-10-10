<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
                return $query->where('productName', 'like', "%{$search}%")
                             ->orWhere('genericName', 'like', "%{$search}%")
                             ->orWhere('productWeight', 'like', "%{$search}%")
                             ->orWhere('dosageForm', 'like', "%{$search}%")
                             ->orWhere('category', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('products.index', compact('products', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'productName' => 'required|string|max:255',
            'genericName' => 'nullable|string|max:255',
            'productWeight' => 'nullable|string|max:100',
            'dosageForm' => 'required|in:Tablet,Capsule,Syrup,Injection,Cream,Ointment,Drops',
            'category' => 'required|in:Antibiotic,Vitamins,Prescription,Analgesic',
            'description' => 'nullable|string',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    // ✅ Update product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'productName' => 'required|string|max:255',
            'genericName' => 'nullable|string|max:255',
            'productWeight' => 'nullable|string|max:100',
            'dosageForm' => 'required|in:Tablet,Capsule,Syrup,Injection,Cream,Ointment,Drops',
            'category' => 'required|in:Antibiotic,Vitamins,Prescription,Analgesic',
            'description' => 'nullable|string',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // ✅ Delete product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}

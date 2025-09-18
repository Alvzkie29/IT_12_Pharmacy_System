<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplierID'   => 'required|exists:suppliers,supplierID',
            'productName'  => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'category'     => 'required|in:Antibiotic,Vitamins,Prescription,Analgesic',
            'description'  => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Product added successfully!');
    }
}

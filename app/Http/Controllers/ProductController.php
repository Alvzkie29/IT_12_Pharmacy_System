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

        $products = Product::with('supplier')
            ->when($search, function ($query, $search) {
                return $query->where('productName', 'like', "%{$search}%")
                            ->orWhere('genericName', 'like', "%{$search}%") 
                            ->orWhere('productWeight', 'like', "%{$search}%") 
                            ->orWhere('dosageForm', 'like', "%{$search}%")   
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhereHas('supplier', function ($q) use ($search) {
                                $q->where('supplierName', 'like', "%{$search}%");
                            });
            })
            ->paginate(10)
            ->appends(['search' => $search]);

        $suppliers = Supplier::all();

        return view('products.index', compact('products', 'suppliers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplierID' => 'required|exists:suppliers,supplierID',
            'productName' => 'required|string|max:255',
            'genericName' => 'required|string|max:255',   // ✅ new validation
            'productWeight' => 'required|string|max:100', // ✅ new validation
            'dosageForm' => 'required|in:Tablet,Capsule,Syrup,Injection,Cream,Ointment,Drops', // ✅ enforce enum
            'category' => 'required|in:Antibiotic,Vitamins,Prescription,Analgesic', // ✅ enforce enum
            'description' => 'nullable|string',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }
}

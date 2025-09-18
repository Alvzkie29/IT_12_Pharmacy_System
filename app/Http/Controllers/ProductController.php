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
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhereHas('supplier', function ($q) use ($search) {
                                $q->where('supplierName', 'like', "%{$search}%");
                            });
            })
            ->paginate(10) // show 10 per page
            ->appends(['search' => $search]); // keep search query in pagination links

        $suppliers = Supplier::all();

        return view('products.index', compact('products', 'suppliers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplierID' => 'required|exists:suppliers,supplierID',
            'productName' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }
}

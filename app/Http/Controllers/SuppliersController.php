<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    /**
     * Display a listing of suppliers with optional search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliers = Supplier::when($search, function ($query, $search) {
                return $query
                    ->where('supplierName', 'like', "%$search%")
                    ->orWhere('contactInfo', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%");
            })
            ->orderBy('supplierName')
            ->paginate(10);

        $suppliers->appends($request->only('search'));

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplierName' => 'required|string|max:255',
            'contactInfo'  => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
        ]);

        Supplier::create([
            'supplierName' => $request->supplierName,
            'contactInfo'  => $request->contactInfo,
            'address'      => $request->address,
            'is_active'    => true, // Suppliers are active by default
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplierName' => 'required|string|max:255',
            'contactInfo'  => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
        ]);

        $supplier->update([
            'supplierName' => $request->supplierName,
            'contactInfo'  => $request->contactInfo,
            'address'      => $request->address,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Deactivate (soft-remove) a supplier.
     */
    public function deactivate($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = false;
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deactivated.');
    }

    /**
     * Activate a previously inactive supplier.
     */
    public function activate($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = true;
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'Supplier activated.');
    }
}

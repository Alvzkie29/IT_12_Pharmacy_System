<?php

namespace App\Http\Controllers;

use App\Models\Suppliers;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    /**
     * Display a listing of suppliers with optional search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliers = Suppliers::where('is_active', true)
            ->when($search, function ($query, $search) {
                return $query->where('supplierName', 'like', "%$search%");
            })
            ->orderBy('supplierName')
            ->paginate(10); 

        // Keep the search query in pagination links
        $suppliers->appends($request->only('search'));

        return view('suppliers.index', compact('suppliers'));
    }
    
    /**
     * Display a listing of deactivated suppliers.
     */
    public function deactivatedList(Request $request)
    {
        $search = $request->input('search');

        $suppliers = Suppliers::where('is_active', false)
            ->when($search, function ($query, $search) {
                return $query->where('supplierName', 'like', "%$search%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        // Keep the search query in pagination links
        $suppliers->appends($request->only('search'));

        return view('suppliers.deactivated', compact('suppliers'));
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

        Suppliers::create([
            'supplierName' => $request->supplierName,
            'contactInfo'  => $request->contactInfo,
            'address'      => $request->address,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Suppliers::findOrFail($id);

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

    public function deactivate($id)
    {
        $supplier = Suppliers::findOrFail($id);
        $supplier->is_active = false;
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deactivated.');
    }

    /**
     * Activate a previously inactive supplier.
     */
    public function activate($id)
    {
        $supplier = Suppliers::findOrFail($id);
        $supplier->is_active = true;
        $supplier->save();

        return redirect()->route('suppliers.index')->with('success', 'Supplier activated.');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy($id)
    {
        $supplier = Suppliers::findOrFail($id);
        $supplier->delete(); // soft delete -> archived

        return redirect()->route('suppliers.index')->with('success', 'Supplier archived successfully.');
    }
    
    /**
     * Restore a deactivated supplier.
     */
    public function restore($id)
    {
        $supplier = Suppliers::findOrFail($id);
        $supplier->is_active = true;
        $supplier->save();

        return redirect()->route('suppliers.deactivated')->with('success', 'Supplier restored successfully.');
    }
}

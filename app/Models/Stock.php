<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $primaryKey = 'stockID';

    protected $fillable = [
        'supplierID',
        'productID',
        'employeeID',
        'type',
        'reason',
        'purchase_price',
        'selling_price',
        'package_total_cost',
        'quantity',
        'availability',
        'batchNo',
        'expiryDate',
        'movementDate',
    ];

    protected $casts = [
        'movementDate' => 'datetime',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'package_total_cost' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function getStatusBadgeAttribute()
    {
        if ($this->type === 'IN') {
            return '<span class="badge bg-success">In</span>';
        } elseif ($this->reason === 'expired') {
            return '<span class="badge bg-danger">Expired</span>';
        } elseif ($this->reason === 'damaged') {
            return '<span class="badge bg-warning">Damaged</span>';
        } elseif ($this->reason === 'pullout') {
            return '<span class="badge bg-info">Pulled Out</span>';
        } elseif ($this->type === 'OUT') {
            return '<span class="badge bg-secondary">Out</span>';
        }
        return '<span class="badge bg-dark">Unknown</span>';
    }

    // 💡 New helper for profit
    public function getProfitAttribute()
    {
        return ($this->selling_price - $this->purchase_price) * $this->quantity;
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'stockID');
    }

    // Calculate available quantity for a specific stock record
    public function getAvailableQuantityAttribute()
    {
        if ($this->type !== 'IN') {
            return 0; // Only IN records can have available quantity
        }

        // Get total quantity from this stock-in record
        $totalIn = $this->quantity;

        // Get total quantity sold from this specific stock batch
        $totalSold = Stock::where('productID', $this->productID)
            ->where('batchNo', $this->batchNo)
            ->where('type', 'OUT')
            ->where('reason', 'sold')
            ->sum('quantity');

        // Get total quantity pulled out from this specific stock batch
        $totalPulledOut = Stock::where('productID', $this->productID)
            ->where('batchNo', $this->batchNo)
            ->where('type', 'OUT')
            ->where('reason', 'expired')
            ->sum('quantity');

        // Get total quantity expired from this specific stock batch
        $totalExpired = Stock::where('productID', $this->productID)
            ->where('batchNo', $this->batchNo)
            ->where('type', 'OUT')
            ->where('reason', 'expired')
            ->sum('quantity');

        // Get total quantity pulled out (other reasons) from this specific stock batch
        $totalOtherOut = Stock::where('productID', $this->productID)
            ->where('batchNo', $this->batchNo)
            ->where('type', 'OUT')
            ->where('reason', '!=', 'sold')
            ->where('reason', '!=', 'expired')
            ->sum('quantity');

        $available = $totalIn - $totalSold - $totalPulledOut - $totalExpired - $totalOtherOut;
        
        return max(0, $available); // Never return negative
    }

    // Static method to get available stock for sales
    public static function getAvailableStock()
    {
        return self::with('product')
            ->where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now())
            ->get()
            ->filter(function ($stock) {
                return $stock->available_quantity > 0;
            });
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    use HasFactory;

    protected $primaryKey = 'batchID';

    // Fillable attributes for mass-assignment
    protected $fillable = [
        'productID',
        'batchNo',
        'expiryDate',
        'purchase_price',
        'selling_price',
        'availability',
    ];

    /**
     * The product this batch belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    /**
     * Stock movements associated with this batch.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'batchID', 'batchID');
    }

    /**
     * Scope only available batches.
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability', true);
    }

    /**
     * Set the expiry date as a Carbon instance automatically (optional).
     */
    protected $casts = [
        'expiryDate' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'availability' => 'boolean',
    ];
}

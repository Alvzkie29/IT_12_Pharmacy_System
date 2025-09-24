<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'productID';

    protected $fillable = [
        'supplierID',
        'productName',
        'genericName',     // ✅ added
        'productWeight',   // ✅ added
        'dosageForm',      // ✅ added
        'price',
        'category',
        'description',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierID');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'productID');
    }
}

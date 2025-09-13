<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transactionID';

    protected $fillable = [
        'saleID',
        'productID',
        'quantity',
        'unitPrice',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleID', 'saleID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    public function stockMovements()
    {
        return $this->hasMany(Stock::class, 'transactionID', 'transactionID');
    }
}

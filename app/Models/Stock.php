<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $primaryKey = 'stockID';

    protected $fillable = [
        'productID',
        'transactionID',
        'type',
        'quantity',
        'isAvailable',
        'batchNo',
        'expiryDate',
        'movementDate',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactionID', 'transactionID');
    }
}

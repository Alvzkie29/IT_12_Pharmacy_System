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
        'stockID',
        'quantity',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleID');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stockID');
    }
}


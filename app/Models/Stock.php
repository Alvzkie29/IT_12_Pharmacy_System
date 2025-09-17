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
        'employeeID',
        'type',
        'price',
        'quantity',
        'availability',
        'batchNo',
        'expiryDate',
        'movementDate',
    ];

    // Relationships
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $primaryKey = 'saleID';

    protected $fillable = [
        'employeeID',
        'cash_received',
        'change_given',
        'totalAmount',
        'isDiscounted',
        'subtotal',
        'discountAmount',
        'saleDate',
    ];

    protected $casts = [
        'cash_received' => 'decimal:2',
        'change_given' => 'decimal:2',
        'totalAmount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discountAmount' => 'decimal:2',
        'isDiscounted' => 'boolean',
        'saleDate' => 'datetime',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'saleID');
    }
}

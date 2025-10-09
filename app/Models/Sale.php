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

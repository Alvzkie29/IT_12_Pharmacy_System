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


    public function getStatusBadgeAttribute()
    {
        if ($this->type === 'IN') {
            return '<span class="badge bg-success">In</span>';
        } elseif ($this->type === 'OUT') {
            return '<span class="badge bg-danger">Out</span>';
        }

        return '<span class="badge bg-secondary">N/A</span>';
    }

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

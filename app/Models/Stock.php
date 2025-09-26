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
        'reason',
        'purchase_price',
        'selling_price',
        'quantity',
        'availability',
        'batchNo',
        'expiryDate',
        'movementDate',
    ];

    protected $casts = [
        'movementDate' => 'datetime',
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


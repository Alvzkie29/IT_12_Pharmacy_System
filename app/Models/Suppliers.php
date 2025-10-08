<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    use HasFactory;

    protected $primaryKey = 'supplierID';

    // Add 'is_active' to fillable fields to allow mass assignment
    protected $fillable = [
        'supplierName',
        'contactInfo',
        'address',
        'is_active',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'supplierID', 'supplierID');
    }

    // Scope for active suppliers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for inactive suppliers
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}

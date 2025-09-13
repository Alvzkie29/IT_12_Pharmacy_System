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
        'name',
        'price',
        'category',
        'description',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierID', 'supplierID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'productID', 'productID');
    }

    public function stock()
    {
        return $this->hasMany(Stock::class, 'productID', 'productID');
    }
}

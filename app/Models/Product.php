<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'productID';

    protected $fillable = [
        'productName',
        'genericName',     
        'productWeight',  
        'dosageForm',      
        'price',
        'category',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'productID');
    }
}

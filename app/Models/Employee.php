<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employeeID';

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'email',
        'password',
        'role',
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class, 'employeeID', 'employeeID');
    }
}

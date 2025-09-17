<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory;

    protected $primaryKey = 'employeeID'; 

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class, 'employeeID', 'employeeID');
    }

       public function stocks()
    {
        return $this->hasMany(Stock::class, 'employeeID');
    }
}

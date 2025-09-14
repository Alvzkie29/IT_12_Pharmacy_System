<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'employeeID'; // since your migration uses this

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'email',
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
}

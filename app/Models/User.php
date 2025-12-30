<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
class User extends Authenticatable
{    use HasApiTokens, Notifiable;    // <-- ADD HasApiTokens here

    protected $table = "users";

    protected $fillable = [
        'name','profile','email','phone','provider','provider_id','password',
        'email_verified_at','phone_verified_at','role_id','remember_token'
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Role;

class AdminUser extends Model
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'username', 'email', 'password'
    ];

    public function role() {
        return $this->hasOne(Role::class);
    }
}

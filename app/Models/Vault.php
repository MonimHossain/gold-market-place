<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vault extends Model
{
    use HasFactory;
    protected $table = 'vaults';

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}

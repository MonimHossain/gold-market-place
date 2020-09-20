<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultDelivery extends Model
{
    use HasFactory;
    protected $table = 'vault_deliveries';
    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nichestore extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'stores_id',
        'niche_id'
    ];


     protected $table = 'niche_stores';
}

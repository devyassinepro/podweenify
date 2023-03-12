<?php

namespace App\Models;
use App\Models\stores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niche extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'user_id'
    ];


    public function stores(){
        return $this->belongsToMany(stores::class);
    }
     protected $table = 'niches';
}

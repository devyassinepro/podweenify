<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Jenssegers\Mongodb\Eloquent\Model;


class Apistatus extends Model
{

    // protected $connection = 'mongodb';
    // protected $collection = 'apistatuses';
    
    use HasFactory;
    protected $fillable = [
        'id',
        'store',
        'status',
    ];
     protected $table = 'apistatuses';



}

<?php

namespace App\Models;

use App\Models\Niche;
use App\Models\Product;
use Carbon\Carbon;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Jenssegers\Mongodb\Eloquent\Model;


class stores extends Model
{

    // protected $connection = 'mongodb';
    // protected $collection = 'stores';

    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'url',
        'status',
        'revenue',
        'city',
        'country',
        'currency',
        'shopifydomain',
        'sales',
        'allproducts',
        'user_id'
    ];
    public function niches(){
        return $this->belongsToMany(Niche::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
        
    }
    public function sales()
    {
        return $this->hasMany(Sales::class);
        
    }
    public function todaysales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->format('Y-m-d'));
    }

    public function yesterdaysales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(1)->format('Y-m-d'));
    }

    public function day3sales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(2)->format('Y-m-d'));
    }

    public function day4sales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(3)->format('Y-m-d'));
    }

    public function day5sales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(4)->format('Y-m-d'));
    }

    public function day6sales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(5)->format('Y-m-d'));
    }
    public function day7sales()
    {
        return $this->hasMany(Sales::class)->where('created_at', '=', Carbon::now()->subDays(6)->format('Y-m-d'));
    }


    protected $table = 'stores';

    
}

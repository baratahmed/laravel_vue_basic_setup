<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function scopeActive($query)
    {
        $query->where('status', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function products()
    {
        return $this->hasMany(Product::class)->where('status', true);
    }
}

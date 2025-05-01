<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    protected $with = ['mall'];

    public function mall(){
        return $this->belongsTo(Mall::class);
    }

    public function shop_type(){
        return $this->belongsTo(ShopType::class);
    }

}

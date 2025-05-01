<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function divisions()
    {
        return $this->hasMany(Division::class,'id','division_id');
    }

    public function zilas()
    {
        return $this->hasMany(Zila::class,'id','zila_id');
    }

    public function upazilas()
    {
        return $this->hasMany(Upazila::class,'id','upazila_id');
    }

    public function unions()
    {
        return $this->hasMany(Union::class,'id','union_id');
    }

}

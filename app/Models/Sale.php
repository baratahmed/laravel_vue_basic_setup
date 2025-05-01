<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function sale_details() {
        return $this->hasMany(SaleDetail::class, 'sale_id', 'id');
    }

    public function sale_payments() {
        return $this->hasMany(SalePayment::class, 'sale_id', 'id');
    }
}

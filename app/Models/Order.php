<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function master(){
        return $this->belongsTo(User::class,'master_id','id');
    }

    public function order_items() {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function client_notes() {
        return $this->hasMany(ClientNote::class, 'order_id', 'id');
    }
    public function master_notes() {
        return $this->hasMany(MasterNote::class, 'order_id', 'id');
    }
}

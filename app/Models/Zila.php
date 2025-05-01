<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zila extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }
}

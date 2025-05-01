<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\Zila;
use Illuminate\Http\Request;


class AddressController extends Controller
{
    public function fetchDivisions()
    {
        return Division::select('id','name')->get();        
    }

    public function fetchZilas($division_id)
    {
        return Zila::select('id','name')->where('division_id',$division_id)->get();        
    }

    public function fetchUpaZilas($zila_id)
    {
        return Upazila::select('id','name')->where('zila_id',$zila_id)->get();        
    }

    public function fetchUnions($upazila_id)
    {
        return Union::select('id','name')->where('upazila_id',$upazila_id)->get();        
    }

}

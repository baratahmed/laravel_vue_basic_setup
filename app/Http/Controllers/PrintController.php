<?php

namespace App\Http\Controllers;

use App\Models\ClientNote;
use App\Models\MasterNote;
use App\Models\Order;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printOrder($id){
        $order = Order::with('order_items')
                        ->with('customer')
                        ->with('master')
                        ->with('shop')
                        ->find($id);
        $c_notes = ClientNote::where('order_id',$order->id)->where('customer_id',$order->customer_id)->select('id','note')->get();
        $m_notes = MasterNote::where('order_id',$order->id)->where('master_id',$order->master_id)->select('id','note')->get();
        return view('print.order', compact('order','c_notes','m_notes'));
    }
}

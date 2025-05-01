<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\ClientNote;
use App\Models\MasterNote;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class OrderController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function($request,$next){
            $this->user = Auth::guard('sanctum')->user();
            return $next($request);
        });
    }

    public function index()
    {
        if(is_null($this->user) || !$this->user->can('order.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $orders = Order::query()
                ->with('customer:id,name,phone,email,address')
                ->with('master:id,name,phone,email')
                ->when((request('from') && request('to')), function($q){
                    $from = Carbon::parse(request('from'))->addHours(6)->toDateString();
                    $to = Carbon::parse(request('to'))->addHours(6)->toDateString();
                    return $q->whereBetween('date',[$from, $to]);
                })
                ->when((request('shop_id')), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        // return $request->all();

        if(is_null($this->user) || !$this->user->can('order.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == null){
            return send_msg('Please, Select a shop',false, 200);
        }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->customer == null){
            return send_msg('Please, Select a customer',false, 200);
        }
        if($request->master == null){
            return send_msg('Please, Select a master',false, 200);
        }
        if($request->order_items[0]['item'] == null){
            return send_msg('Please, Select an item',false, 200);
        }
        $request->validate([
            'user_id' => 'required',
            'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $order = new Order();
            $order->user_id = $request->user_id;
            $order->shop_id = $request->shop_id;
            $order->customer_id = $request->customer['id'];
            $order->master_id = $request->master['id'];
            $order->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $order->trial_date = Carbon::parse($request->trial_date)->addHours(6)->toDateString();
            $order->delivery_date = Carbon::parse($request->delivery_date)->addHours(6)->toDateString();
            $order->sub_total = $request->sub_total;
            $order->discount = $request->discount ?? 0;
            $order->grand_total = $request->grand_total;
            $order->paid = $request->paid ?? 0;
            $order->due = $request->due;
            if($request->due > 0){
                $order->payment_status = 'DUE';
            }else{
                $order->payment_status = 'PAID';
            }
            $order->order_status = 'PROCESSING';
            $order->save();
            
            foreach ($request->order_items as $key => $item) {
                $order_item = new OrderItem();
                $order_item->order_id = $order->id;
                $order_item->item_id = $item['item']['id'];

                $order_item->long = $item['long']+$item['frac_long'];
                $order_item->cheast = $item['cheast']+$item['frac_cheast'];
                $order_item->belly = $item['belly']+$item['frac_belly'];
                $order_item->hip = $item['hip']+$item['frac_hip'];
                $order_item->shoulder = $item['shoulder']+$item['frac_shoulder'];
                $order_item->hand_long_full = $item['hand_long_full']+$item['frac_hand_long_full'];
                $order_item->kop = $item['kop']+$item['frac_kop'];
                $order_item->throat = $item['throat']+$item['frac_throat'];
                $order_item->total_loose = $item['total_loose']+$item['frac_total_loose'];
                $order_item->enclosure = $item['enclosure']+$item['frac_enclosure'];
                $order_item->front_cheast = $item['front_cheast']+$item['frac_front_cheast'];
                $order_item->front_belly = $item['front_belly']+$item['frac_front_belly'];
                $order_item->front_hip = $item['front_hip']+$item['frac_front_hip'];
                $order_item->wrap = $item['wrap']+$item['frac_wrap'];
                $order_item->arm = $item['arm']+$item['frac_arm'];
                $order_item->kop_arm = $item['kop_arm']+$item['frac_kop_arm'];

                $order_item->unit_price = $item['unit_price'];
                $order_item->qty = $item['qty'];
                $order_item->net_price = $item['net_price'];
                $order_item->save();
            }

            if($request->client_notes){
                foreach ($request->client_notes as $key => $note) {
                    $c_note = new ClientNote();
                    $c_note->order_id = $order->id;
                    $c_note->customer_id = $order->customer_id;
                    $num = ($note['num']!=null || $note['num']!='') ? $note['num'] : '';
                    $frac = ($note['frac']!=null || $note['frac']!='') ? $note['frac'] : '';
                    $c_note->note = $num . $frac .' '.$note['note'];
                    $c_note->save();
                }
            }
            if($request->master_notes){
                foreach ($request->master_notes as $key => $note) {
                    $c_note = new MasterNote();
                    $c_note->order_id = $order->id;
                    $c_note->master_id = $order->master_id;
                    $c_note->note = $note['note'];
                    $c_note->save();
                }
            }

            DB::commit();
            return send_msg("Order Created Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   
        if(is_null($this->user) || !$this->user->can('order.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Order::with('customer:id,name,phone,email,address')
                    ->with('master:id,name,phone,email')
                    ->with('order_items')
                    ->with('client_notes')
                    ->with('master_notes')
                    ->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('order.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->customer == null){
            return send_msg('Please, Select a customer',false, 200);
        }
        if($request->order_items[0]['product'] == null){
            return send_msg('Please, Select at least 1 product',false, 200);
        }
        $request->validate([
            'order_id' => 'required',
            'user_id' => 'required',
            'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $order = Order::find($request->order_id);
            $order->user_id = $request->user_id;
            $order->customer_id = $request->customer['id'];
            $order->shop_id = $request->shop_id;
            $order->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $order->sub_total = $request->sub_total;
            $order->discount = $request->discount ?? 0;
            $order->grand_total = $request->grand_total;
            $order->paid = $request->paid ?? 0;
            $order->due = $request->due;
            if($request->due > 0){
                $order->payment_status = 'DUE';
            }else{
                $order->payment_status = 'PAID';
            }
            $order->is_return = $request->is_return;
            $order->save();
   
            foreach ($request->order_items as $key => $item) {
                if($item['order_details_id'] != null){
                    $purchase_item = OrderItem::find($item['order_details_id']);
                    $purchase_item->purchase_id = $order->id;
                    $purchase_item->product_id = $item['product']['id'];
                    $purchase_item->unit_price = $item['unit_price'];
                    $purchase_item->qty = $item['qty'];
                    $purchase_item->net_price = $item['net_price'];
                    $purchase_item->save();

                    $product = Product::find($item['product']['id']);
                    $product->latest_cost_price = $item['unit_price'];
                    $product->stock = $product->stock + $item['qty'] - $purchase_item->qty;
                    $product->save();
                }else{
                    $purchase_item = new OrderItem();
                    $purchase_item->purchase_id = $order->id;
                    $purchase_item->product_id = $item['product']['id'];
                    $purchase_item->unit_price = $item['unit_price'];
                    $purchase_item->qty = $item['qty'];
                    $purchase_item->net_price = $item['net_price'];
                    $purchase_item->save();

                    $product = Product::find($item['product']['id']);
                    $product->latest_cost_price = $item['unit_price'];
                    $product->stock = $product->stock + $item['qty'];
                    $product->save();

                }
            }


            DB::commit();
            return send_msg("Order Updated Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }


    public function destroy(Order $order)
    {
        if(is_null($this->user) || !$this->user->can('order.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        DB::beginTransaction();
        try {
            foreach ($order->order_details as $key => $value) {
                $product = Product::find($value->product_id);
                $product->stock += $value->qty;
                $product->save();
            }

            $order->delete();
            $order->order_details()->delete();
            DB::commit();  
            return send_msg('Delete Success', true, 200);    

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('order.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        
        DB::beginTransaction();
        try {

            $orders = Order::whereIn('id',request('ids'))->get();
            foreach ($orders as $order) {

                foreach ($order->order_details as $key => $value) {
                    $product = Product::find($value->product_id);
                    $product->stock += $value->qty;
                    $product->save();
                }
                
                $order->delete();
                $order->order_details()->delete();
                DB::commit();
            }
            return send_msg('Delete Success', true, 200); 

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }

    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class PurchaseController extends Controller
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
        if(is_null($this->user) || !$this->user->can('purchase.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $purchases = Purchase::query()
                ->with('supplier:id,name,phone,email,shop_name,address')
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
        return PurchaseResource::collection($purchases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        if(is_null($this->user) || !$this->user->can('purchase.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        // if($request->shop_id == null){
        //     return send_msg('Please, Select a shop',false, 200);
        // }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->supplier == null){
            return send_msg('Please, Select a supplier',false, 200);
        }
        if($request->purchase_items[0]['product'] == null){
            return send_msg('Please, Select at least 1 product',false, 200);
        }
        $request->validate([
            'user_id' => 'required',
            // 'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $purchase = new Purchase();
            $purchase->user_id = $request->user_id;
            $purchase->supplier_id = $request->supplier['id'];
            $purchase->shop_id = $request->shop_id;
            $purchase->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $purchase->sub_total = $request->sub_total;
            $purchase->discount = $request->discount ?? 0;
            $purchase->grand_total = $request->grand_total;
            $purchase->paid = $request->paid ?? 0;
            $purchase->due = $request->due;
            if($request->due > 0){
                $purchase->payment_status = 'DUE';
            }else{
                $purchase->payment_status = 'PAID';
            }
            $purchase->is_return = $request->is_return;
            $purchase->save();

            foreach ($request->purchase_items as $key => $item) {
                $purchase_item = new PurchaseDetail();
                $purchase_item->purchase_id = $purchase->id;
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

            $purchase_payment = new PurchasePayment();
            $purchase_payment->purchase_id = $purchase->id;
            $purchase_payment->amount = $request->paid;
            $purchase_payment->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $purchase_payment->payment_method = $request->payment_method;
            $purchase_payment->save();

            DB::commit();
            return send_msg("Purchase Created Successfully!", true, 200);

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
        if(is_null($this->user) || !$this->user->can('purchase.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Purchase::with('supplier:id,name,phone,email,shop_name,address')
                        ->with('purchase_details')
                        ->with('purchase_payments')
                        ->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('purchase.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->supplier == null){
            return send_msg('Please, Select a supplier',false, 200);
        }
        if($request->purchase_items[0]['product'] == null){
            return send_msg('Please, Select at least 1 product',false, 200);
        }
        $request->validate([
            'purchase_id' => 'required',
            'user_id' => 'required',
            'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $purchase = Purchase::find($request->purchase_id);
            $purchase->user_id = $request->user_id;
            $purchase->supplier_id = $request->supplier['id'];
            $purchase->shop_id = $request->shop_id;
            $purchase->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $purchase->sub_total = $request->sub_total;
            $purchase->discount = $request->discount ?? 0;
            $purchase->grand_total = $request->grand_total;
            $purchase->paid = $request->paid ?? 0;
            $purchase->due = $request->due;
            if($request->due > 0){
                $purchase->payment_status = 'DUE';
            }else{
                $purchase->payment_status = 'PAID';
            }
            $purchase->is_return = $request->is_return;
            $purchase->save();
   
            foreach ($request->purchase_items as $key => $item) {
                if($item['purchase_details_id'] != null){
                    $purchase_item = PurchaseDetail::find($item['purchase_details_id']);
                    $purchase_item->purchase_id = $purchase->id;
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
                    $purchase_item = new PurchaseDetail();
                    $purchase_item->purchase_id = $purchase->id;
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


            foreach ($request->purchase_payments as $key => $item) {
                if($item['purchase_payments_id'] != null){
                    $purchase_payment = PurchasePayment::find($item['purchase_payments_id']);
                    $purchase_payment->amount = $item['amount'];
                    $purchase_payment->date = strlen($item['date']) <= 12 ? $item['date'] : Carbon::parse($item['date'])->addHours(6)->toDateString();
                    $purchase_payment->payment_method = $item['payment_method'];
                    $purchase_payment->save();
                }else{
                    $purchase_payment = new PurchasePayment();
                    $purchase_payment->purchase_id = $purchase->id;
                    $purchase_payment->amount = $item['amount'];
                    $purchase_payment->date = strlen($item['date']) <= 12 ? $item['date'] : Carbon::parse($item['date'])->addHours(6)->toDateString();
                    $purchase_payment->payment_method = $item['payment_method'];
                    $purchase_payment->save();
                }
            }
    

            DB::commit();
            return send_msg("Purchase Updated Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }


    public function destroy(Purchase $purchase)
    {
        if(is_null($this->user) || !$this->user->can('purchase.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        DB::beginTransaction();
        try {
            
            foreach ($purchase->purchase_details as $key => $value) {
                $product = Product::find($value->product_id);
                $product->stock -= $value->qty;
                $product->save();
            }
            
            $purchase->delete();
            $purchase->purchase_details()->delete();
            $purchase->purchase_payments()->delete();
            DB::commit();  
            return send_msg('Delete Success', true, 200);    

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('purchase.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        
        DB::beginTransaction();
        try {

            $purchases = Purchase::whereIn('id',request('ids'))->get();
            foreach ($purchases as $purchase) {

                foreach ($purchase->purchase_details as $key => $value) {
                    $product = Product::find($value->product_id);
                    $product->stock -= $value->qty;
                    $product->save();
                }
                
                $purchase->delete();
                $purchase->purchase_details()->delete();
                $purchase->purchase_payments()->delete();
                DB::commit();
            }
            return send_msg('Delete Success', true, 200); 

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }

    }
}

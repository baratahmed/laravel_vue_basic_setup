<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class SaleController extends Controller
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
        if(is_null($this->user) || !$this->user->can('sale.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $sales = Sale::query()
                ->with('customer:id,name,phone,email,address')
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
        return SaleResource::collection($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        if(is_null($this->user) || !$this->user->can('sale.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        // if($request->shop_id == null){
        //     return send_msg('Please, Select a shop',false, 200);
        // }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->customer == null){
            return send_msg('Please, Select a customer',false, 200);
        }
        if($request->sale_items[0]['product'] == null){
            return send_msg('Please, Select at least 1 product',false, 200);
        }
        $request->validate([
            'user_id' => 'required',
            // 'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $sale = new Sale();
            $sale->user_id = $request->user_id;
            $sale->customer_id = $request->customer['id'];
            $sale->shop_id = $request->shop_id;
            $sale->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $sale->sub_total = $request->sub_total;
            $sale->discount = $request->discount ?? 0;
            $sale->grand_total = $request->grand_total;
            $sale->paid = $request->paid ?? 0;
            $sale->due = $request->due;
            if($request->due > 0){
                $sale->payment_status = 'DUE';
            }else{
                $sale->payment_status = 'PAID';
            }
            $sale->is_return = $request->is_return;
            $sale->save();
            
            foreach ($request->sale_items as $key => $item) {
                $sale_item = new SaleDetail();
                $sale_item->sale_id = $sale->id;
                $sale_item->product_id = $item['product']['id'];
                $sale_item->unit_price = $item['unit_price'];
                $sale_item->qty = $item['qty'];
                $sale_item->net_price = $item['net_price'];
                $sale_item->save();

                $product = Product::find($item['product']['id']);
                $product->latest_sale_price = $item['unit_price'];
                $product->stock = $product->stock - $item['qty'];
                $product->save();


                // Main Operaion Occurs

                $purchase_details = PurchaseDetail::where('product_id',$product->id)
                                                    ->where('qty','!=','sold_count')
                                                    ->get();

                $item_count = $item['qty'];

                foreach ($purchase_details as $key => $purchase_detail) {
         
                    $diff = $purchase_detail->qty - $purchase_detail->sold_count;
                    if($diff >= $item_count){
                        $purchase_detail->sold_count += $item_count;
                        $purchase_detail->save();
                        $sale_item->net_cost_price += $purchase_detail->unit_price * $item_count;
                        $sale_item->save();
                        break;
                    }else{
                        $purchase_detail->sold_count += $diff;
                        $purchase_detail->save();
                        $sale_item->net_cost_price += $purchase_detail->unit_price * $diff;
                        $sale_item->save();
                        $item_count -= $diff;
                    }
                }
            }

                // Main Operaion Ends

            $sale_payment = new SalePayment();
            $sale_payment->sale_id = $sale->id;
            $sale_payment->amount = $request->paid;
            $sale_payment->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $sale_payment->payment_method = $request->payment_method;
            $sale_payment->save();

            DB::commit();
            return send_msg("Sale Created Successfully!", true, 200);

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
        if(is_null($this->user) || !$this->user->can('sale.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Sale::with('customer:id,name,phone,email,address')
                        ->with('sale_details')
                        ->with('sale_payments')
                        ->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('sale.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->due < 0){
            return send_msg('Due Field should not be negative!',false, 200);
        }
        if($request->customer == null){
            return send_msg('Please, Select a customer',false, 200);
        }
        if($request->sale_items[0]['product'] == null){
            return send_msg('Please, Select at least 1 product',false, 200);
        }
        $request->validate([
            'sale_id' => 'required',
            'user_id' => 'required',
            'shop_id' => 'required',
            'date' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $sale = Sale::find($request->sale_id);
            $sale->user_id = $request->user_id;
            $sale->customer_id = $request->customer['id'];
            $sale->shop_id = $request->shop_id;
            $sale->date = Carbon::parse($request->date)->addHours(6)->toDateString();
            $sale->sub_total = $request->sub_total;
            $sale->discount = $request->discount ?? 0;
            $sale->grand_total = $request->grand_total;
            $sale->paid = $request->paid ?? 0;
            $sale->due = $request->due;
            if($request->due > 0){
                $sale->payment_status = 'DUE';
            }else{
                $sale->payment_status = 'PAID';
            }
            $sale->is_return = $request->is_return;
            $sale->save();
   
            foreach ($request->sale_items as $key => $item) {
                if($item['sale_details_id'] != null){
                    $purchase_item = SaleDetail::find($item['sale_details_id']);
                    $purchase_item->purchase_id = $sale->id;
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
                    $purchase_item = new SaleDetail();
                    $purchase_item->purchase_id = $sale->id;
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
                    $purchase_payment = SalePayment::find($item['purchase_payments_id']);
                    $purchase_payment->amount = $item['amount'];
                    $purchase_payment->date = strlen($item['date']) <= 12 ? $item['date'] : Carbon::parse($item['date'])->addHours(6)->toDateString();
                    $purchase_payment->payment_method = $item['payment_method'];
                    $purchase_payment->save();
                }else{
                    $purchase_payment = new SalePayment();
                    $purchase_payment->purchase_id = $sale->id;
                    $purchase_payment->amount = $item['amount'];
                    $purchase_payment->date = strlen($item['date']) <= 12 ? $item['date'] : Carbon::parse($item['date'])->addHours(6)->toDateString();
                    $purchase_payment->payment_method = $item['payment_method'];
                    $purchase_payment->save();
                }
            }
    

            DB::commit();
            return send_msg("Sale Updated Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }


    public function destroy(Sale $sale)
    {
        if(is_null($this->user) || !$this->user->can('sale.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        DB::beginTransaction();
        try {
            foreach ($sale->sale_details as $key => $value) {
                $product = Product::find($value->product_id);
                $product->stock += $value->qty;
                $product->save();
            }

            $sale->delete();
            $sale->sale_details()->delete();
            $sale->sale_payments()->delete();
            DB::commit();  
            return send_msg('Delete Success', true, 200);    

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('sale.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        
        DB::beginTransaction();
        try {

            $sales = Sale::whereIn('id',request('ids'))->get();
            foreach ($sales as $sale) {

                foreach ($sale->sale_details as $key => $value) {
                    $product = Product::find($value->product_id);
                    $product->stock += $value->qty;
                    $product->save();
                }
                
                $sale->delete();
                $sale->sale_details()->delete();
                $sale->sale_payments()->delete();
                DB::commit();
            }
            return send_msg('Delete Success', true, 200); 

        } catch (\Throwable $th) {
            DB::rollBack();
            return send_msg("Something went wrong!", false, 200);
        }

    }
}

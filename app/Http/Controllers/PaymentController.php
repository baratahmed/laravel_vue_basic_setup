<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Mail\RequestForAdminApproval;
use App\Models\Payment;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
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
        if(is_null($this->user) || !$this->user->can('payment.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $payments = Payment::query()
                ->when((request('from') && request('to')), function($q){
                    $from = Carbon::parse(request('from'))->addHours(6)->toDateString();
                    $to = Carbon::parse(request('to'))->addHours(6)->toDateString();
                    return $q->whereBetween('date',[$from, $to]);
                })
                ->when(request('shop_id'), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return PaymentResource::collection($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('payment.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            // 'shop_id' => 'required',
            'net_total' => 'required',
            'discount' => 'required',
            'grand_total' => 'required',
            'month_count' => 'required',        
        ]);
        $payment = new Payment();
        $payment->shop_id = $request->shop_id;
        $payment->net_total = $request->net_total;
        $payment->discount = $request->discount;
        $payment->grand_total = $request->grand_total;
        $payment->date = Carbon::parse($request->date)->addHours(6)->toDateString();
        $payment->month_count = $request->month_count;
        $payment->status = $request->status;
        $payment->payment_type = $request->payment_type;
        if(isset($request->trx_id)){
            $payment->trx_id = $request->trx_id;
        }
        if(isset($request->info)){
            $payment->info = $request->info;
        }
        $payment->save();

        
        if(env('APP_ENV') == 'production'){
            $mailData = [
                'shop_name' => $payment->shop->name,
                'amount' => $payment->grand_total,
            ];
            Mail::to("baratahmed28@gmail.com")->send(new RequestForAdminApproval($mailData));
        }

        return send_msg("Payment Created Successfully!", true, 200);
    }

    public function approve($id){
        if(is_null($this->user) || !$this->user->can('payment.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $payment = Payment::find($id);
        $payment->status = 'APPROVED';
        $payment->save();

        $shop = Shop::find($payment->shop_id);
        $shop->valid_till = $payment->month_count == 1 ? Carbon::now()->addMonth(1) : Carbon::now()->addMonths($payment->month_count);
        $shop->save();

        return send_msg("Approved!", true, 200);

    }

    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('payment.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Payment::find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('payment.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'shop_id' => 'required',
            'shop_name' => 'required|max:50',
            'name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $payment = Payment::find($request->id);
        $payment->shop_id = $request->shop_id;
        $payment->name = $request->name;
        $payment->shop_name = $request->shop_name;
        $payment->phone = $request->phone;
        $payment->email = $request->email;
        $payment->address = $request->address;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.sup.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
            if($payment->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$payment->image);
                }else{
                    @unlink($payment->image);
                }
            }
        }
        if(isset($save_url)){
            $payment->image = $save_url;
        }
        $payment->save();

        return send_msg("Payment Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if(is_null($this->user) || !$this->user->can('payment.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $payment->delete();
        if($payment->image != 'img/users/user.jpg'){
            @unlink($payment->image);
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('payment.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $payments = Payment::whereIn('id',request('ids'))->get();
        foreach ($payments as $payment) {
            $payment->delete();
            if($payment->image != 'img/users/user.jpg'){
                @unlink($payment->image);
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

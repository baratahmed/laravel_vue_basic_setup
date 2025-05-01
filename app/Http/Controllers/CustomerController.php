<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class CustomerController extends Controller
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
        if(is_null($this->user) || !$this->user->can('customer.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $customers = Customer::query()
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->when(request('shop_id'), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('customer.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            // 'shop_id' => 'required',
            'name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $customer = new Customer();
        $customer->shop_id = $request->shop_id;
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;

        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.cus.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
        }
        $customer->image = $save_url ?? "img/users/user.jpg";
        $customer->save();

        return send_msg("Customer Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('customer.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Customer::find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('customer.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'shop_id' => 'required',
            'name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $customer = Customer::find($request->id);
        $customer->shop_id = $request->shop_id;
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.cus.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
            if($customer->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$customer->image);
                }else{
                    @unlink($customer->image);
                }
            }
        }
        if(isset($save_url)){
            $customer->image = $save_url;
        }
        $customer->save();

        return send_msg("Customer Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if(is_null($this->user) || !$this->user->can('customer.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $customer->delete();
        if($customer->image != 'img/users/user.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$customer->image);
            }else{
                @unlink($customer->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('customer.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $customers = Customer::whereIn('id',request('ids'))->get();
        foreach ($customers as $customer) {
            $customer->delete();
            if($customer->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$customer->image);
                }else{
                    @unlink($customer->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

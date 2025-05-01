<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Master;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
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
        if(is_null($this->user) || !$this->user->can('user.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $users = User::query()
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->when(request('shop_id'), function($q){
                    return $q->where('shop_id',request('shop_id'))->whereNot('id',1);
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
                
        return UserResource::collection($users);
    }

    public function fetchInitialData(Request $request)
    {
        if(isset($request->id)){
            $user = User::find($request->id);
        }
        if($request->shop_id == 'undefined' || $request->shop_id == null ){
            $roles = Role::select('id','name')->whereNot('name','Super Admin')->get();
        }else{
            if(Shop::find($request->shop_id)->shop_type_id == 2){
                $roles = Role::select('id','name')->whereNot('name','Super Admin')->whereNot('name','Admin')->get();
            }else{
                $roles = Role::select('id','name')->whereNot('name','Super Admin')->whereNot('name','Admin')->whereNot('name','Master')->get();
            }
        }
        return [
            'user' => new UserResource($user) ?? null,
            'roles' => $roles,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('user.create')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $request->validate([
            'name' => 'required|max:50',
            'role_name' => 'required',
            'email' => 'required|email|max:50|unique:users',
            'phone' => 'required|max:20|unique:users',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',          
            'division_id' => 'required',
            'zila_id' => 'required',
            'address' => 'required',
        ]);

        DB::beginTransaction();
        try {
                $user = new User();
                $user->name = $request->name;
                $user->shop_id = $request->shop_id ?? null;
                $user->phone = $request->phone;
                $user->email = $request->email;
                // $str = rand(10000000,99999999);
                // $user->real_password = $str;
                // $user->password = bcrypt($str);
                $user->password = bcrypt('password');

                $user->is_verified = $request->status ?? 0;
                $save_url = null;
                if($request->hasFile('image')){
                    $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
                    $manager = new ImageManager(new Driver());
                    $img = $manager->read($request->file('image'));
                    $img->save(base_path('public/img/users/'.$name_gen));
                    $save_url = 'img/users/'.$name_gen;
                }
                $user->image = $save_url ?? "img/users/user.jpg";
                $user->save();

                $user->assignRole($request->role_name);                

                $address = new Address();
                $address->user_id = $user->id;
                $address->division_id = $request->division_id;
                $address->zila_id = $request->zila_id;
                $address->upazila_id = $request->upazila_id;
                $address->union_id = $request->union_id;
                $address->address = $request->address;
                $address->save();

                DB::commit();
                return send_msg("User Created Successfully!", true, 200);
    
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
        if(is_null($this->user) || !$this->user->can('user.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return User::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('user.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        // return $request->all();  
        $request->validate([
            'id' => 'required',
            'address_id' => 'required',
            'name' => 'required|max:50',
            'role_name' => 'required',
            'email' => 'required|email|max:50|unique:users,email,'.$request->id,
            'password' => 'required',
            'phone' => 'required|max:20|unique:users,phone,'.$request->id,
            'image' => 'nullable|image|mimes:png,jpeg,jpg',          
            'division_id' => 'required',
            'zila_id' => 'required',
            'address' => 'required',
        ]);

        DB::beginTransaction();
        try {
                $user = User::find($request->id);
                $user->name = $request->name;
                $user->shop_id = $request->shop_id ?? null;
                $user->phone = $request->phone;
                $user->email = $request->email;
                // $user->real_password = $request->password;
                // $user->password = bcrypt($request->password);
                $user->is_verified = $request->status ?? 0;
                $save_url = null;
                if($request->hasFile('image')){
                    if(env('APP_ENV') == 'production'){
                        @unlink('public/'.$user->image);
                    }else{
                        @unlink($user->image);
                    }
                    $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
                    $manager = new ImageManager(new Driver());
                    $img = $manager->read($request->file('image'));
                    $img->save(base_path('public/img/users/'.$name_gen));
                    $save_url = 'img/users/'.$name_gen;
                }
                if(isset($save_url)){
                    $user->image = $save_url;
                }                
                $user->save();

                if(isset($request->role_name)){
                    $user->roles()->detach();
                    $user->assignRole($request->role_name);
                }

                $address = Address::find($request->address_id);
                $address->division_id = $request->division_id;
                $address->zila_id = $request->zila_id;
                $address->upazila_id = $request->upazila_id;
                $address->union_id = $request->union_id;
                $address->address = $request->address;
                $address->save();

                DB::commit();
                return send_msg("User Updated Successfully!", true, 200);
    
            } catch (\Throwable $th) {
                DB::rollback();
                return send_msg("Something went wrong!", false, 200);
            }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if(is_null($this->user) || !$this->user->can('user.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $user->delete();
        if($user->image != 'img/users/user.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$user->image);
            }else{
                @unlink($user->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('user.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $users = User::whereIn('id',request('ids'))->get();
        foreach ($users as $user) {
            $user->delete();
            if($user->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$user->image);
                }else{
                    @unlink($user->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }


    public function storeSupplier(Request $request){
        if(is_null($this->user) || !$this->user->can('supplier.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == null){
            return send_msg('You need shop_id',false, 200);
        }
        $request->validate([
            'shop_id' => 'required',
            'name' => 'required',
            'shop_name' => 'required',
            'phone' => 'required',
        ]);
        $supplier = new Supplier();
        $supplier->shop_id = $request->shop_id;
        $supplier->name = $request->name;
        $supplier->shop_name = $request->shop_name;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->address = $request->address;
        $supplier->save();

        return send_msg('Supplier created successfully', true, 200);

    }

    public function storeCustomer(Request $request){
        if(is_null($this->user) || !$this->user->can('customer.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == null){
            return send_msg('You need shop_id',false, 200);
        }
        $request->validate([
            'shop_id' => 'required',
            'name' => 'required',
            'phone' => 'required',
        ]);
        $customer = new Customer();
        $customer->shop_id = $request->shop_id;
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->save();

        return send_msg('Customer created successfully', true, 200);
    }

    public function storeMaster(Request $request){
        if(is_null($this->user) || !$this->user->can('user.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == null){
            return send_msg('You need shop_id',false, 200);
        }
        $request->validate([
            'shop_id' => 'required',
            'name' => 'required',
            'phone' => 'required',
        ]);
        $user = new User();
        $user->shop_id = $request->shop_id;
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt('password');
        $user->email = $request->email;
        $user->is_verified = 1;
        $user->image = 'img/users/user.jpg';
        $user->save();
        $user->assignRole('Master');

        return send_msg('Master created successfully', true, 200);
    }
}

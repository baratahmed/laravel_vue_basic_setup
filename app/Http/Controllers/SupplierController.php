<?php

namespace App\Http\Controllers;

use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class SupplierController extends Controller
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
        if(is_null($this->user) || !$this->user->can('supplier.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $suppliers = Supplier::query()
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->when(request('shop_id'), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return SupplierResource::collection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('supplier.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            // 'shop_id' => 'required',
            'name' => 'required|max:50',
            'shop_name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $supplier = new Supplier();
        $supplier->shop_id = $request->shop_id;
        $supplier->name = $request->name;
        $supplier->shop_name = $request->shop_name;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->address = $request->address;

        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.sup.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
        }
        $supplier->image = $save_url ?? "img/users/user.jpg";
        $supplier->save();

        return send_msg("Supplier Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('supplier.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Supplier::find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('supplier.update')){
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

        $supplier = Supplier::find($request->id);
        $supplier->shop_id = $request->shop_id;
        $supplier->name = $request->name;
        $supplier->shop_name = $request->shop_name;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->address = $request->address;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.sup.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
            if($supplier->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$supplier->image);
                }else{
                    @unlink($supplier->image);
                }
            }
        }
        if(isset($save_url)){
            $supplier->image = $save_url;
        }
        $supplier->save();

        return send_msg("Supplier Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        if(is_null($this->user) || !$this->user->can('supplier.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $supplier->delete();
        if($supplier->image != 'img/users/user.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$supplier->image);
            }else{
                @unlink($supplier->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('supplier.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $suppliers = Supplier::whereIn('id',request('ids'))->get();
        foreach ($suppliers as $supplier) {
            $supplier->delete();
            if($supplier->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$supplier->image);
                }else{
                    @unlink($supplier->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

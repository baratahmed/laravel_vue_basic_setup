<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\File;
use App\Models\Product;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
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
        if(is_null($this->user) || !$this->user->can('product.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $products = Product::query()
                ->with('user:id,name')
                ->with('category:id,name')
                ->when(request('shop_id'), function($q, $search){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%')->orWhere('code','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('product.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == 'undefined' || $request->shop_id == null){
            return send_msg('Please, select a shop', false, 200);
        }
        $request->merge([
            'code' => $request->code.'-'.$request->shop_id,
        ]);

        $request->validate([
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'brand_id' => 'required',
            'shop_id' => 'required',
            'unit_id' => 'required',
            'size_id' => 'required',
            'color' => 'required',
            'name' => 'required|max:50',
            'code' => 'required|max:50|unique:products',
            'thumbnail' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);
        DB::beginTransaction();
        try {
            $product = new Product();
            $product->user_id = $request->user()->id;
            $product->shop_id = $request->shop_id; // bitrain
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->brand_id = $request->brand_id;
            $product->unit_id = $request->unit_id;
            $product->size_id = $request->size_id;
            $product->color = $request->color;
            $product->name = $request->name;
            $product->code = $request->code;
            $product->status = $request->status ?? 1;
        
            $save_url = null;
            if($request->hasFile('thumbnail')){
                $name_gen = hexdec(uniqid()).'.'.$request->file('thumbnail')->getClientOriginalExtension();
                $manager = new ImageManager(new Driver());
                $img = $manager->read($request->file('thumbnail'));
                $img->save(base_path('public/img/products/'.$name_gen));
                $save_url = 'img/products/'.$name_gen;
            }

            $product->thumbnail = $save_url ?? "img/products/product.jpg";
            $product->save();

            if(isset($request->file_ids)){
                $file_array =   explode(",",$request->file_ids);
                File::whereIn('id',$file_array)->update(['fileable_id'=>$product->id]);
            }

            DB::commit();
            return send_msg("Product Created Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }

    }

    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('product.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Product::with('user:id,name')
                        ->with('category:id,name')
                        ->with('sub_category:id,name')
                        ->with('brand:id,name')
                        ->with('size:id,name,short_name')
                        ->with('unit:id,name,short_name')
                        ->with('files:id,fileable_id,file_name')
                        ->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('product.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        if($request->shop_id == 'undefined' || $request->shop_id == null){
            return send_msg('Please, select a shop', false, 200);
        }
        $request->merge([
            'code' => $request->code.'-'.$request->shop_id,
        ]);
        $request->validate([
            'id' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'brand_id' => 'required',
            'unit_id' => 'required',
            'size_id' => 'required',
            'color' => 'required',
            'name' => 'required|max:50',
            'code' => 'required|max:50|unique:products,code,'.$request->id,
            'thumbnail' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);
        DB::beginTransaction();
        try {
            $product = Product::find($request->id);
            $product->user_id = $request->user()->id;
            $product->shop_id = $request->shop_id; // bitrain
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->brand_id = $request->brand_id;
            $product->unit_id = $request->unit_id;
            $product->size_id = $request->size_id;
            $product->color = $request->color;
            $product->name = $request->name;
            $product->code = $request->code;
            $product->status = $request->status ?? 1;

            $save_url = null;
            if($request->hasFile('thumbnail')){
                $name_gen = hexdec(uniqid()).'.'.$request->file('thumbnail')->getClientOriginalExtension();
                $manager = new ImageManager(new Driver());
                $img = $manager->read($request->file('thumbnail'));
                $img->save(base_path('public/img/products/'.$name_gen));
                $save_url = 'img/products/'.$name_gen;
                if($product->image != 'img/products/product.jpg'){
                    if(env('APP_ENV') == 'production'){
                        @unlink('public/'.$product->image);
                    }else{
                        @unlink($product->image);
                    }
                }
            }

            $product->thumbnail = $save_url ?? $product->thumbnail;
            $product->save();

            if(isset($request->file_ids)){
                $file_array =   explode(",",$request->file_ids);
                File::whereIn('id',$file_array)->update(['fileable_id'=>$product->id]);
            }

            DB::commit();
            return send_msg("Product Updated Successfully!", true, 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return send_msg("Something went wrong!", false, 200);
        }
        
    }

    public function destroy(Product $product)
    {
        if(is_null($this->user) || !$this->user->can('product.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $product->delete();
        if($product->thumbnail != 'img/products/product.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$product->thumbnail);
            }else{
                @unlink($product->thumbnail);
            }
        }
        $files = File::where('fileable_id',$product->id)->where('fileable_type','App/Models/Product')->get();
        foreach ($files as $key => $file) {
            $file->delete();
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$file->file_name);
            }else{
                @unlink($file->file_name);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('product.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $products = Product::whereIn('id',request('ids'))->get();
        foreach ($products as $product) {
            $product->delete();
            if($product->thumbnail != 'img/products/product.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$product->thumbnail);
                }else{
                    @unlink($product->thumbnail);
                }
            }
            $files = File::where('fileable_id',$product->id)->where('fileable_type','App/Models/Product')->get();
            foreach ($files as $key => $file) {
                $file->delete();
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$file->file_name);
                }else{
                    @unlink($file->file_name);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

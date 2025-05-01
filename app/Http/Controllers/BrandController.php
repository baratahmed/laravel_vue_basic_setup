<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
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
        if(is_null($this->user) || !$this->user->can('brand.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $brands = Brand::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return BrandResource::collection($brands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('brand.create')){
            return send_msg('Unauthorized Access', false, 403);
        }

        $request->validate([
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $brand = new Brand();
        $brand->user_id = $request->user()->id;
        $brand->name = $request->name;
        $brand->status = $request->status ?? 1;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/brands/'.$name_gen));
            $save_url = 'img/brands/'.$name_gen;
        }
        $brand->image = $save_url ?? "img/brands/brand.jpg";
        $brand->save();

        return send_msg("Brand Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('brand.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Brand::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('brand.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $brand = Brand::find($request->id);
        $brand->user_id = $request->user()->id;
        $brand->name = $request->name;
        $brand->status = $request->status;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/brands/'.$name_gen));
            $save_url = 'img/brands/'.$name_gen;
            if($brand->image != 'img/brands/brand.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$brand->image);
                }else{
                    @unlink($brand->image);
                }
            }
        }
        if(isset($save_url)){
            $brand->image = $save_url;
        }
        $brand->save();

        return send_msg("Brand Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        if(is_null($this->user) || !$this->user->can('brand.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $brand->delete();
        if($brand->image != 'img/brands/brand.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$brand->image);
            }else{
                @unlink($brand->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('brand.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        
        $brands = Brand::whereIn('id',request('ids'))->get();
        foreach ($brands as $brand) {
            $brand->delete();
            if($brand->image != 'img/brands/brand.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$brand->image);
                }else{
                    @unlink($brand->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

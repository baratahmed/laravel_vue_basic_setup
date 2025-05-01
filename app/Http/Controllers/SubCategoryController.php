<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class SubCategoryController extends Controller
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
        if(is_null($this->user) || !$this->user->can('subcategory.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $sub_categories = SubCategory::query()
                ->with('user:id,name')
                ->with('category:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
                
        return SubCategoryResource::collection($sub_categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('subcategory.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'category_id' => 'required',
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $sub_category = new SubCategory();
        $sub_category->category_id = $request->category_id;
        $sub_category->user_id = $request->user()->id;
        $sub_category->name = $request->name;
        $sub_category->status = $request->status ?? 1;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/sub_categories/'.$name_gen));
            $save_url = 'img/sub_categories/'.$name_gen;
        }
        $sub_category->image = $save_url ?? "img/sub_categories/sub_category.jpg";
        $sub_category->save();

        return send_msg("Sub-Category Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('subcategory.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return SubCategory::with('user:id,name')->with('category:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('subcategory.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'category_id' => 'required',
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $sub_category = SubCategory::find($request->id);
        $sub_category->category_id = $request->category_id;
        $sub_category->user_id = $request->user()->id;
        $sub_category->name = $request->name;
        $sub_category->status = $request->status;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/sub_categories/'.$name_gen));
            $save_url = 'img/sub_categories/'.$name_gen;
            if($sub_category->image != 'img/sub_categories/sub_category.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$sub_category->image);
                }else{
                    @unlink($sub_category->image);
                }
            }
        }
        if(isset($save_url)){
            $sub_category->image = $save_url;
        }
        $sub_category->save();

        return send_msg("Sub-Category Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SUbCategory $sub_category)
    {
        if(is_null($this->user) || !$this->user->can('subcategory.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $sub_category->delete();
        if($sub_category->image != 'img/sub_categories/sub_category.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$sub_category->image);
            }else{
                @unlink($sub_category->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('subcategory.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $sub_categories = SubCategory::whereIn('id',request('ids'))->get();
        foreach ($sub_categories as $sub_category) {
            $sub_category->delete();
            if($sub_category->image != 'img/sub_categories/sub_category.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$sub_category->image);
                }else{
                    @unlink($sub_category->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

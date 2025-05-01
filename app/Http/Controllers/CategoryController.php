<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
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
        if(is_null($this->user) || !$this->user->can('category.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $categories = Category::query()
                ->with('user:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
                
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('category.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $category = new Category();
        $category->user_id = $request->user()->id;
        $category->name = $request->name;
        $category->status = $request->status ?? 1;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/categories/'.$name_gen));
            $save_url = 'img/categories/'.$name_gen;
        }
        $category->image = $save_url ?? "img/categories/category.jpg";
        $category->save();

        return send_msg("Category Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('category.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Category::with('user:id,name')->find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('category.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $category = Category::find($request->id);
        $category->user_id = $request->user()->id;
        $category->name = $request->name;
        $category->status = $request->status;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/categories/'.$name_gen));
            $save_url = 'img/categories/'.$name_gen;
            if($category->image != 'img/categories/category.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$category->image);
                }else{
                    @unlink($category->image);
                }
            }
        }
        if(isset($save_url)){
            $category->image = $save_url;
        }
        $category->save();

        return send_msg("Category Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if(is_null($this->user) || !$this->user->can('category.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $category->delete();
        if($category->image != 'img/categories/category.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$category->image);
            }else{
                @unlink($category->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('category.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $categories = Category::whereIn('id',request('ids'))->get();
        foreach ($categories as $category) {
            $category->delete();
            if($category->image != 'img/categories/category.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$category->image);
                }else{
                    @unlink($category->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

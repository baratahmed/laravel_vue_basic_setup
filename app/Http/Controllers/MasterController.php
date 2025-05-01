<?php

namespace App\Http\Controllers;

use App\Http\Resources\MasterResource;
use App\Models\Master;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class MasterController extends Controller
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
        if(is_null($this->user) || !$this->user->can('master.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $masters = Master::query()
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->when(request('shop_id'), function($q){
                    return $q->where('shop_id',request('shop_id'));
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
        return MasterResource::collection($masters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('master.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'shop_id' => 'required',
            'name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',            
        ]);
        $master = new Master();
        $master->shop_id = $request->shop_id;
        $master->name = $request->name;
        $master->phone = $request->phone;
        $master->email = $request->email;
        $master->address = $request->address;

        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.mas.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
        }
        $master->image = $save_url ?? "img/users/user.jpg";
        $master->save();

        return send_msg("Master Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('master.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Master::find($id);
    }

    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('master.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'shop_id' => 'required',
            'name' => 'required|max:50',
            'phone' => 'required|max:50',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $master = Master::find($request->id);
        $master->shop_id = $request->shop_id;
        $master->name = $request->name;
        $master->phone = $request->phone;
        $master->email = $request->email;
        $master->address = $request->address;
        $save_url = null;
        if($request->hasFile('image')){
            $name_gen = hexdec(uniqid()).'.mas.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
            if($master->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$master->image);
                }else{
                    @unlink($master->image);
                }
            }
        }
        if(isset($save_url)){
            $master->image = $save_url;
        }
        $master->save();

        return send_msg("Master Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        if(is_null($this->user) || !$this->user->can('master.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $master->delete();
        if($master->image != 'img/users/user.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$master->image);
            }else{
                @unlink($master->image);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('master.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $masters = Master::whereIn('id',request('ids'))->get();
        foreach ($masters as $master) {
            $master->delete();
            if($master->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$master->image);
                }else{
                    @unlink($master->image);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

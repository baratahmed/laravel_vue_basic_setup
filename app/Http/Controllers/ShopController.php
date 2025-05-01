<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;


class ShopController extends Controller
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
        if(is_null($this->user) || !$this->user->can('shop.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $perPage = request('perPage') ?: 10;
        $shops = Shop::query()
                ->with('mall:id,name')
                ->with('shop_type:id,name')
                ->when(request('search'), function($q, $search){
                    return $q->where('name','LIKE','%'.$search.'%');
                })
                ->orderBy('id','desc')
                ->paginate($perPage);
                
        return ShopResource::collection($shops);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('shop.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'mall_id' => 'required',
            'shop_type_id' => 'required',
            'name' => 'required|max:50',
            'valid_till' => 'required',
            'logo' => 'nullable|image|mimes:png,jpeg,jpg',       

            'user_name' => 'required|max:50',
            'phone' => 'required|max:50',
            'password' => 'required|confirmed',

        ]);

        $shop = new Shop();
        $shop->mall_id = $request->mall_id;
        $shop->shop_type_id = $request->shop_type_id;
        $shop->name = $request->name;
        $shop->valid_till = $request->valid_till == 1 ? Carbon::now()->addMonth(1) : Carbon::now()->addMonths($request->valid_till);
        $shop->branch_name = $request->branch_name;
        $save_url = null;
        if($request->hasFile('logo')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('logo')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('logo'));
            $img->save(base_path('public/img/shops/'.$name_gen));
            $save_url = 'img/shops/'.$name_gen;
        }
        $shop->logo = $save_url ?? "img/shops/shop.jpg";
        $shop->save();


        $user = new User();
        $user->name = $request->user_name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->image = 'img/users/user.jpg';
        $user->is_verified = true;
        $user->save();
        $user->assignRole('Owner');
        $user->shop_id = $shop->id;
        $user->save();

        return send_msg("Shop Created Successfully!", true, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(is_null($this->user) || !$this->user->can('shop.read')){
            return send_msg('Unauthorized Access', false, 403);
        }

        return Shop::with('mall:id,name')->with('shop_type:id,name')->find($id);
    }

    public function simpleUpdate(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('shop.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'shop_id' => 'required',
            'mall_id' => 'required',
            'name' => 'required|max:50',
        ]);

        $shop = Shop::find($request->shop_id);
        $shop->mall_id = $request->mall_id;
        $shop->name = $request->name;
        $shop->branch_name = $request->branch_name;
        $shop->save();

        return send_msg("Shop Updated Successfully!", true, 200);
        
    }


    public function update(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('shop.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'id' => 'required',
            'mall_id' => 'required',
            'shop_type_id' => 'required',
            'name' => 'required|max:50',
            'valid_till' => 'required',
            'logo' => 'nullable|image|mimes:png,jpeg,jpg',   
        ]);

        $shop = Shop::find($request->id);
        $shop->mall_id = $request->mall_id;
        $shop->shop_type_id = $request->shop_type_id;
        $shop->name = $request->name;
        $shop->valid_till = $request->valid_till == 1 ? Carbon::now()->addMonth(1) : Carbon::now()->addMonths($request->valid_till);
        $shop->branch_name = $request->branch_name;
        $save_url = null;
        if($request->hasFile('logo')){
            $name_gen = hexdec(uniqid()).'.'.$request->file('logo')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('logo'));
            $img->save(base_path('public/img/shops/'.$name_gen));
            $save_url = 'img/shops/'.$name_gen;
            if($shop->logo != 'img/shops/shop.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$shop->logo);
                }else{
                    @unlink($shop->logo);
                }
            }
        }
        if(isset($save_url)){
            $shop->logo = $save_url;
        }
        $shop->save();

        return send_msg("Shop Updated Successfully!", true, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        if(is_null($this->user) || !$this->user->can('shop.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $shop->delete();
        if($shop->logo != 'img/shops/shop.jpg'){
            if(env('APP_ENV') == 'production'){
                @unlink('public/'.$shop->logo);
            }else{
                @unlink($shop->logo);
            }
        }
        return send_msg('Delete Success', true, 200);
    }

    public function multipleDelete()
    {
        if(is_null($this->user) || !$this->user->can('shop.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $shops = Shop::whereIn('id',request('ids'))->get();
        foreach ($shops as $shop) {
            $shop->delete();
            if($shop->logo != 'img/shops/shop.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$shop->logo);
                }else{
                    @unlink($shop->logo);
                }
            }
        }
        return send_msg('Delete Success', true, 200);

    }
}

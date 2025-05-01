<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class AuthController extends Controller
{
    public function login(LoginRequest $request){
        $user = User::verifiedUser()->where('phone', $request->phone)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        if($user->shop && $user->shop->valid_till){
            if($user->shop->valid_till < Carbon::now()->toDateTimeString()){
                return send_msg('Please, Confirm your payment to login', false, 300);
            }
        }
        return $this->makeToken($user);
    }

    public function makeToken($user){
        if($user->id == 1){
            $token = $user->createToken('admin')->plainTextToken;
        }else{
            $token = $user->createToken('user')->plainTextToken;
        }

        // return response()->json([
        //     'token' => $token,
        //     'user' => AuthResource::make($user),
        // ]);

        return (new AuthResource($user))
                ->additional(['meta' => [
                    'type' => $user->id == 1 ? 'admin' : 'user',
                    'token' => $token,
                ]]);
    }

    public function logout(Request $request){
        try {
            $request->user()->tokens()->delete();
            return send_msg('Logout Success', true, 200);
        } catch (\Exception $e) {
            return send_msg($e->getMessage(), false, $e->getCode());
        }
    }

    public function user(Request $request){
        return AuthResource::make($request->user());
    }

    public function getInstantRolePermissions($user_id){
        $user = User::find($user_id);
        return [
            'role' => $user->role()->name,
            'permissions' => $user->role()->permissions->select('name'),
        ];
    }

    public function updateShopImage(Request $request){
        if($request->hasFile('image')){
            if($request->shop_id){
                $shop = Shop::find($request->shop_id);
            }else{
                send_msg('Something went wrong!',false, 200);
            }
            if($shop->logo != 'img/shops/shop.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$shop->logo);
                }else{
                    @unlink($shop->logo);
                }
            }
            $name_gen = $shop->id.hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/shops/'.$name_gen));
            $save_url = 'img/shops/'.$name_gen;
            $shop->update([
                'logo' => $save_url
            ]);
            return $save_url;
        }
    }

    public function updateShopInfo(Request $request){
        $validated = $request->validate([
            'shop_id' => 'required',
            'mall' => 'required',
            'name' => 'required',
            'branch_name' => 'required',
        ]);

        $shop = Shop::find($request->shop_id);

        if($validated){
            $shop->update([
                'mall_id' => $request->mall['id'],
                'name' => $request->name,
                'branch_name' => $request->branch_name,
            ]);        

            return send_msg('Shop Info Updated', true, 200);
    
        }else{
            return send_msg('Something Went Wrong!', false, 200);
        }

    }

    public function updateProfileImage(Request $request){
        if($request->hasFile('image')){
            if($request->user_id){
                $user = User::find($request->user_id);
            }else{
                send_msg('Something went wrong!',false, 200);
            }
            if($user->image != 'img/users/user.jpg'){
                if(env('APP_ENV') == 'production'){
                    @unlink('public/'.$user->image);
                }else{
                    @unlink($user->image);
                }
            }
            $name_gen = $user->id.hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $manager = new ImageManager(new Driver());
            $img = $manager->read($request->file('image'));
            $img->save(base_path('public/img/users/'.$name_gen));
            $save_url = 'img/users/'.$name_gen;
            $user->update([
                'image' => $save_url
            ]);
            return $save_url;
        }
    }

    public function updateProfile(Request $request){
        
        $validated = $request->validate([
            'user_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|max:50|unique:users,email,'.$request->user_id,
            'phone' => 'required|max:20|unique:users,phone,'.$request->user_id,
        ]);

        $user = User::find($request->user_id);

        if($validated){
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);        

            return send_msg('Profile Updated', true, 200);
    
        }else{
            return send_msg('Something Went Wrong!', false, 200);
        }

    }

    public function changePassword(Request $request){
        
        $validated = $request->validate([
            'user_id' => ['required'],
            'current_password' => ['required'],
            'password' => ['required',Password::defaults(),'confirmed'],
        ]);

        $user = User::find($request->user_id);

        if($validated && Hash::check($request->current_password, $user->password)){
            $user->update([
                'real_password' => null,
                'password' => bcrypt($validated['password']),
            ]);        

            return send_msg('Password Updated', true, 200);
    
        }else{
            return send_msg('Something Went Wrong!', false, 200);
        }

    }


    // public function storeAddress(Request $request){
    //     $request->user()->update([
    //         'division_id' => $request->division_id,
    //         'zila_id' => $request->zila_id,
    //         'upazila_id' => $request->upazila_id,
    //         'union_id' => $request->union_id,
    //         'address' => $request->address,
    //     ]);
    //     return send_msg('Address Saved', true, 201);
    // }

    // public function address(Request $request){
    //     $id = Auth::guard('user-api')->id();

    //     $data = User::where('id',$id)->select('division_id','district_id','address')
    //                     ->with(['division'=>function($q){
    //                         $q->select('id','name','charge');
    //                     },'district'=>function($q){
    //                         $q->select('id','name');
    //                     }])
    //                     ->first();
    //     return $data;
    // }
}

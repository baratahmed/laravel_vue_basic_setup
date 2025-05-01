<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use App\Models\Size;
use App\Models\Color;
use App\Models\Customer;
use App\Models\ExpenseType;
use App\Models\Fraction;
use App\Models\Item;
use App\Models\Mall;
use App\Models\Master;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SimpleFetchController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function($request,$next){
            $this->user = Auth::guard('sanctum')->user();
            return $next($request);
        });
    }

    public function fetchCategories(){
        if(is_null($this->user) || !$this->user->can('category.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Category::select('id','name')->get();
    }
    public function fetchSubCategories($cat_id){
        if(is_null($this->user) || !$this->user->can('subcategory.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return SubCategory::where('category_id',$cat_id)->select('id','name')->get();
    }
    public function fetchBrands(){
        if(is_null($this->user) || !$this->user->can('brand.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Brand::select('id','name')->orderBy('id','desc')->get();
    }
    public function fetchUnits(){
        if(is_null($this->user) || !$this->user->can('unit.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Unit::select('id','name')->orderBy('id','desc')->get();
    }
    public function fetchSizes(){
        if(is_null($this->user) || !$this->user->can('size.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Size::select('id','short_name')->orderBy('id','desc')->get();
    }
    public function fetchColors(){
        if(is_null($this->user) || !$this->user->can('color.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Color::select('id','name','code')->orderBy('id','desc')->get();
    }
    public function fetchMalls(){
        if(is_null($this->user) || !$this->user->can('mall.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Mall::select('id','name','location')->orderBy('id','desc')->get();
    }
    public function fetchShopTypes(){
        if(is_null($this->user) || !$this->user->can('shop.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return ShopType::select('id','name')->get();
    }
    public function fetchShops(){
        if(is_null($this->user) || !$this->user->can('shop.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Shop::select('id','name')->orderBy('id','desc')->get();
    }
    public function fetchProducts(Request $request){
        if(is_null($this->user) || !$this->user->can('product.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Product::select('id','name','stock','latest_cost_price')->orderBy('id','desc')->get();
        // return Product::where('shop_id',$request->shop_id)->select('id','name','stock','latest_cost_price')->orderBy('id','desc')->get();
    }
    public function fetchSuppliers(Request $request){
        if(is_null($this->user) || !$this->user->can('supplier.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        // return Supplier::where('shop_id',$request->shop_id)->select('id','name','shop_name','phone','email','image','address')->orderBy('id','desc')->get();
        return Supplier::select('id','name','shop_name','phone','email','image','address')->orderBy('id','desc')->get();
    }
    public function fetchCustomers(Request $request){
        if(is_null($this->user) || !$this->user->can('customer.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Customer::select('id','name','phone','email','image','address')->orderBy('id','desc')->get();
        // return Customer::where('shop_id',$request->shop_id)->select('id','name','phone','email','image','address')->orderBy('id','desc')->get();
    }
    public function fetchMasters(Request $request){
        if(is_null($this->user) || !$this->user->can('user.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $users = User::where('shop_id',$request->shop_id)->get();
        $masters = collect();
        foreach ($users as $key => $user) {
            if($user->getRoleNames()[0] == 'Master'){
                $masters->push($user);
            };
        }
        return $masters;
    }
    public function fetchItems(){
        return Item::select('id','name')->where('status',1)->get();
    }
    public function fetchFractions(){
        return Fraction::select('id','name','code','value')->where('status',1)->get();
    }
    public function fetchExpenseTypes(){
        if(is_null($this->user) || !$this->user->can('expensetype.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return ExpenseType::select('id','name')->orderBy('id','desc')->get();
    }
}


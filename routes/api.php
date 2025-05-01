<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SimpleFetchController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\MallController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(TestController::class)->group(function(){
    Route::get('/test','index');
});

Route::controller(AuthController::class)->group(function(){
    Route::post('/login','login');
    Route::get('/instant/role/permissions/{user_id}','getInstantRolePermissions');
    Route::post('/change/password','changePassword');
    Route::post('/update/profile','updateProfile');
    Route::post('/update/profile/image','updateProfileImage');
    Route::post('/update/shop/image','updateShopImage');
    Route::post('/update/shop','updateShopInfo');
});


Route::middleware('auth:sanctum','check_authorization')->group(function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/logout','logout');
        Route::get('/me','user');
    });
    Route::controller(DashboardController::class)->group(function(){
        Route::get('/dashboard','index');
    });
    Route::apiResources([
        'brands' => BrandController::class,
        'categories' => CategoryController::class,
        'sub_categories' => SubCategoryController::class,
        'sizes' => SizeController::class,
        'units' => UnitController::class,
        'colors' => ColorController::class,
        'products' => ProductController::class,
        'roles' => RoleController::class,
        'users' => UserController::class,
        'purchases' => PurchaseController::class,
        'sales' => SaleController::class,
        'orders' => OrderController::class,
        'expense_types' => ExpenseTypeController::class,
        'expenses' => ExpenseController::class,
        'malls' => MallController::class,
        'shops' => ShopController::class,
        'customers' => CustomerController::class,
        'masters' => MasterController::class,
        'suppliers' => SupplierController::class,
        'payments' => PaymentController::class,
    ]);

    Route::controller(SimpleFetchController::class)->group(function(){
        Route::get('/simple/categories','fetchCategories');
        Route::get('/simple/sub_categories/{cat_id}','fetchSubCategories');
        Route::get('/simple/brands','fetchBrands');
        Route::get('/simple/units','fetchUnits');
        Route::get('/simple/sizes','fetchSizes');
        Route::get('/simple/colors','fetchColors');
        Route::get('/simple/malls','fetchMalls');
        Route::get('/simple/shop_types','fetchShopTypes');
        Route::get('/simple/shops','fetchShops');
        Route::get('/simple/products','fetchProducts');
        Route::get('/simple/suppliers','fetchSuppliers');
        Route::get('/simple/customers','fetchCustomers');
        Route::get('/simple/masters','fetchMasters');
        Route::get('/simple/items','fetchItems');
        Route::get('/simple/fractions','fetchFractions');
        Route::get('/simple/expense/types','fetchExpenseTypes');
    });

    Route::controller(UserController::class)->group(function(){
        Route::get('/user/fetch/initial/data','fetchInitialData');
        Route::post('/update/user','update');
        Route::delete('/user/multiple-delete','multipleDelete');

        Route::post('/store/supplier','storeSupplier');
        Route::post('/store/customer','storeCustomer');
        Route::post('/store/master','storeMaster');
    });

    Route::controller(PaymentController::class)->group(function(){
        Route::post('/payment/approve/{id}','approve');
        Route::delete('/payment/multiple-delete','multipleDelete');
    });

    Route::controller(MasterController::class)->group(function(){
        Route::post('/update/master','update');
        Route::delete('/master/multiple-delete','multipleDelete');
    });

    Route::controller(CustomerController::class)->group(function(){
        Route::post('/update/customer','update');
        Route::delete('/customer/multiple-delete','multipleDelete');
    });

    Route::controller(SupplierController::class)->group(function(){
        Route::post('/update/supplier','update');
        Route::delete('/supplier/multiple-delete','multipleDelete');
    });

    Route::controller(AddressController::class)->group(function(){
        Route::get('/divisions','fetchDivisions');
        Route::get('/zilas/{id}','fetchZilas');
        Route::get('/upazilas/{id}','fetchUpazilas');
        Route::get('/unions/{id}','fetchUnions');
    });

    Route::controller(RoleController::class)->group(function(){
        Route::get('/fetch/permissions/by/group/name','fetchPermissionsByGroupName');
    });

    Route::controller(BrandController::class)->group(function(){
        Route::post('/update/brand','update');
        Route::delete('/brand/multiple-delete','multipleDelete');
    });

    Route::controller(CategoryController::class)->group(function(){
        Route::post('/update/category','update');
        Route::delete('/category/multiple-delete','multipleDelete');
    });

    Route::controller(SubCategoryController::class)->group(function(){
        Route::post('/update/sub_category','update');
        Route::delete('/sub_category/multiple-delete','multipleDelete');
    });

    Route::controller(ProductController::class)->group(function(){
        Route::post('/update/product','update');
        Route::delete('/product/multiple-delete','multipleDelete');
    });

    Route::controller(PurchaseController::class)->group(function(){
        Route::delete('/purchase/multiple-delete','multipleDelete');
    });

    Route::controller(SaleController::class)->group(function(){
        Route::delete('/sale/multiple-delete','multipleDelete');
    });

    Route::controller(OrderController::class)->group(function(){
        Route::delete('/order/multiple-delete','multipleDelete');
    });

    Route::controller(MallController::class)->group(function(){
        Route::delete('/mall/multiple-delete','multipleDelete');
    });

    Route::controller(ShopController::class)->group(function(){
        Route::post('/simple/update/shop','simpleUpdate');
        Route::post('/update/shop','update');
        Route::delete('/shop/multiple-delete','multipleDelete');
    });


    Route::controller(FileManagerController::class)->group(function(){
        Route::post('/file_manager/upload','upload');
        Route::get('/file_manager/read/{fileable_id}','fetchFiles');
        Route::delete('/file_manager/{lastModified}/{size}','destroyFromClient');
        Route::delete('/file_manager/{fileId}','destroy');
    });
});

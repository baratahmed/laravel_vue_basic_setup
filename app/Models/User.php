<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guarded = [];
    protected $guard_name = 'sanctum';

    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeVerifiedUser($q){
        $q->where('is_verified',1);
    }
    
    public static function getPermissionGroups(){
        return DB::table('permissions')->select('group_name')->groupBy('group_name')->get();
    }

    public static function getPermissionsByGroupName($group_name)
    {
        $permissions = DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
        return $permissions;
    }

    public static function roleHasPermisions($role, $permissions)
    {
        $hasPermission = true;
        foreach ($permissions as $permission) {
            if(!$role->hasPermissionTo($permission->name)){
                return false;
            }
        }
        return $hasPermission;
    }

    public function role(){
        $roles = Role::select('name')->get();
        $arr = [];
        foreach ($roles as $key => $role) {
            array_push($arr,$role->name);
        }
        foreach ($arr as $key => $value) {
            if($this->hasRole($value)){
                return Role::select('id','name')->where('name',$value)->first();
            }
        }
    }

    public function address(){
        return Address::with('divisions:id,name')
                        ->with('zilas:id,name')
                        ->with('upazilas:id,name')        
                        ->with('unions:id,name')
                        ->select('id','user_id','division_id','zila_id','upazila_id','union_id','address')->where('user_id',$this->id)->latest()->first();
    }

    public function shop(){
        return $this->belongsTo(Shop::class);
    }
}

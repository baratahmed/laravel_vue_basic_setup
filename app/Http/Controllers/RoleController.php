<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class RoleController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function($request,$next){
            $this->user = Auth::guard('sanctum')->user();
            return $next($request);
        });
    }

    public function index(){
        if(is_null($this->user) || !$this->user->can('role.read')){
            return send_msg('Unauthorized Access', false, 403);
        }
        return Role::with('permissions:id,name,group_name')->get();
    }

    public function fetchPermissionsByGroupName(Request $request){
        if(isset($request->id)){
            $role = Role::with('permissions:id,name')->find($request->id);
        }
        $groups =  Permission::select('group_name')->groupBy('group_name')->get();
        $myArray = array();
        foreach ($groups as $key => $group) {
            $myArray[trim($group->group_name)] = User::getPermissionsByGroupName($group->group_name);
        }
        return [
            'role' => $role ?? null,
            'permissions_by_groups' => $myArray,
        ];
    }

    public function store(Request $request)
    {
        if(is_null($this->user) || !$this->user->can('role.create')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:100|unique:roles',
            'permissions' => 'required'
        ],[
            'name.required' => 'Please, give a role name',
            'permissions.required' => 'Please, check at least one permission'
        ]);

        if(isset($request->name)){
            $role = Role::create(['name' => $request->name]);
            $permissions = $request->input('permissions');
            if(!empty($permissions)){
                if(isset($role)){
                    $role->syncPermissions($permissions);
                }
            }
        }

        return send_msg("Role Created Successfully!", true, 200);      
    }

    public function update(Request $request, $id)
    {
        if(is_null($this->user) || !$this->user->can('role.update')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $request->validate([
            'name' => 'required|max:100|unique:roles,name,'.$id,
            'permissions' => 'required'
        ],[
            'name.required' => 'Please, give a role name',
            'permissions.required' => 'Please, check at least one permission'
        ]);

        $role = Role::findById($id);
        if(isset($request->name)){
            $role->name = $request->name;
            $role->save;
        }
        $permissions = $request->input('permissions');
        if(!empty($permissions)){
            if(isset($role)){
                $role->syncPermissions($permissions);
            }
        }

        return send_msg("Role Updated Successfully!", true, 200);     
    
    }



    public function destroy($id)
    {
        if(is_null($this->user) || !$this->user->can('role.delete')){
            return send_msg('Unauthorized Access', false, 403);
        }
        $role = Role::find($id);
        if(isset($role)){
            $role->delete();
        }
        return send_msg("Role Deleted Successfully!", true, 200);      

    }

}

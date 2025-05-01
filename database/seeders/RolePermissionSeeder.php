<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roleSuperAdmin = Role::create(['name' => 'Super Admin','guard_name' => 'sanctum']);
        $roleAdmin = Role::create(['name' => 'Admin','guard_name' => 'sanctum']);
        $roleTeacher = Role::create(['name' => 'Teacher','guard_name' => 'sanctum']);

        // Create Permissions as Array
        $permissions= [
            [
                'group_name' => 'Dashboard',
                'permissions' => [
                    'dashboard.read',
                ]
            ],
            [
                'group_name' => 'Role',
                'permissions' => [
                    'role.create',
                    'role.read',
                    'role.update',
                    'role.delete',
                ]
            ],
            [
                'group_name' => 'User',
                'permissions' => [
                    'user.create',
                    'user.read',
                    'user.update',
                    'user.delete',
                ]
            ],
            [
                'group_name' => 'Order',
                'permissions' => [
                    'order.create',
                    'order.read',
                    'order.update',
                    'order.delete',
                ]
            ],

        ];

        // Create & Assign Permissions
        for ($i = 0; $i < count($permissions); $i++) {
            $permissionGroup = $permissions[$i]['group_name'];
            for ($j = 0; $j < count($permissions[$i]['permissions']); $j++) {
                $permission = Permission::create(['name' => $permissions[$i]['permissions'][$j],'group_name'=>$permissionGroup,'guard_name'=>'sanctum']);

                if($permission->name == 'dashboard.read'){
                    $roleSuperAdmin->givePermissionTo($permission);
                    $roleAdmin->givePermissionTo($permission);
                    $roleTeacher->givePermissionTo($permission);
                }

                if($permission->name == 'role.create' || $permission->name == 'role.read' || $permission->name == 'role.update' || $permission->name == 'role.delete'){
                    $roleSuperAdmin->givePermissionTo($permission);
                }

                if($permission->name == 'user.create' || $permission->name == 'user.read' || $permission->name == 'user.update' || $permission->name == 'user.delete'){
                    $roleSuperAdmin->givePermissionTo($permission);
                    $roleAdmin->givePermissionTo($permission);
                    $roleTeacher->givePermissionTo($permission);
                }
            
                if($permission->name == 'order.create' || $permission->name == 'order.read' || $permission->name == 'order.update' || $permission->name == 'order.delete'){
                    $roleSuperAdmin->givePermissionTo($permission);
                    $roleAdmin->givePermissionTo($permission);
                    $roleTeacher->givePermissionTo($permission);
                }                
                
            }
        }
        
        
    }
}

<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $user = new User();
        $user->name = 'Super Admin';
        $user->phone = '01812521337';
        $user->email = 'admin@gmail.com';
        $user->image = 'img/users/user.jpg';
        $user->password = bcrypt('password');
        $user->is_verified = true;
        $user->save();
        $user->assignRole('Super Admin');

        $address = new Address();
        $address->user_id = $user->id;
        $address->division_id = 1;
        $address->zila_id = 1;
        $address->upazila_id = 1;
        $address->union_id = 1;
        $address->address = "1 no police gate";
        $address->save();




        // // Owner one
        // $user = new User();
        // $user->shop_id = 1;
        // $user->name = 'Test Owner 1';
        // $user->phone = '0123456781';
        // $user->email = 'testowner1@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Owner');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();

        // // Manager one
        // $user = new User();
        // $user->shop_id = 1;
        // $user->name = 'Test Manager 1';
        // $user->phone = '0123456782';
        // $user->email = 'testmanager1@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Manager');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();

        // // Employee one
        // $user = new User();
        // $user->shop_id = 1;
        // $user->name = 'Test Employee 1';
        // $user->phone = '0123456783';
        // $user->email = 'testemployee1@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Employee');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();




        // // Owner Two
        // $user = new User();
        // $user->shop_id = 2;
        // $user->name = 'Test Owner 2';
        // $user->phone = '0123456719';
        // $user->email = 'testowner2@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Owner');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();

        // // Manager two
        // $user = new User();
        // $user->shop_id = 2;
        // $user->name = 'Test Manager 2';
        // $user->phone = '0123456729';
        // $user->email = 'testmanager2@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Manager');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();

        // // Employee two
        // $user = new User();
        // $user->shop_id = 2;
        // $user->name = 'Test Employee 2';
        // $user->phone = '0123456739';
        // $user->email = 'testemployee2@gmail.com';
        // $user->image = 'img/users/user.jpg';
        // $user->password = bcrypt('password');
        // $user->is_verified = true;
        // $user->save();
        // $user->assignRole('Employee');

        // $address = new Address();
        // $address->user_id = $user->id;
        // $address->division_id = 1;
        // $address->zila_id = 1;
        // $address->upazila_id = 1;
        // $address->union_id = 1;
        // $address->address = "Dampara WASA";
        // $address->save();
    }
}
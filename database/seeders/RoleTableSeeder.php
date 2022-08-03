<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role')->truncate();
        DB::table("role")->insert([
            "role_id"   =>   1,
            "role_name" => "Admin",
            "status_id"     => 1,
        ]);
        DB::table("role")->insert([
            "role_id"   =>   2,
            "role_name" => "Manager",
            "status_id"     => 1,
        ]);
        DB::table("role")->insert([
            "role_id"   =>   3,
            "role_name" => "Agent",
            "status_id"     => 1,
        ]);
        DB::table("role")->insert([
            "role_id"   =>   4,
            "role_name" => "Designer",
            "status_id"     => 1,
        ]);
        DB::table("role")->insert([
            "role_id"   =>   5,
            "role_name" => "Digitizer",
            "status_id"     => 1,
        ]);
        DB::table("role")->insert([
            "role_id"   =>   6,
            "role_name" => "Accountant",
            "status_id"     => 1,
        ]);
    }
}

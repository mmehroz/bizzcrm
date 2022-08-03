<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("location")->truncate();
        DB::table("location")->insert([
            "location_id"   =>   1,
            "location_name" => "United-States",
            "status_id"     => 1,
        ]);
        DB::table("location")->insert([
            "location_id"   =>   2,
            "location_name" => "Canada",
            "status_id"     => 1,
        ]);
        DB::table("location")->insert([
            "location_id"   =>   3,
            "location_name" => "United-Kingdom",
            "status_id"     => 1,
        ]);
        DB::table("location")->insert([
            "location_id"   =>   4,
            "location_name" => "Pakistan",
            "status_id"     => 1,
        ]);
        DB::table("location")->insert([
            "location_id"   =>   5,
            "location_name" => "Australia",
            "status_id"     => 1,
        ]);

    }
}

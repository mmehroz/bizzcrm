<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("group")->truncate();
        DB::table("group")->insert([
            "group_id"    =>   1,
            "group_name"  => "Max Digitizing",
            "group_image" => "group_chat.png",
            "status_id"   => 1,
            "created_by"  => 1,
        ]);
        DB::table("group")->insert([
            "group_id"    =>   2,
            "group_name"  => "Avidhaus",
            "group_image" => "group_chat_icon.png",
            "status_id"   => 1,
            "created_by"  => 2,
        ]);
    }
}

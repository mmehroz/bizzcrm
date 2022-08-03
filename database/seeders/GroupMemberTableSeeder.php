<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupMemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("groupmember")->truncate();
        DB::table("groupmember")->insert([
            "groupmember_id"    => 1,
            "group_id"          => 1,
            "user_id"           => 1,
            "status_id"         => 1,
        ]);
        DB::table("groupmember")->insert([
            "groupmember_id"    => 2,
            "group_id"          => 1,
            "user_id"           => 2,
            "status_id"         => 1,
        ]);
        DB::table("groupmember")->insert([
            "groupmember_id"    => 3,
            "group_id"          => 2,
            "user_id"           => 2,
            "status_id"         => 1,
        ]);
        DB::table("groupmember")->insert([
            "groupmember_id"    => 4,
            "group_id"          => 2,
            "user_id"           => 3,
            "status_id"         => 1,
        ]);
    }
}
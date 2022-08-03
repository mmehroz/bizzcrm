<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user')->truncate();
        DB::table("user")->insert([
            "user_id" =>   1,
            "user_name" => "Admin",
            "user_email" => "admin@maxdigitizing.com",
            "user_officenumberext" => 0001,
            "user_phonenumber" => 03001010001,
            "user_username" => "admin",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 1,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
        DB::table("user")->insert([
            "user_id" =>   2,
            "user_name" => "Manager",
            "user_email" => "manager@maxdigitizing.com",
            "user_officenumberext" => 0002,
            "user_phonenumber" => 03001010002,
            "user_username" => "manager",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 2,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
        DB::table("user")->insert([
            "user_id" =>   3,
            "user_name" => "Agent",
            "user_email" => "agent@maxdigitizing.com",
            "user_officenumberext" => 0003,
            "user_phonenumber" => 03001010003,
            "user_username" => "agent",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 3,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
        DB::table("user")->insert([
            "user_id" =>   4,
            "user_name" => "Designer",
            "user_email" => "designer@maxdigitizing.com",
            "user_officenumberext" => 0004,
            "user_phonenumber" => 03001010004,
            "user_username" => "designer",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 4,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
        DB::table("user")->insert([
            "user_id" =>   5,
            "user_name" => "Digitizer",
            "user_email" => "digitizer@maxdigitizing.com",
            "user_officenumberext" => 0005,
            "user_phonenumber" => 03001010005,
            "user_username" => "digitizer",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 5,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
        DB::table("user")->insert([
            "user_id" =>   6,
            "user_name" => "Accountant",
            "user_email" => "accountant@maxdigitizing.com",
            "user_officenumberext" => 0006,
            "user_phonenumber" => 03001010006,
            "user_username" => "accountant",
            "user_target" => NULL,
            "user_targetmonth" => NULL,
            "user_password" => 12345678,
            "user_picture" => NULL,
            "user_loginstatus" => 0,
            "campaign_id" => 1,
            "role_id" => 6,
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
    }
}

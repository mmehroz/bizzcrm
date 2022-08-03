<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('campaign')->truncate();
        DB::table("campaign")->insert([
            "campaign_id" =>   1,
            "campaign_banner" => "campaign_banner.png",
            "campaign_logo" => "campaign_logo.png",
            "campaign_website" => "http://maxdigitizing.com",
            "campaign_campaignname" => "Max Digitizing",
            "campaign_email" => "info@maxdigitizing.com",
            "currency_id" => 1,
            "location_id" => 1,
            "campaign_campaignfor" => "Digitizing & Vector",
            "campaign_aboutus" => "Max Digitizing provides embroidery digitizing further as vector art services with a price effective resolution. We provide high standards towards our work which will keep our purchasers on top.",
            "status_id" => 1,
            "created_by" => 1,
            "updated_by" => NULL,
        ]);
    }
}


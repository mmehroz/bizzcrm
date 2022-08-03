<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign', function (Blueprint $table) {
            $table->bigIncrements('campaign_id');
            $table->text('campaign_banner',)->nullable();
            $table->text('campaign_logo',)->nullable();
            $table->string('campaign_website', 250)->nullable();
            $table->string('campaign_campaignname', 250)->nullable();
            $table->string('campaign_email', 250)->nullable();
            $table->integer('currency_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->text('campaign_campaignfor')->nullable();
            $table->text('campaign_aboutus')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign');
        // Schema::table('campaign', function (Blueprint $table) {
        //     //
        // });
    }
}

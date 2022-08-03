<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
            $table->bigIncrements('client_id');
            $table->string('client_companyname', 250)->nullable();
            $table->string('client_contactperson', 250)->nullable();
            $table->text('client_address',)->nullable();
            $table->string('client_officenumber', 250)->nullable();
            $table->string('client_alternateofficenumber', 250)->nullable();
            $table->text('client_twitterid')->nullable();
            $table->text('client_facebookid')->nullable();
            $table->text('client_instagramid')->nullable();
            $table->string('client_state', 250)->nullable();
            $table->string('client_city', 250)->nullable();
            $table->integer('location_id')->nullable();
            $table->string('client_timezone', 250)->nullable();
            $table->string('client_email', 250)->nullable();
            $table->string('client_alternateemail', 250)->nullable();
            $table->string('client_website', 250)->nullable();
            $table->string('client_companyindustry', 250)->nullable();
            $table->string('client_designation', 250)->nullable();
            $table->text('client_companydecription')->nullable();
            $table->string('client_zipcode', 250)->nullable();
            $table->decimal('client_totalrevenue', 10, 0)->nullable();
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
        Schema::dropIfExists('client');
        // Schema::table('client', function (Blueprint $table) {
        //     //
        // });
    }
}

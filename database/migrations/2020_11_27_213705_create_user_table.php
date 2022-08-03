<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('user_name', 250)->nullable();
            $table->string('user_email', 250)->nullable();
            $table->string('user_officenumberext', 250)->nullable();
            $table->string('user_phonenumber', 250)->nullable();
            $table->string('user_username', 250)->nullable();
            $table->decimal('user_target', 10, 0)->nullable();
            $table->integer('user_targetmonth')->nullable();
            $table->text('user_password')->nullable();
            $table->text('user_picture')->nullable();
            $table->string('user_loginstatus', 250)->nullable();
            $table->integer('campaign_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('user');
        // Schema::table('user', function (Blueprint $table) {
        //     //
        // });
    }
}

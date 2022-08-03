<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target', function (Blueprint $table) {
            $table->bigIncrements('target_id');
            $table->integer('target_month')->nullable();
            $table->decimal('target_amount', 10, 0)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('target');
        // Schema::table('target', function (Blueprint $table) {
        //     //
        // });
    }
}

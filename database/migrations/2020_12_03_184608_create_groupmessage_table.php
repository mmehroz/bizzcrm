<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupmessage', function (Blueprint $table) {
            $table->bigIncrements('groupmessage_id');
            $table->integer('user_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->longText('groupmessage_body')->nullable();
            $table->text('groupmessage_attachment')->nullable();
            $table->string('groupmessage_originalname', 250)->nullable();
            $table->integer('status_id')->nullable();
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
        Schema::dropIfExists('groupmessage');
        // Schema::table('group_message', function (Blueprint $table) {
        //     //
        // });
    }
}

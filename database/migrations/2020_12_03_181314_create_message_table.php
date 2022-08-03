<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message', function (Blueprint $table) {
            $table->bigIncrements('message_id');
            $table->integer('message_from')->nullable();
            $table->integer('message_to')->nullable();
            $table->longText('message_body')->nullable();
            $table->text('message_attachment')->nullable();
            $table->string('message_originalname', 250)->nullable();
            $table->boolean('message_seen')->nullable();
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
        Schema::dropIfExists('message');
        // Schema::table('message', function (Blueprint $table) {
        //     //
        // });
    }
}

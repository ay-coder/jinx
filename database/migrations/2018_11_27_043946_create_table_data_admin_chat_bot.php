<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDataAdminChatBot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('data_chat_boat', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('other_user_id')->nullable();
            $table->longtext('question')->nullable();
            $table->longtext('user_answer')->nullable();
            $table->longtext('other_user_answer')->nullable();
            $table->integer('accept_user_id')->default(0)->nullable();
            $table->integer('accept_other_user_id')->default(0)->nullable();
            $table->dateTime('user_answer_time')->nullable();
            $table->dateTime('other_user_answer_time')->nullable();
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
        //
    }
}

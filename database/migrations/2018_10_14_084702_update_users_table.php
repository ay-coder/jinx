<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) 
        {
            $table->date('birthdate')->after('profile_pic')->nullable();
            $table->longText('profession')->after('birthdate')->nullable();
            $table->longText('education')->after('profession')->nullable();
            $table->string('state')->after('city')->nullable();
            $table->string('latitude')->after('education')->default(0)->nullable();
            $table->string('longitude')->after('latitude')->default(0)->nullable();
            
            $table->dropColumn('is_archive');
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

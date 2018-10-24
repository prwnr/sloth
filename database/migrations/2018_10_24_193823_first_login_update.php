<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FirstLoginUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('first_login');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('first_login')->default(true);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('first_login')->default(true);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_login');
        });

    }
}

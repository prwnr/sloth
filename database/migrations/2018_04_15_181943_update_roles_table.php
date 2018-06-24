<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_unique');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->integer('team_id')->unsigned()->nullable();

            $table->foreign('team_id')->references('id')->on('teams')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['name', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     * Unique index is not reversed for name, as it may be duplicated with new accounts
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_team_id_unique');
            $table->dropForeign('roles_team_id_foreign');
            $table->dropColumn('team_id');
        });
    }
}

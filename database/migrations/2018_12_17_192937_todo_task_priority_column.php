<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TodoTaskPriorityColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('todo_tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable(false)->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('todo_tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
}

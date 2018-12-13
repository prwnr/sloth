<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTodoTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description')->nullable(false);
            $table->integer('member_id')->unsigned()->nullable(false);
            $table->integer('project_id')->unsigned()->nullable(false);
            $table->integer('task_id')->unsigned()->nullable();
            $table->integer('timelog_id')->unsigned()->nullable();
            $table->float('finished')->default(false);
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('task_id')->references('id')->on('tasks')
                ->onDelete('set null');
            $table->foreign('timelog_id')->references('id')->on('time_logs')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todo_tasks');
    }
}

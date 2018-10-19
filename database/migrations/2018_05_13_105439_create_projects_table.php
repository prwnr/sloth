<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable(false);
            $table->string('name')->nullable(false);
            $table->float('budget');
            $table->string('budget_period');
            $table->integer('budget_currency_id')->unsigned();
            $table->integer('client_id')->unsigned()->nullable();
            $table->integer('team_id')->unsigned();
            $table->integer('billing_id')->unsigned()->nullable();
            $table->timestamps();

            $table->unique(['code', 'team_id']);

            $table->foreign('team_id')->references('id')->on('teams')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('client_id')->references('id')->on('clients')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('billing_id')->references('id')->on('billings');
            $table->foreign('budget_currency_id')->references('id')->on('currencies');
        });

        Schema::create('member_project', function (Blueprint $table) {
            $table->integer('member_id')->unsigned();
            $table->integer('project_id')->unsigned();

            $table->foreign('member_id')->references('id')->on('members')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['member_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_project');
        Schema::dropIfExists('projects');
    }
}

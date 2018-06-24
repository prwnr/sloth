<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name');
            $table->string('city');
            $table->string('zip');
            $table->string('country');
            $table->string('street');
            $table->string('vat');
            $table->string('fullname');
            $table->string('email');
            $table->timestamps();

            $table->integer('billing_id')->unsigned()->nullable();
            $table->foreign('billing_id')->references('id')->on('billings')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->integer('team_id')->unsigned();
            $table->foreign('team_id')->references('id')->on('teams')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}

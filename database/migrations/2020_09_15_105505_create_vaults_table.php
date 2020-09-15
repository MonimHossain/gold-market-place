<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type_of_metal');
            $table->string('category');
            $table->string('manufactor');
            $table->integer('weight');
            $table->string('purity');
            $table->string('serial_no');
            $table->text('description')->nullable();
            $table->string('filename');
            $table->enum('sending_option', ['pick_up', 'drop_off']); 
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('country');
            $table->integer('user_id');
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
        Schema::dropIfExists('vaults');
    }
}

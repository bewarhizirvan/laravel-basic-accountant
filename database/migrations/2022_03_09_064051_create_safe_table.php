<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safe', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('description')->nullable();
            $table->string('address')->nullable();
            $table->string('type');
            $table->string('direction')->default('out');
            $table->double('amount')->default(0);
            $table->boolean('active')->default(1);
            $table->bigInteger('currency_id')->default(1);
            $table->bigInteger('customer_id')->default(0);
            $table->bigInteger('wallet_id')->default(0);
            $table->bigInteger('user_id')->default(1);
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
        Schema::dropIfExists('safe');
    }
};

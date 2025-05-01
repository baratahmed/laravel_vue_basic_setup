<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */ 
    
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('long',8,3);
            $table->decimal('cheast',8,3);
            $table->decimal('belly',8,3);
            $table->decimal('hip',8,3);
            $table->decimal('shoulder',8,3);
            $table->decimal('hand_long_full',8,3);
            $table->decimal('kop',8,3);
            $table->decimal('throat',8,3);
            $table->decimal('total_loose',8,3);
            $table->decimal('enclosure',8,3);
            $table->decimal('front_cheast',8,3);
            $table->decimal('front_belly',8,3);
            $table->decimal('front_hip',8,3);
            $table->decimal('wrap',8,3);
            $table->decimal('arm',8,3);
            $table->decimal('kop_arm',8,3);

            $table->decimal('unit_price',10,2);
            $table->integer('qty');
            $table->decimal('net_price',10,2);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

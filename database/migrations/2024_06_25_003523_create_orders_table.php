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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Seller ID');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('master_id');
            $table->unsignedBigInteger('shop_id');
            $table->date('date');
            $table->date('trial_date');
            $table->date('delivery_date');

            $table->decimal('sub_total',10,2);
            $table->decimal('discount',10,2)->default(0.00);
            $table->decimal('grand_total',10,2);
            $table->decimal('paid',10,2);
            $table->decimal('due',10,2);
            $table->enum('order_status',['PROCESSING','DELIVERED']);
            $table->enum('payment_status',['PAID','DUE']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

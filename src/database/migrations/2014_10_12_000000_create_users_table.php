<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('email', 255)->unique();

            // 役割
            $table->string('role', 20)->default('user')->index();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);

            // ▼ 追加：Stripe 連携用
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_payment_method')->nullable();

            // $table->rememberToken(); // 任意

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

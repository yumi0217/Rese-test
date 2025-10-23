<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')
                ->constrained()->cascadeOnDelete();

            $table->foreignId('reservation_id')
                ->constrained()->cascadeOnDelete();

            $table->integer('amount');              // 円（setupモードは 0 を入れる）
            $table->string('status', 50);           // pending / succeeded / canceled / setup_completed など

            $table->string('transaction_id', 255)   // StripeのIDを入れる
                ->unique();                         // PaymentIntent ID や SetupIntent ID を格納

            $table->timestamps();

            // よく使う検索のためのインデックス（任意）
            $table->index(['user_id', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

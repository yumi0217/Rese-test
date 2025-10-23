<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $t->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $t->unsignedTinyInteger('rating'); // 1..5
            $t->text('comment')->nullable();
            $t->timestamps();

            $t->unique('reservation_id'); // 同じ予約の重複レビューを防止
            $t->index(['restaurant_id', 'rating']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

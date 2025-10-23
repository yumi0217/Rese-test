<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoritesTable extends Migration
{
    public function up()
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->bigIncrements('id');                         // PK
            $table->unsignedBigInteger('user_id');               // FK → users(id)
            $table->unsignedBigInteger('restaurant_id');         // FK → restaurants(id)
            $table->timestamps();                                // created_at / updated_at

            // 外部キー（ユーザー/店舗削除でお気に入りも削除）
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('restaurant_id')
                ->references('id')->on('restaurants')
                ->onDelete('cascade');

            // 同じユーザーが同じ店舗を重複登録しない
            $table->unique(['user_id', 'restaurant_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorites');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantsTable extends Migration
{
    public function up()
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->bigIncrements('id');                  // PK

            // ★ 管轄する店舗代表者（users.id）。未割当の店もあるので nullable
            $table->unsignedBigInteger('owner_id')->nullable()->comment('店舗代表者ユーザーID');

            $table->string('name', 100);                  // 店名
            $table->text('description')->nullable();      // 説明文（任意）
            $table->unsignedBigInteger('area_id');        // FK → areas(id)
            $table->unsignedBigInteger('genre_id');       // FK → genres(id)
            $table->string('image_url', 255)->nullable(); // 画像パス（public/storage など）
            $table->timestamps();                         // created_at / updated_at

            // 外部キー
            $table->foreign('owner_id')
                ->references('id')->on('users')
                ->onDelete('set null');                  // 代表者削除時は管轄解除

            $table->foreign('area_id')
                ->references('id')->on('areas')
                ->onDelete('cascade');

            $table->foreign('genre_id')
                ->references('id')->on('genres')
                ->onDelete('cascade');

            // 参照用インデックス
            $table->index('owner_id');
            $table->index('area_id');
            $table->index('genre_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('restaurants');
    }
}

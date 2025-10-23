<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->bigIncrements('id'); // PK

            // 外部キー
            $table->unsignedBigInteger('user_id');        // users(id)
            $table->unsignedBigInteger('restaurant_id');  // restaurants(id)

            // 予約情報
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->unsignedSmallInteger('number_of_people');

            // QR照合
            $table->string('qr_token', 64)->unique();     // 予約作成時にランダム生成
            $table->timestamp('qr_verified_at')->nullable(); // 照合日時（任意）

            // ★ 追加：リマインド送信済みフラグ（送信時刻）
            $table->timestamp('reminder_sent_at')->nullable()->index();

            // タイムスタンプ
            $table->timestamps(); // created_at / updated_at

            // 外部キー制約
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('restaurant_id')
                ->references('id')->on('restaurants')
                ->onDelete('cascade');

            // 索引
            $table->index('user_id');
            $table->index('restaurant_id');
            $table->index('reservation_date');
            $table->index(['restaurant_id', 'reservation_date'], 'res_restaurant_date_idx');
            $table->index(['user_id', 'reservation_date'], 'res_user_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}

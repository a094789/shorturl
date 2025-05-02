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
        Schema::table('short_urls', function (Blueprint $table) {
            // 添加索引到 user_id 欄位
            // 因為 user_id 已經是外鍵，所以我們只需要添加一個普通索引來優化查詢效能
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            // 移除索引
            $table->dropIndex(['user_id']);
        });
    }
};

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
            // 添加軟刪除欄位
            $table->softDeletes();
            // 添加自動清理時間戳記，用於判斷何時永久刪除
            $table->timestamp('auto_cleanup_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            // 移除軟刪除欄位
            $table->dropSoftDeletes();
            // 移除自動清理時間戳記
            $table->dropColumn('auto_cleanup_at');
        });
    }
};

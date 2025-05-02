<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 檢查索引是否已存在
        $indexExists = collect(DB::select("SHOW INDEXES FROM users WHERE Key_name = 'users_name_index'"))->isNotEmpty();
        
        if (!$indexExists) {
            Schema::table('users', function (Blueprint $table) {
                // 為 name 欄位添加索引以提升搜尋效能
                $table->index('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 移除 name 欄位的索引
            $table->dropIndex(['name']);
        });
    }
};

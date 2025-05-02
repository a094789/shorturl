<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('部門名稱');
            $table->string('code')->unique()->comment('部門代碼');
            $table->text('description')->nullable()->comment('部門描述');
            $table->timestamps();
            $table->softDeletes();
        });

        // 新增預設部門
        DB::table('departments')->insert([
            'name' => '預設部門',
            'code' => 'default',
            'description' => '系統預設部門',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}; 
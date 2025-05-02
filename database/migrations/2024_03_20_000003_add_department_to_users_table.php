<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('id')
                ->constrained('departments')
                ->nullOnDelete();
            $table->foreignId('role_id')->nullable()->after('department_id')
                ->constrained('roles')
                ->nullOnDelete();
            // 保留原有的 is_admin 欄位作為向下相容
            // $table->dropColumn('is_admin');
        });

        // 將現有使用者分配到預設部門和角色
        $defaultDepartmentId = DB::table('departments')->where('code', 'default')->value('id');
        $defaultRoleId = DB::table('roles')->where('code', 'user')->value('id');

        if ($defaultDepartmentId && $defaultRoleId) {
            DB::table('users')->whereNull('department_id')->update([
                'department_id' => $defaultDepartmentId,
                'role_id' => $defaultRoleId
            ]);
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['department_id', 'role_id']);
            // $table->boolean('is_admin')->default(false);
        });
    }
}; 
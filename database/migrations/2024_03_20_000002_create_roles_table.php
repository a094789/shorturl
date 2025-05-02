<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('name')->comment('角色名稱');
            $table->string('code')->unique()->comment('角色代碼');
            $table->text('description')->nullable()->comment('角色描述');
            $table->json('permissions')->nullable()->comment('權限設定');
            $table->timestamps();
            $table->softDeletes();
        });

        // 新增預設角色
        DB::table('roles')->insert([
            [
                'type' => 'Admin',
                'name' => '管理員',
                'code' => 'admin',
                'description' => '系統管理員角色',
                'permissions' => json_encode([
                    'create_short_url',
                    'view_all_short_urls',
                    'edit_all_short_urls',
                    'delete_all_short_urls',
                    'manage_users',
                    'manage_roles',
                    'manage_departments'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type' => 'AW4',
                'name' => '網路組教職員',
                'code' => 'aw4',
                'description' => '網路組教職員角色',
                'permissions' => json_encode([
                    'create_short_url',
                    'view_own_short_urls',
                    'edit_own_short_urls',
                    'delete_own_short_urls',
                    'view_department_short_urls',
                    'edit_department_short_urls',
                    'delete_department_short_urls'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type' => 'Employees',
                'name' => '教職員',
                'code' => 'employees',
                'description' => '一般教職員角色',
                'permissions' => json_encode([
                    'create_short_url',
                    'view_own_short_urls',
                    'edit_own_short_urls',
                    'delete_own_short_urls'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}; 
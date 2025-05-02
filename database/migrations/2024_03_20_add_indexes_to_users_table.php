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
        $connection = DB::connection();
        $hasNameIndex = collect($connection->getSchemaBuilder()->getIndexes('users'))
            ->contains(function ($index) {
                return $index['name'] === 'users_name_index';
            });
            
        $hasEmailIndex = collect($connection->getSchemaBuilder()->getIndexes('users'))
            ->contains(function ($index) {
                return $index['name'] === 'users_email_index';
            });

        Schema::table('users', function (Blueprint $table) use ($hasNameIndex, $hasEmailIndex) {
            if (!$hasNameIndex) {
                $table->index('name');
            }
            
            if (!$hasEmailIndex) {
                $table->index('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists(['name']);
            $table->dropIndexIfExists(['email']);
        });
    }
}; 
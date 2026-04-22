<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->string('system_subheader')->nullable()->after('system_name');
        });

        DB::table('system_settings')
            ->whereNull('system_subheader')
            ->update(['system_subheader' => 'Municipality of Lebak']);
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('system_subheader');
        });
    }
};

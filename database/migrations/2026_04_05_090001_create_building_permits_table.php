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
        Schema::create('building_permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_id')->unique();
            $table->string('owner_last_name');
            $table->string('owner_first_name');
            $table->string('owner_middle_name')->nullable();
            $table->string('owner_suffix')->nullable();
            $table->unsignedBigInteger('building_type_id')->index();
            $table->unsignedBigInteger('building_category_id')->index();
            $table->string('barangay');
            $table->string('status')->default('Pending');
            $table->string('document_status')->default('Incomplete');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_permits');
    }
};

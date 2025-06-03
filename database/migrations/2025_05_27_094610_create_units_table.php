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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('crm_unit_id')->nullable(); // Unit yang dihubungkan ke CRM
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 0); // Harga unit
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 15,2)->default(0); // Nilai komisi untuk unit ini

            $table->string('image')->nullable(); // Gambar unit
            $table->string('unit_type')->nullable(); // Tipe unit (residential, commercial, dll)
            $table->string('building_area')->nullable(); // Luas bangunan
            $table->string('land_area')->nullable(); // Luas tanah
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('carport')->nullable();
            $table->integer('floor')->nullable(); // Lantai
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('crm_unit_id');
            $table->index(['project_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

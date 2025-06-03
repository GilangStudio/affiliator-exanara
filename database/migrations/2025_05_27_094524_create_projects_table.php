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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->text('terms_and_conditions');
            $table->text('additional_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_digital_signature')->default(true);

            //apakah sudah menyetujui perjanjian kerjasama / MoU
            $table->boolean('is_agreement_accepted')->default(false);
            $table->text('agreement_sign')->nullable();

            $table->unsignedBigInteger('crm_project_id')->nullable(); // Project yang dihubungkan ke CRM
            
            $table->timestamps();

            $table->index('crm_project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

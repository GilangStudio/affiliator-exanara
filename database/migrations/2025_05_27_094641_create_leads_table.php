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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliator_project_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('notes')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->decimal('commission_earned', 15, 0)->default(0);
            // $table->enum('status', [''])->default('pending');
            // Relasi ke CRM external
            $table->unsignedBigInteger('crm_lead_id')->nullable();
            $table->timestamps();
            
            $table->index('crm_lead_id');
            $table->index(['unit_id', 'verification_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

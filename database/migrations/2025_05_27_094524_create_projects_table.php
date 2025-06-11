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
            $table->string('developer_name')->nullable();
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('brochure_file')->nullable();
            $table->string('price_list_file')->nullable();
            $table->text('terms_and_conditions');
            $table->text('additional_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_digital_signature')->default(true);
            $table->enum('commission_payment_trigger', ['booking_fee', 'akad_kredit', 'spk'])->nullable();

            $table->string('pic_name')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('pic_phone')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('registration_type', ['manual', 'internal', 'crm'])->default('internal');
            $table->enum('registration_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');

            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

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

<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Seeder;
use App\Models\ProjectRegistration;

class ProjectRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample admin for testing
        $admin = User::create([
            'name' => 'Admin Test',
            'username' => 'developer_test',
            'email' => 'developer@example.com',
            'country_code' => '+62',
            'phone' => '81234567892',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create sample manual project
        $project = Project::create([
            'name' => 'Green Valley Residence',
            'developer_name' => 'PT Hunian Harmonis Ceria',
            'location' => 'Jakarta Selatan',
            'website_url' => 'https://greenvalley.example.com',
            'description' => '<p>Green Valley Residence adalah project perumahan modern dengan konsep eco-friendly yang berlokasi strategis di Jakarta Selatan. Dilengkapi dengan berbagai fasilitas modern seperti taman bermain, kolam renang, dan area jogging.</p>',
            'terms_and_conditions' => '<p>Syarat dan ketentuan akan diatur setelah project disetujui.</p>',
            'commission_payment_trigger' => 'booking_fee',
            'pic_name' => 'Budi Santoso',
            'pic_phone' => '081987654321',
            'pic_email' => 'budi.santoso@greenvalley.com',
            'start_date' => now()->addDays(30),
            'end_date' => now()->addYear(),
            'registration_type' => 'manual',
            'registration_status' => 'pending',
            'is_active' => false,
            'require_digital_signature' => true,
        ]);

        // Create sample units
        $units = [
            [
                'name' => 'Type A - Minimalist',
                'price' => 850000000,
                'unit_type' => 'residential',
                'commission_type' => 'percentage',
                'commission_value' => 2.5,
                'description' => 'Rumah minimalis 2 lantai dengan desain modern',
                'building_area' => 80,
                'land_area' => 100,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'carport' => 1,
                'floor' => 2,
                'power_capacity' => 2200,
                'certificate_type' => 'SHM',
                'unit_status' => 'ready',
                'is_active' => false,
            ],
            [
                'name' => 'Type B - Family',
                'price' => 1200000000,
                'unit_type' => 'residential',
                'commission_type' => 'percentage',
                'commission_value' => 3.0,
                'description' => 'Rumah keluarga dengan ruang yang lebih luas',
                'building_area' => 120,
                'land_area' => 150,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'carport' => 2,
                'floor' => 2,
                'power_capacity' => 3500,
                'certificate_type' => 'SHM',
                'unit_status' => 'ready',
                'is_active' => false,
            ],
            [
                'name' => 'Type C - Premium',
                'price' => 1800000000,
                'unit_type' => 'residential',
                'commission_type' => 'fixed',
                'commission_value' => 45000000,
                'description' => 'Rumah premium dengan fasilitas lengkap',
                'building_area' => 180,
                'land_area' => 200,
                'bedrooms' => 5,
                'bathrooms' => 4,
                'carport' => 3,
                'floor' => 2,
                'power_capacity' => 5500,
                'certificate_type' => 'SHM',
                'unit_status' => 'indent',
                'is_active' => false,
            ],
        ];

        foreach ($units as $unitData) {
            Unit::create(array_merge($unitData, ['project_id' => $project->id]));
        }

        // Create PIC user
        $picUser = User::createPicUser(
            'Budi Santoso',
            'budi.santoso@greenvalley.com', 
            '081987654321'
        );

        // Link PIC to project
        $project->update(['pic_user_id' => $picUser->id]);

        // Create project registration record
        ProjectRegistration::create([
            'project_id' => $project->id,
            'submitted_by' => $picUser->id,
            'form_data' => [
                'project_name' => $project->name,
                'developer_name' => $project->developer_name,
                'location' => $project->location,
                'website_url' => $project->website_url,
                'description' => $project->description,
                'commission_payment_trigger' => $project->commission_payment_trigger,
                'pic_name' => $project->pic_name,
                'pic_phone' => $project->pic_phone,
                'pic_email' => $project->pic_email,
                'start_date' => $project->start_date->format('Y-m-d'),
                'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : null,
                'units' => $units,
                'data_accuracy' => true,
                'terms_agreement' => true,
            ],
            'status' => 'pending',
        ]);

        // Create another approved project example
        $approvedProject = Project::create([
            'name' => 'Urban Heights',
            'developer_name' => 'PT Pembangunan Urban',
            'location' => 'Bandung',
            'website_url' => 'https://urbanheights.example.com',
            'description' => '<p>Urban Heights adalah apartemen modern di pusat kota Bandung dengan akses mudah ke berbagai fasilitas umum.</p>',
            'terms_and_conditions' => '<p>Syarat dan ketentuan telah disetujui dan berlaku.</p>',
            'commission_payment_trigger' => 'spk',
            'pic_name' => 'Sari Wijaya',
            'pic_phone' => '081234567899',
            'pic_email' => 'sari.wijaya@urbanheights.com',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addMonths(18),
            'registration_type' => 'manual',
            'registration_status' => 'approved',
            'is_active' => true,
            'require_digital_signature' => true,
            'approved_by' => 1, // Assuming superadmin ID is 1
            'approved_at' => now()->subDays(5),
        ]);

        // Create unit for approved project
        Unit::create([
            'project_id' => $approvedProject->id,
            'name' => 'Studio Premium',
            'price' => 650000000,
            'unit_type' => 'residential',
            'commission_type' => 'percentage',
            'commission_value' => 3.5,
            'description' => 'Studio apartemen dengan view kota',
            'building_area' => 35,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'floor' => 15,
            'power_capacity' => 1300,
            'certificate_type' => 'SHM',
            'unit_status' => 'ready',
            'is_active' => true,
        ]);

        // Create approved PIC user
        $approvedPicUser = User::createPicUser(
            'Sari Wijaya',
            'sari.wijaya@urbanheights.com',
            '081234567899'
        );
        $approvedPicUser->update(['is_active' => true]);

        // Link PIC to approved project
        $approvedProject->update(['pic_user_id' => $approvedPicUser->id]);

        // Create approved registration record
        ProjectRegistration::create([
            'project_id' => $approvedProject->id,
            'submitted_by' => $approvedPicUser->id,
            'form_data' => [
                'project_name' => $approvedProject->name,
                'developer_name' => $approvedProject->developer_name,
                'location' => $approvedProject->location,
                'commission_payment_trigger' => $approvedProject->commission_payment_trigger,
                'pic_name' => $approvedProject->pic_name,
                'pic_phone' => $approvedProject->pic_phone,
                'pic_email' => $approvedProject->pic_email,
            ],
            'status' => 'approved',
            'reviewed_by' => 1, // Assuming superadmin ID is 1
            'reviewed_at' => now()->subDays(5),
            'review_notes' => 'Project sangat bagus dan sesuai dengan standar. Disetujui.',
        ]);
    }
}
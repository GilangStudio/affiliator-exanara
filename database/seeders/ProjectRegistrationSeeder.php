<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Unit;
use App\Models\ProjectRegistration;
use Faker\Factory as Faker;

class ProjectRegistrationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat user Admin untuk pengujian
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

        // Inisialisasi Faker untuk data Indonesia
        $faker = Faker::create('id_ID');

        // 2. Loop untuk membuat 20 project yang sudah disetujui
        for ($i = 1; $i <= 20; $i++) {
            
            // --- Data Dinamis untuk Setiap Project ---
            $projectName = $faker->company . ' Residence';
            $developerName = $faker->company;
            $location = $faker->randomElement(['Jakarta Selatan', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan', 'Denpasar']);
            $picName = $faker->name;
            // Pastikan email unik dengan menambahkan nomor iterasi
            $picEmail = strtolower(str_replace(' ', '.', $picName)) . $i . '@example.com'; 
            $picPhone = $faker->unique()->numerify('081#########');
            $startDate = now();
            $approvedAt = $startDate->copy()->subDays(rand(2, 5));

            // --- Buat Project ---
            $project = Project::create([
                'name' => str_replace(['PT ', 'CV ', 'Fa ', 'Yayasan ', 'UD ', 'PD ', 'PJ ', 'Tbk', '(Persero)'], '', $projectName),
                'developer_name' => 'PT ' . str_replace(['PT ', 'CV ', 'Fa ', 'Yayasan ', 'UD ', 'PD ', 'PJ ', 'Perum '], '', $developerName),
                'location' => $location,
                'website_url' => 'https://' . strtolower(str_replace(' ', '', $faker->company)) . '.example.com',
                'description' => '<p>' . $faker->paragraph(3) . '</p>',
                'terms_and_conditions' => '<p>Syarat dan ketentuan telah disetujui dan berlaku untuk project ini.</p>',
                'commission_payment_trigger' => $faker->randomElement(['booking_fee', 'akad_kredit', 'spk']),
                'pic_name' => $picName,
                'pic_phone' => $picPhone,
                'pic_email' => $picEmail,
                'start_date' => $startDate,
                'end_date' => now()->addMonths(rand(3, 12)),
                'registration_type' => 'manual',
                'registration_status' => 'approved', // Status disetujui
                'is_active' => true,                 // Project aktif
                'require_digital_signature' => $faker->boolean,
                'approved_by' => $admin->id,         // Disetujui oleh admin yang kita buat
                'approved_at' => $approvedAt,
            ]);

            // --- Buat beberapa unit (2-4 unit per project) ---
            $unitsDataForJson = [];
            $unitCount = rand(2, 4);
            for ($j = 1; $j <= $unitCount; $j++) {

                $commissionType = $faker->randomElement(['percentage', 'fixed']);
                $commissionValue = $commissionType === 'percentage' ? $faker->randomElement([2, 2.5, 3]) : $faker->randomElement([30000000, 50000000]);

                $unitData = [
                    'project_id' => $project->id,
                    'name' => 'Tipe ' . chr(64 + $j) . ' ' . $faker->word, // Tipe A, Tipe B, dst.
                    'price' => $faker->numberBetween(500, 4000) * 1000000,
                    'unit_type' => 'residential',
                    'commission_type' => $commissionType,
                    'commission_value' => $commissionValue,
                    'description' => 'Unit modern dengan ' . $faker->sentence(4),
                    'building_area' => $faker->numberBetween(40, 200),
                    'land_area' => $faker->numberBetween(60, 300),
                    'bedrooms' => $faker->numberBetween(2, 5),
                    'bathrooms' => $faker->numberBetween(1, 4),
                    'carport' => $faker->numberBetween(1, 3),
                    'floor' => $faker->numberBetween(1, 2),
                    'power_capacity' => $faker->randomElement([1300, 2200, 3500, 5500]),
                    'certificate_type' => 'SHM',
                    'unit_status' => $faker->randomElement(['ready', 'indent']),
                    'is_active' => true, // Unit aktif
                ];
                Unit::create($unitData);
                $unitsDataForJson[] = $unitData; // Simpan untuk form_data registrasi
            }

            // --- Buat User PIC dan aktifkan ---
            $picUser = User::createPicUser($picName, $picEmail, $picPhone);
            $picUser->update(['is_active' => true]);

            // --- Hubungkan PIC ke Project ---
            $project->update(['pic_user_id' => $picUser->id]);

            // --- Buat Catatan Registrasi yang Disetujui ---
            ProjectRegistration::create([
                'project_id' => $project->id,
                'submitted_by' => $picUser->id,
                'form_data' => [ // Simulasikan data formulir yang dikirim
                    'project_name' => $project->name,
                    'developer_name' => $project->developer_name,
                    'location' => $project->location,
                    'pic_name' => $project->pic_name,
                    'pic_phone' => $project->pic_phone,
                    'pic_email' => $project->pic_email,
                    'units' => $unitsDataForJson, // Data unit yang dibuat di atas
                ],
                'status' => 'approved', // Status disetujui
                'reviewed_by' => $admin->id,
                'reviewed_at' => $approvedAt,
                'review_notes' => 'Project disetujui via seeder.',
            ]);

            // --- Buat Admin yang Mengelola Project ---
            $admin = User::create([
                'name' => $faker->name,
                'username' => strtolower($faker->userName),
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->unique()->numerify('081#########'),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'country_code' => '+62', // default Indonesia
                'is_active' => true,
            ]);

            $project->admins()->attach([$admin->id, $picUser->id]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Affiliator System',
                'type' => 'string',
                'description' => 'Nama situs/aplikasi'
            ],
            [
                'key' => 'site_description',
                'value' => 'Sistem Manajemen Affiliator Terpadu',
                'type' => 'string',
                'description' => 'Deskripsi situs/aplikasi'
            ],
            [
                'key' => 'contact_email',
                'value' => 'admin@example.com',
                'type' => 'string',
                'description' => 'Email kontak utama'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+62812345678',
                'type' => 'string',
                'description' => 'Nomor telepon kontak'
            ],
            [
                'key' => 'contact_address',
                'value' => 'Jl. Contoh No. 123, Jakarta',
                'type' => 'string',
                'description' => 'Alamat kantor'
            ],

            // Commission Settings
            [
                'key' => 'min_withdrawal_amount',
                'value' => '100000',
                'type' => 'integer',
                'description' => 'Minimal amount untuk penarikan komisi'
            ],
            [
                'key' => 'commission_withdrawal_fee',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Biaya admin untuk penarikan komisi'
            ],
            [
                'key' => 'max_projects_per_affiliator',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Maksimal project yang bisa diikuti affiliator'
            ],
            [
                'key' => 'auto_approve_withdrawals',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Otomatis approve penarikan tanpa review manual'
            ],

            // Agreement Project Settings
            [
                'key' => 'agreement_project',
                'value' => 'Silakan baca dan setujui syarat dan ketentuan berikut sebelum menggunakan layanan kami.',
                'type' => 'string',
                'description' => 'Teks syarat dan ketentuan project'
            ],

            // Notification Settings
            [
                'key' => 'email_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi email'
            ],
            [
                'key' => 'whatsapp_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi WhatsApp'
            ],
            [
                'key' => 'push_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Aktifkan push notification'
            ],

            // Maintenance Settings
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Mode maintenance sistem'
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.',
                'type' => 'string',
                'description' => 'Pesan yang ditampilkan saat maintenance'
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
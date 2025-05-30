<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Apa itu sistem affiliator?',
                'answer' => 'Sistem affiliator adalah platform yang memungkinkan Anda untuk mempromosikan produk atau layanan dan mendapatkan komisi dari setiap penjualan yang berhasil dilakukan melalui referral Anda.',
                'category' => 'general',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'question' => 'Bagaimana cara mendaftar sebagai affiliator?',
                'answer' => 'Untuk mendaftar sebagai affiliator, klik menu "Daftar" di halaman utama, isi formulir pendaftaran dengan data yang lengkap dan valid, kemudian verifikasi email Anda. Setelah itu, Anda dapat bergabung dengan project yang tersedia.',
                'category' => 'account',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'question' => 'Berapa minimal penarikan komisi?',
                'answer' => 'Minimal penarikan komisi adalah Rp 100.000. Pastikan saldo komisi Anda sudah mencukupi sebelum melakukan request penarikan.',
                'category' => 'commission',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'question' => 'Berapa lama proses penarikan komisi?',
                'answer' => 'Proses penarikan komisi biasanya memakan waktu 1-3 hari kerja setelah request Anda disetujui oleh admin. Pastikan data rekening bank Anda sudah terverifikasi untuk mempercepat proses.',
                'category' => 'commission',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Apa saja persyaratan untuk bergabung dengan project?',
                'answer' => 'Untuk bergabung dengan project, Anda perlu: 1) Melengkapi profil dengan data yang valid, 2) Upload foto KTP yang jelas, 3) Menyetujui syarat dan ketentuan project, 4) Memberikan tanda tangan digital jika diperlukan.',
                'category' => 'project',
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Bagaimana cara menambahkan lead baru?',
                'answer' => 'Untuk menambahkan lead baru, masuk ke menu "Lead", klik "Tambah Lead", isi data customer dengan lengkap dan benar, kemudian submit. Lead akan diverifikasi oleh admin sebelum komisi dihitung.',
                'category' => 'project',
                'sort_order' => 6,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer' => 'Kami mendukung pembayaran melalui transfer bank ke rekening BCA, Mandiri, BRI, BNI, dan bank-bank besar lainnya. E-wallet seperti GoPay, OVO, DANA, dan LinkAja juga tersedia.',
                'category' => 'payment',
                'sort_order' => 7,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Bagaimana cara mengubah data rekening bank?',
                'answer' => 'Untuk mengubah data rekening bank, masuk ke menu "Rekening Bank", pilih rekening yang ingin diubah, klik "Edit", perbarui data yang diperlukan, dan submit. Data baru akan diverifikasi ulang oleh admin.',
                'category' => 'account',
                'sort_order' => 8,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Apa yang harus dilakukan jika lupa password?',
                'answer' => 'Jika lupa password, klik "Lupa Password" di halaman login, masukkan email yang terdaftar, kemudian cek email Anda untuk link reset password. Ikuti instruksi di email untuk membuat password baru.',
                'category' => 'technical',
                'sort_order' => 9,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Bagaimana cara melacak status verifikasi KTP?',
                'answer' => 'Status verifikasi KTP dapat dilihat di menu "Project" pada project yang Anda ikuti. Status akan menampilkan "Pending", "Verified", atau "Rejected" beserta catatan dari admin jika ada.',
                'category' => 'project',
                'sort_order' => 10,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Apakah ada biaya admin untuk penarikan?',
                'answer' => 'Biaya admin untuk penarikan komisi bervariasi tergantung kebijakan masing-masing project. Informasi detail biaya akan ditampilkan saat Anda melakukan request penarikan.',
                'category' => 'commission',
                'sort_order' => 11,
                'is_active' => true,
                'is_featured' => false
            ],
            [
                'question' => 'Bagaimana cara menghubungi customer service?',
                'answer' => 'Anda dapat menghubungi customer service melalui menu "Support" untuk membuat tiket bantuan, atau melalui WhatsApp di nomor yang tercantum di profil kontak kami.',
                'category' => 'general',
                'sort_order' => 12,
                'is_active' => true,
                'is_featured' => false
            ]
        ];

        foreach ($faqs as $faqData) {
            Faq::create(array_merge($faqData, [
                'project_id' => null, // Global FAQ
                'type' => 'general',
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
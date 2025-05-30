@extends('layouts.main')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-settings me-2"></i>
                    Pengaturan
                </h3>
            </div>
            <div class="card-body p-2">
                <nav class="nav nav-pills nav-vertical settings-nav">
                    <a class="nav-link {{ $activeTab == 'general' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'general']) }}">
                        <i class="ti ti-home me-2"></i>
                        Pengaturan Umum
                    </a>
                    <a class="nav-link {{ $activeTab == 'commission' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'commission']) }}">
                        <i class="ti ti-percentage me-2"></i>
                        Pengaturan Komisi
                    </a>
                    <a class="nav-link {{ $activeTab == 'notification' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'notification']) }}">
                        <i class="ti ti-bell me-2"></i>
                        Notifikasi
                    </a>
                    {{-- <a class="nav-link {{ $activeTab == 'security' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'security']) }}">
                        <i class="ti ti-shield me-2"></i>
                        Keamanan
                    </a> --}}
                    <a class="nav-link {{ $activeTab == 'profile' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'profile']) }}">
                        <i class="ti ti-user me-2"></i>
                        Profil Saya
                    </a>
                    <a class="nav-link {{ $activeTab == 'maintenance' ? 'active' : '' }}" 
                       href="{{ route('superadmin.settings.index', ['tab' => 'maintenance']) }}">
                        <i class="ti ti-tools me-2"></i>
                        Maintenance
                    </a>
                </nav>
            </div>
        </div>

        <!-- System Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-chart-bar me-2"></i>
                    Statistik Sistem
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h3 mb-0 text-blue">{{ number_format($stats['total_users']) }}</div>
                            <div class="text-secondary small">Total Users</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h3 mb-0 text-green">{{ number_format($stats['total_affiliators']) }}</div>
                            <div class="text-secondary small">Affiliator</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h3 mb-0 text-orange">{{ number_format($stats['total_admins']) }}</div>
                            <div class="text-secondary small">Admin</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h3 mb-0 text-dark">{{ $stats['storage_size'] }}</div>
                            <div class="text-secondary small">Storage</div>
                        </div>
                    </div>
                </div>
                
                {{-- @if($stats['last_backup'])
                <div class="mt-3 pt-3 border-top">
                    <div class="text-secondary small">Backup Terakhir</div>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($stats['last_backup'])->format('d/m/Y H:i') }}</div>
                    <div class="text-secondary small">{{ \Carbon\Carbon::parse($stats['last_backup'])->diffForHumans() }}</div>
                </div>
                @endif --}}
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-check me-2"></i>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-exclamation-circle me-2"></i>
                </div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <!-- General Settings Tab -->
        @if($activeTab == 'general')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-home me-2"></i>
                    Pengaturan Umum
                </h3>
            </div>
            <form action="{{ route('superadmin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Situs <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                       name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required>
                                @error('site_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Kontak <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" required>
                                @error('contact_email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Situs</label>
                        <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                  name="site_description" rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                        @error('site_description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Situs</label>
                                @if($settings['site_logo'])
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('site_logo') is-invalid @enderror" 
                                       name="site_logo" accept="image/*">
                                @error('site_logo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Format: JPG, PNG, GIF, SVG. Maksimal 2MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Favicon</label>
                                @if($settings['site_favicon'])
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Favicon" class="img-thumbnail" style="max-height: 50px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('site_favicon') is-invalid @enderror" 
                                       name="site_favicon" accept=".ico,image/png">
                                @error('site_favicon')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Format: ICO, PNG. Maksimal 512KB</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" required>
                                @error('contact_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control @error('contact_address') is-invalid @enderror" 
                                          name="contact_address" rows="3">{{ old('contact_address', $settings['contact_address']) }}</textarea>
                                @error('contact_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Commission Settings Tab -->
        @if($activeTab == 'commission')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-percentage me-2"></i>
                    Pengaturan Komisi
                </h3>
            </div>
            <form action="{{ route('superadmin.settings.commission.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimal Penarikan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('min_withdrawal_amount') is-invalid @enderror" 
                                           name="min_withdrawal_amount" value="{{ old('min_withdrawal_amount', $settings['min_withdrawal_amount']) }}" 
                                           min="0" step="1000" required>
                                </div>
                                @error('min_withdrawal_amount')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Jumlah minimal untuk penarikan komisi</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Biaya Penarikan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('commission_withdrawal_fee') is-invalid @enderror" 
                                           name="commission_withdrawal_fee" value="{{ old('commission_withdrawal_fee', $settings['commission_withdrawal_fee']) }}" 
                                           min="0" step="1000">
                                </div>
                                @error('commission_withdrawal_fee')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Biaya admin untuk setiap penarikan (0 = gratis)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Maksimal Project per Affiliator <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_projects_per_affiliator') is-invalid @enderror" 
                                       name="max_projects_per_affiliator" value="{{ old('max_projects_per_affiliator', $settings['max_projects_per_affiliator']) }}" 
                                       min="1" max="10" required>
                                @error('max_projects_per_affiliator')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Batas maksimal project yang bisa diikuti affiliator</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" name="auto_approve_withdrawals" 
                                           value="1" {{ old('auto_approve_withdrawals', $settings['auto_approve_withdrawals']) ? 'checked' : '' }}>
                                    <span class="form-check-label">Otomatis Setujui Penarikan</span>
                                </label>
                                <small class="form-hint">Jika diaktifkan, penarikan akan langsung disetujui tanpa review manual</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Notification Settings Tab -->
        @if($activeTab == 'notification')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-bell me-2"></i>
                    Pengaturan Notifikasi
                </h3>
            </div>
            <form action="{{ route('superadmin.settings.notification.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="email_notification" 
                                           value="1" {{ old('email_notification', $settings['email_notification']) ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        <i class="ti ti-mail me-2"></i>
                                        Notifikasi Email
                                    </span>
                                </label>
                                <small class="form-hint">Kirim notifikasi melalui email</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="whatsapp_notification" 
                                           value="1" {{ old('whatsapp_notification', $settings['whatsapp_notification']) ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        <i class="ti ti-brand-whatsapp me-2"></i>
                                        Notifikasi WhatsApp
                                    </span>
                                </label>
                                <small class="form-hint">Kirim notifikasi melalui WhatsApp</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="push_notification" 
                                           value="1" {{ old('push_notification', $settings['push_notification']) ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        <i class="ti ti-bell me-2"></i>
                                        Push Notification
                                    </span>
                                </label>
                                <small class="form-hint">Kirim notifikasi push dalam aplikasi</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <div>
                            <strong>Catatan:</strong> Pengaturan ini mengatur apakah sistem akan mengirim notifikasi melalui channel yang dipilih. 
                            Pastikan konfigurasi email dan WhatsApp sudah diatur dengan benar di file environment.
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Security Settings Tab -->
        {{-- @if($activeTab == 'security')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-shield me-2"></i>
                    Pengaturan Keamanan
                </h3>
            </div>
            <form action="{{ route('superadmin.settings.security.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimal Panjang Password <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('password_min_length') is-invalid @enderror" 
                                       name="password_min_length" value="{{ old('password_min_length', $settings['password_min_length']) }}" 
                                       min="6" max="20" required>
                                @error('password_min_length')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Minimal 6 karakter, maksimal 20 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Timeout Session (menit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('session_timeout') is-invalid @enderror" 
                                       name="session_timeout" value="{{ old('session_timeout', $settings['session_timeout']) }}" 
                                       min="30" max="1440" required>
                                @error('session_timeout')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Waktu logout otomatis jika tidak ada aktivitas</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Maksimal Percobaan Login <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_login_attempts') is-invalid @enderror" 
                                       name="max_login_attempts" value="{{ old('max_login_attempts', $settings['max_login_attempts']) }}" 
                                       min="3" max="10" required>
                                @error('max_login_attempts')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Akun akan diblokir setelah melebihi batas ini</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" name="require_email_verification" 
                                           value="1" {{ old('require_email_verification', $settings['require_email_verification']) ? 'checked' : '' }}>
                                    <span class="form-check-label">Wajib Verifikasi Email</span>
                                </label>
                                <small class="form-hint">User harus verifikasi email sebelum bisa menggunakan sistem</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
        @endif --}}

        <!-- Profile Tab -->
        @if($activeTab == 'profile')
        <div class="row">
            <!-- Profile Info -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user me-2"></i>
                            Informasi Profil
                        </h3>
                    </div>
                    <form action="{{ route('superadmin.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+62</span>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           placeholder="8123456789" value="{{ old('phone', $user->phone) }}" required>
                                </div>
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Nomor tanpa kode negara dan tanpa 0 di depan</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                                       name="profile_photo" accept="image/*">
                                @error('profile_photo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>
                                Perbarui Profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-key me-2"></i>
                            Ubah Password
                        </h3>
                    </div>
                    <form action="{{ route('superadmin.settings.password.change') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       name="current_password" required>
                                @error('current_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                       name="new_password" minlength="8" required>
                                @error('new_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Minimal 8 karakter</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                       name="new_password_confirmation" required>
                                @error('new_password_confirmation')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="ti ti-key me-1"></i>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Profile Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                                 class="avatar avatar-xl mb-3">
                        </div>
                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <div class="text-secondary mb-3">{{ $user->email }}</div>
                        <div class="text-secondary mb-3">
                            <i class="ti ti-phone me-1"></i>
                            {{ $user->country_code }} {{ $user->phone }}
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="text-secondary small">Role</div>
                                    <div class="h4 mb-0">{{ $user->role }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="text-secondary small">Status</div>
                                    <div class="h4 mb-0">
                                        <span class="badge bg-success-lt">Aktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->profile_photo)
                        <div class="mt-3">
                            <form action="{{ route('superadmin.settings.photo.delete') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Hapus foto profil?')">
                                    <i class="ti ti-trash me-1"></i>
                                    Hapus Foto
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Account Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Info Akun</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="text-secondary small">Bergabung</div>
                            <div>{{ $user->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary small">Login Terakhir</div>
                            <div>
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('d/m/Y H:i') }}
                                    <div class="text-secondary small">{{ $user->last_login_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-secondary">Belum pernah</span>
                                @endif
                            </div>
                        </div>
                        @if($user->email_verified_at)
                        <div class="mb-2">
                            <div class="text-secondary small">Email Terverifikasi</div>
                            <div class="text-success">
                                <i class="ti ti-check me-1"></i>
                                {{ $user->email_verified_at->format('d/m/Y') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Maintenance Tab -->
        @if($activeTab == 'maintenance')
        <div class="row">
            <div class="col-md-6">
                <!-- Maintenance Mode -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-tools me-2"></i>
                            Mode Maintenance
                        </h3>
                    </div>
                    <form action="{{ route('superadmin.settings.maintenance.toggle') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" 
                                           {{ $settings['maintenance_mode'] ? 'checked' : '' }}
                                           onchange="this.form.submit()">
                                    <span class="form-check-label">
                                        <strong>Mode Maintenance</strong>
                                    </span>
                                </label>
                                <small class="form-hint">
                                    Jika diaktifkan, hanya SuperAdmin yang bisa mengakses sistem
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pesan Maintenance</label>
                                <textarea class="form-control" name="maintenance_message" rows="3" 
                                          placeholder="Pesan yang ditampilkan saat maintenance">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                                <small class="form-hint">Pesan yang akan ditampilkan kepada user</small>
                            </div>

                            @if($settings['maintenance_mode'])
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Mode maintenance sedang aktif!</strong><br>
                                Hanya SuperAdmin yang dapat mengakses sistem saat ini.
                            </div>
                            @endif
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="ti ti-refresh me-1"></i>
                                {{ $settings['maintenance_mode'] ? 'Nonaktifkan' : 'Aktifkan' }} Maintenance
                            </button>
                        </div>
                    </form>
                </div>

                <!-- System Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-server me-2"></i>
                            Aksi Sistem
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-bold">Bersihkan Cache</div>
                                    <div class="text-secondary small">Hapus semua cache sistem untuk meningkatkan performa</div>
                                </div>
                                <form action="{{ route('superadmin.settings.cache.clear') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm" 
                                            onclick="return confirm('Bersihkan cache sistem?')">
                                        <i class="ti ti-trash me-1"></i>
                                        Bersihkan
                                    </button>
                                </form>
                            </div>

                            {{-- <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-bold">Backup Database</div>
                                    <div class="text-secondary small">Buat backup database untuk keamanan data</div>
                                </div>
                                <form action="{{ route('superadmin.settings.backup.create') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" 
                                            onclick="return confirm('Buat backup database?')">
                                        <i class="ti ti-download me-1"></i>
                                        Backup
                                    </button>
                                </form>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- System Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-info-circle me-2"></i>
                            Informasi Sistem
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>PHP Version</span>
                                <span class="fw-bold">{{ PHP_VERSION }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>Laravel Version</span>
                                <span class="fw-bold">{{ app()->version() }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>Environment</span>
                                <span class="badge bg-{{ app()->environment() == 'production' ? 'success' : 'warning' }}-lt">
                                    {{ strtoupper(app()->environment()) }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>Debug Mode</span>
                                <span class="badge bg-{{ config('app.debug') ? 'danger' : 'success' }}-lt">
                                    {{ config('app.debug') ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0">
                                <span>Storage Used</span>
                                <span class="fw-bold">{{ $stats['storage_size'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Backups -->
                {{-- <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-history me-2"></i>
                            Backup Terakhir
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($stats['last_backup'])
                            <div class="d-flex align-items-center">
                                <div class="stat-icon text-success me-3">
                                    <i class="ti ti-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ \Carbon\Carbon::parse($stats['last_backup'])->format('d/m/Y H:i') }}</div>
                                    <div class="text-secondary small">{{ \Carbon\Carbon::parse($stats['last_backup'])->diffForHumans() }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-secondary">
                                <i class="ti ti-backup icon icon-lg mb-2"></i>
                                <div>Belum ada backup</div>
                                <div class="small">Buat backup pertama untuk keamanan data</div>
                            </div>
                        @endif
                    </div>
                </div> --}}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle maintenance mode toggle
    const maintenanceToggle = document.querySelector('input[type="checkbox"][onchange*="maintenance"]');
    if (maintenanceToggle) {
        maintenanceToggle.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Aktifkan mode maintenance? Hanya SuperAdmin yang dapat mengakses sistem.')) {
                    this.checked = false;
                    return false;
                }
            }
        });
    }

    // Auto-save maintenance message when typing
    const maintenanceMessage = document.querySelector('textarea[name="maintenance_message"]');
    if (maintenanceMessage) {
        let timeout;
        maintenanceMessage.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Auto-save could be implemented here
            }, 2000);
        });
    }

    // Confirm dangerous actions
    document.querySelectorAll('button[onclick*="confirm"]').forEach(button => {
        button.addEventListener('click', function(e) {
            const action = this.textContent.trim();
            if (action.includes('Backup') || action.includes('Bersihkan')) {
                e.preventDefault();
                if (confirm(`Yakin ingin ${action.toLowerCase()}?`)) {
                    this.form.submit();
                }
            }
        });
    });
});
</script>
@endpush
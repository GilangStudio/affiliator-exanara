<!doctype html>
<!--
* Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
* @version 1.2.0
* @link https://tabler.io
* Copyright 2018-2025 The Tabler Authors
* Copyright 2018-2025 codecalm.net Paweł Kuna
* Licensed under MIT (https://github.com/tabler/tabler/blob/master/LICENSE)
-->
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Sign up</title>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('icons/tabler-icons.min.css') }}" rel="stylesheet" />
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN CUSTOM FONT -->
    <style>
        @import url("https://rsms.me/inter/inter.css");
    </style>
    <!-- END CUSTOM FONT -->
</head>

<body>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                {{-- IMAGE --}}
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Buat akun baru</h2>

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-exclamation-circle icon alert-icon me-2"></i>
                            </div>
                            <div>{{ session('error') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-exclamation-circle icon alert-icon me-2"></i>
                            </div>
                            <div>{{ $errors->first() }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @endif

                    <form action="{{ route('register') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Whatsapp</label>
                            {{-- <div class="input-group">
                                <select name="country_code" class="form-select" style="max-width: 100px;">
                                    <option value="+62" {{ old('country_code', '+62' )=='+62' ? 'selected' : '' }}>+62
                                    </option>
                                    <option value="+1" {{ old('country_code')=='+1' ? 'selected' : '' }}>+1</option>
                                    <option value="+65" {{ old('country_code')=='+65' ? 'selected' : '' }}>+65</option>
                                    <option value="+60" {{ old('country_code')=='+60' ? 'selected' : '' }}>+60</option>
                                </select>

                            </div> --}}
                            <input type="tel" name="phone" class="form-control" placeholder="081234567890"
                                value="{{ old('phone') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="passwordInput" name="password" class="form-control"
                                    placeholder="Password Anda" autocomplete="off" required />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" id="togglePassword" title="Tampilkan password">
                                        <i class="ti ti-eye icon icon-1"></i>
                                    </a>
                                </span>
                            </div>
                            <small class="form-hint">Minimal 8 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="passwordConfirmationInput" name="password_confirmation"
                                    class="form-control" placeholder="Ulangi password Anda" autocomplete="off" required />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" id="togglePasswordConfirmation"
                                        title="Tampilkan password">
                                        <i class="ti ti-eye icon icon-1"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" required />
                                <span class="form-check-label">Saya setuju dengan <a href="#" target="_blank">syarat dan
                                        ketentuan</a></span>
                            </label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100">Daftar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center text-secondary mt-3">
                Sudah punya akun? <a href="{{ route('login') }}" tabindex="-1">Masuk sekarang</a>
            </div>
        </div>
    </div>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{ asset('js/tabler.min.js') }}" defer></script>
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    @include('components.scripts.global')

    <!-- BEGIN CUSTOM SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password toggle functionality
            function setupPasswordToggle(inputId, toggleId) {
                const passwordInput = document.getElementById(inputId);
                const togglePassword = document.getElementById(toggleId);
                const eyeIcon = togglePassword.querySelector('i');

                togglePassword.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Toggle the type attribute
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle the icon
                    if (type === 'password') {
                        eyeIcon.classList.remove('ti-eye-off');
                        eyeIcon.classList.add('ti-eye');
                        togglePassword.setAttribute('title', 'Tampilkan password');
                    } else {
                        eyeIcon.classList.remove('ti-eye');
                        eyeIcon.classList.add('ti-eye-off');
                        togglePassword.setAttribute('title', 'Sembunyikan password');
                    }
                });
            }

            // Setup password toggles
            setupPasswordToggle('passwordInput', 'togglePassword');
            setupPasswordToggle('passwordConfirmationInput', 'togglePasswordConfirmation');
        });
    </script>
    <!-- END CUSTOM SCRIPTS -->
</body>

</html>
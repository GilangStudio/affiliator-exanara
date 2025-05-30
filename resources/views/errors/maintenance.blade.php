<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Maintenance Mode</title>
    <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('icons/tabler-icons.min.css') }}" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');
        .maintenance-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .maintenance-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .maintenance-title {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .maintenance-message {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            animation: loading 2s infinite;
        }
        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(400%); }
        }
    </style>
</head>
<body>
    <div class="maintenance-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="maintenance-card p-5 text-center">
                        <div class="maintenance-icon">
                            <i class="ti ti-tools text-white" style="font-size: 2.5rem;"></i>
                        </div>
                        
                        <h1 class="maintenance-title h2">
                            Sistem Sedang Maintenance
                        </h1>
                        
                        <div class="maintenance-message">
                            {{ $message }}
                        </div>
                        
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        
                        <div class="text-secondary small mb-4">
                            Kami sedang melakukan perbaikan untuk meningkatkan layanan
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-primary">
                                    <i class="ti ti-shield-check icon icon-lg mb-2"></i>
                                    <div class="small">Keamanan</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-blue">
                                    <i class="ti ti-speedboat icon icon-lg mb-2"></i>
                                    <div class="small">Performa</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-warning">
                                    <i class="ti ti-star icon icon-lg mb-2"></i>
                                    <div class="small">Fitur Baru</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            @if (Auth::check())
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left me-1"></i>
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Kembali ke Login
                                </a>
                            @endif

                        </div>
                        
                        {{-- <div class="mt-4 pt-4 border-top">
                            <div class="text-secondary small">
                                <i class="ti ti-clock me-1"></i>
                                Estimasi selesai: {{ now()->addHours(2)->format('H:i') }} WIB
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const icon = document.querySelector('.maintenance-icon');
            
            icon.addEventListener('mouseover', function() {
                this.style.transform = 'scale(1.1) rotate(10deg)';
                this.style.transition = 'all 0.3s ease';
            });
            
            icon.addEventListener('mouseout', function() {
                this.style.transform = 'scale(1) rotate(0deg)';
            });
        });
    </script>
</body>
</html>
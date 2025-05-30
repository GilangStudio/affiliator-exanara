<!--  BEGIN SIDEBAR  -->
<aside class="navbar navbar-vertical navbar-expand-lg navbar-transparent">
    <div class="container-fluid">
        <!-- BEGIN NAVBAR TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- END NAVBAR TOGGLER -->
        <!-- BEGIN NAVBAR LOGO -->
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}">
                @php
                $siteLogo = \App\Models\SystemSetting::getValue('site_logo');
                $siteName = \App\Models\SystemSetting::getValue('site_name', 'Affiliator System');
                @endphp

                @if($siteLogo)
                <img src="{{ asset('storage/' . $siteLogo) }}" width="110" height="32" alt="{{ $siteName }}"
                    class="navbar-brand-image">
                @else
                {{ $siteName }}
                @endif
            </a>
        </h1>
        <!-- END NAVBAR LOGO -->
        <div class="navbar-nav flex-row d-lg-none">
            <div class="d-none d-lg-flex">
                <div class="nav-item dropdown d-none d-md-flex">
                    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1"
                        aria-label="Show notifications" data-bs-auto-close="outside" aria-expanded="false">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/bell -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-1">
                            <path
                                d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                        </svg>
                        <span class="badge bg-red"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                        <div class="card">
                            <div class="card-header d-flex">
                                <h3 class="card-title">Notifications</h3>
                                <div class="btn-close ms-auto" data-bs-dismiss="dropdown"></div>
                            </div>
                            <div class="list-group list-group-flush list-group-hoverable">
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span
                                                class="status-dot status-dot-animated bg-red d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 1</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">Change deprecated
                                                html tags to text
                                                decoration classes (#29604)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-muted icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto"><span class="status-dot d-block"></span></div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Example 2</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">
                                                justify-content:between ⇒
                                                justify-content:space-between (#29734)</div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="list-group-item-actions show">
                                                <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon text-yellow icon-2">
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <a href="#" class="btn btn-2 w-100"> Archive all </a>
                                    </div>
                                    <div class="col">
                                        <a href="#" class="btn btn-2 w-100"> Mark all as read </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url(./static/avatars/000m.jpg)"> </span>
                    <div class="d-none d-xl-block ps-2">
                        <div>Paweł Kuna</div>
                        <div class="mt-1 small text-secondary">UI Designer</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="#" class="dropdown-item">Status</a>
                    <a href="./profile.html" class="dropdown-item">Profile</a>
                    <a href="#" class="dropdown-item">Feedback</a>
                    <div class="dropdown-divider"></div>
                    <a href="./settings.html" class="dropdown-item">Settings</a>
                    <a href="./sign-in.html" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <!-- BEGIN NAVBAR MENU -->
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-dashboard icon icon-1"></i>
                        </span>
                        <span class="nav-link-title"> Dashboard </span>
                    </a>
                </li>

                @if(auth()->user()->role === 'superadmin')
                <!-- User Management -->
                {{-- <li class="nav-item {{ Route::is('superadmin.users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('superadmin.users.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users icon icon-1"></i>
                        </span>
                        <span class="nav-link-title"> Users </span>
                    </a>
                </li> --}}

                <!-- Affiliator Management -->
                <li class="nav-item {{ Route::is('superadmin.affiliators.*')? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('superadmin.affiliators.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-users-group icon icon-1"></i>
                        </span>
                        <span class="nav-link-title">Affiliators</span>
                    </a>
                </li>

                <!-- Projects Management -->
                <li class="nav-item {{ Route::is('superadmin.projects.*')? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('superadmin.projects.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-folder icon icon-1"></i>
                        </span>
                        <span class="nav-link-title">Projects</span>
                    </a>
                </li>

                <!-- Support & FAQ -->
                <li class="nav-item dropdown {{ request()->routeIs('superadmin.faqs.*') || request()->routeIs('superadmin.support.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('superadmin.faqs.*') || request()->routeIs('superadmin.support.*') ? 'show' : '' }}" 
                       href="#navbar-support" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" 
                       aria-expanded="{{ request()->routeIs('superadmin.faqs.*') || request()->routeIs('superadmin.support.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-help-circle"></i>
                        </span>
                        <span class="nav-link-title">Support & FAQ</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('superadmin.faqs.*') || request()->routeIs('superadmin.support.*') ? 'show' : '' }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ request()->routeIs('superadmin.faqs.*') ? 'active' : '' }}" 
                                   href="{{ route('superadmin.faqs.index') }}">
                                    <i class="ti ti-help me-2"></i>
                                    Kelola FAQ
                                </a>
                                {{-- <a class="dropdown-item {{ request()->routeIs('superadmin.support.*') ? 'active' : '' }}" 
                                   href="{{ route('superadmin.support.index') }}">
                                    <i class="ti ti-headset me-2"></i>
                                    Support Tickets
                                    @php
                                        $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
                                    @endphp
                                    @if($openTickets > 0)
                                        <span class="badge badge-sm bg-red text-white ms-1">{{ $openTickets }}</span>
                                    @endif
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Settings Menu - MENU UTAMA SETTINGS -->
                <li class="nav-item dropdown {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('superadmin.settings.*') ? 'show' : '' }}"
                        href="#navbar-settings" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button"
                        aria-expanded="{{ request()->routeIs('superadmin.settings.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="ti ti-settings"></i>
                        </span>
                        <span class="nav-link-title">Pengaturan Sistem</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('superadmin.settings.*') ? 'show' : '' }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ request()->get('tab') == 'general' || (request()->routeIs('superadmin.settings.index') && !request()->get('tab')) ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'general']) }}">
                                    <i class="ti ti-home me-2"></i>
                                    Pengaturan Umum
                                </a>
                                <a class="dropdown-item {{ request()->get('tab') == 'commission' ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'commission']) }}">
                                    <i class="ti ti-percentage me-2"></i>
                                    Komisi
                                </a>
                                <a class="dropdown-item {{ request()->get('tab') == 'notification' ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'notification']) }}">
                                    <i class="ti ti-bell me-2"></i>
                                    Notifikasi
                                </a>
                                {{-- <a class="dropdown-item {{ request()->get('tab') == 'security' ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'security']) }}">
                                    <i class="ti ti-shield me-2"></i>
                                    Keamanan
                                </a> --}}
                                <a class="dropdown-item {{ request()->get('tab') == 'profile' ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'profile']) }}">
                                    <i class="ti ti-user me-2"></i>
                                    Profil Saya
                                </a>
                                <a class="dropdown-item {{ request()->get('tab') == 'maintenance' ? 'active' : '' }}"
                                    href="{{ route('superadmin.settings.index', ['tab' => 'maintenance']) }}">
                                    <i class="ti ti-tools me-2"></i>
                                    Maintenance
                                    @if(\App\Models\SystemSetting::getValue('maintenance_mode', false))
                                    <span class="badge badge-sm bg-orange text-white ms-1">ON</span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @endif
            </ul>
            <!-- END NAVBAR MENU -->
        </div>
    </div>
</aside>
<!--  END SIDEBAR  -->
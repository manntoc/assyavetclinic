@php
    $brandData = \App\Services\Instagram\InstagramBrandResolver::resolve();
    $logoUrl = $brandData['logo_url'] ?? asset('uploads/img/general/1722842083-148color.png');
    $clinicName = $brandData['clinic_name'] ?? 'Assyavet Clinic';
@endphp
<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header p-3 pb-2 border-bottom position-relative">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="logo text-center">
                    <a href="{{ route('dashboard-user') }}" class="d-inline-flex justify-content-center align-items-center text-decoration-none" title="{{ $clinicName }}">
                        @if(!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="{{ $clinicName }}" class="sidebar-brand-logo img-fluid mx-auto" style="max-height: 40px; max-width: 170px; width: auto; height: auto; object-fit: contain; display: block;">
                        @else
                            <span class="fw-bold fs-5" style="color: #2d8aba;">
                                <i class="bi bi-hospital-fill me-1"></i> {{ $clinicName }}
                            </span>
                        @endif
                    </a>
                </div>
                <div class="toggler position-absolute end-0 me-3" style="top: 50%; transform: translateY(-50%);">
                    <button class="sidebar-toggle-btn p-1 px-2 border-0 bg-light" title="Tutup Sidebar">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="sidebar-menu p-2">
            <ul class="menu list-unstyled">
                <li class="sidebar-title text-uppercase small fw-bold text-muted px-3 py-2 mb-1">Main Menu</li>

                <!-- 1. DASHBOARD -->
                <li class="sidebar-item {{ request()->routeIs('dashboard-user') ? 'active' : '' }} mb-1">
                    <a href="{{ route('dashboard-user') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-grid-fill me-2 fs-6"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- 2. BOOKING / ANTREAN PASIEN -->
                <li class="sidebar-item {{ request()->routeIs('booking-uncompleted') || request()->routeIs('booking-completed') ? 'active' : '' }} mb-1">
                    <a href="{{ route('booking-uncompleted') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-calendar-check-fill me-2 fs-6"></i>
                        <span>Booking</span>
                    </a>
                </li>

                <!-- 2.5 NOTA ESTIMASI BIAYA (PERKIRAAN HARGA NON-BILLING) -->
                <li class="sidebar-item {{ request()->routeIs('instagram-studio*') ? 'active' : '' }} mb-1">
                    <a href="{{ route('instagram-studio.index') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-instagram me-2 fs-6"></i>
                        <span>Instagram Studio</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('estimasi-biaya*') ? 'active' : '' }} mb-1">
                    <a href="{{ route('estimasi-biaya.index') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-calculator-fill me-2 fs-6"></i>
                        <span>Nota Estimasi Biaya</span>
                    </a>
                </li>

                <!-- 2.6 LAPORAN OMZET FINANSIAL DETAIL -->
                <li class="sidebar-item {{ request()->routeIs('laporan-omzet*') ? 'active' : '' }} mb-1">
                    <a href="{{ route('laporan-omzet.index') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-graph-up-arrow me-2 fs-6"></i>
                        <span>Laporan Omzet</span>
                    </a>
                </li>


                <!-- 3. DATA MASTER (DROPDOWN CONTAINING OWNER, INVENTORI, STOK, KARYAWAN, LAYANAN) -->
                @php
                    $isMasterActive = request()->routeIs('owner') || request()->routeIs('inventori') || request()->routeIs('manajemen-stok') || request()->routeIs('karyawan') || request()->routeIs('layanan');
                @endphp
                <li class="sidebar-item has-sub {{ $isMasterActive ? 'active' : '' }} mb-1">
                    <a href="#" class='sidebar-link d-flex align-items-center text-decoration-none'>
                        <i class="bi bi-stack me-2 fs-6"></i>
                        <span>Data Master</span>
                    </a>
                    <ul class="submenu list-unstyled ps-4 py-1 {{ $isMasterActive ? 'active' : '' }}" style="{{ $isMasterActive ? 'display: block;' : '' }}">
                        <li class="submenu-item {{ request()->routeIs('owner') ? 'active' : '' }} py-1">
                            <a href="{{ route('owner') }}" class="text-decoration-none text-muted small fw-medium">
                                <i class="bi bi-people me-2"></i> Customer
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('inventori') ? 'active' : '' }} py-1">
                            <a href="{{ route('inventori') }}" class="text-decoration-none text-muted small fw-medium">
                                <i class="bi bi-box-seam me-2"></i> Gudang
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('manajemen-stok') ? 'active' : '' }} py-1">
                            <a href="{{ route('manajemen-stok') }}" class="text-decoration-none text-muted small fw-medium">
                                <i class="bi bi-cart-check me-2"></i> Pickup
                            </a>
                        </li>
                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'superadmin')
                            <li class="submenu-item {{ request()->routeIs('karyawan') ? 'active' : '' }} py-1">
                                <a href="{{ route('karyawan') }}" class="text-decoration-none text-muted small fw-medium">
                                    <i class="bi bi-person-badge me-2"></i> Karyawan
                                </a>
                            </li>
                            <li class="submenu-item {{ request()->routeIs('layanan') ? 'active' : '' }} py-1">
                                <a href="{{ route('layanan') }}" class="text-decoration-none text-muted small fw-medium">
                                    <i class="bi bi-gear me-2"></i> Layanan
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- 4. PEKERJAAN ANDA (FOR KARYAWAN) -->
                @if(Auth::user()->role == 'karyawan')
                    <li class="sidebar-item {{ request()->routeIs('pekerjaansaya') ? 'active' : '' }} mb-1">
                        <a href="{{ route('pekerjaansaya') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                            <i class="bi bi-briefcase-fill me-2 fs-6"></i>
                            <span>Pekerjaan Anda</span>
                        </a>
                    </li>
                @endif

                @if(Auth::user()->role == 'superadmin' || Auth::user()->role == 'admin')
                    <li class="sidebar-item mb-1">
                        <a href="{{ route('dashboard') }}" class='sidebar-link d-flex align-items-center text-decoration-none'>
                            <i class="bi bi-laptop me-2 fs-6"></i>
                            <span>Website</span>
                        </a>
                    </li>
                @endif

                <hr class="sidebar-divider my-3">

                <!-- 5. LOGOUT -->
                <li class="sidebar-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" class='sidebar-link d-flex align-items-center text-decoration-none text-danger'>
                            <i class="bi bi-box-arrow-left me-2 fs-6 text-danger"></i>
                            <span>Logout</span>
                        </a>
                    </form>
                </li>
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i class="bi bi-x-lg"></i></button>
    </div>
</div>
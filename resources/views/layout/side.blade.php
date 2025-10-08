<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
id="sidenav-main">
<div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
        aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="index.html" target="_blank">
        <img src="https://blue.kumparan.com/image/upload/fl_progressive,fl_lossy,c_fill,q_auto:best,w_640/v1634025439/47d041e6a9a783adb633ac299ceff217511007597335cc6b4e78bc8531c0f933.jpg" class="navbar-brand-img" alt="main_logo"
            style="width: 45px; max-height: max-content; display:flex; margin: auto;">
        <!-- <span class="ms-1 font-weight-bold">SWM-Dashboard</span> -->
    </a>
</div>
<hr class="horizontal mt-0">
<!-- Sidebar -->
<div class="navbar-collapse  w-auto " id="sidenav-collapse-main">
   <ul class="navbar-nav">
       <li class="nav-item">
           <a class="nav-link {{ Request::is('*dashboard*') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                   <div
                       class="icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                       <i class="ri-tv-2-line text-dark text-sm opacity-10"></i>
                   </div>
                   <span class="nav-link-text ms-1">Dashboard</span>
               </a>
       </li>
       <li class="nav-item">
            <a class="nav-link {{ Request::is('*karyawan*') ? 'active' : '' }}"
                href="{{ route('karyawan.index') }}">
                <div
                    class="icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ri-tools-fill text-dark text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Karyawan</span>
            </a>
       </li>
       <li class="nav-item">
            <a class="nav-link {{ Request::is('*jadwal*') ? 'active' : '' }}"
                href="{{ route('jadwal.index') }}">
                <div
                    class="icon-sm border-radius-md text-center me-1 d-flex align-items-center justify-content-center">
                    <i class="ri-draft-fill text-dark text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Jadwal</span>
            </a>
       </li>
       <li class="nav-item">
            <a class="nav-link {{ Request::is('*libur*') ? 'active' : '' }}"
                href="{{ route('libur.index', ['bulan' => now()->month, 'tahun' => now()->year]) }}">
                <div
                    class="icon-sm border-radius-md text-center me-1 d-flex align-items-center justify-content-center">
                    <i class="ri-draft-fill text-dark text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Libur</span>
            </a>
       </li>
   </ul>
</div>
</aside>

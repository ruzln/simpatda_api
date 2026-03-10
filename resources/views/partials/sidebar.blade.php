<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="sidebar-brand-text mx-3">SIMPADTA</div>
    </a>

    <hr class="sidebar-divider my-0">

        <li class="nav-item {{ request()->is('skprd/combined') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('skprd.combined') }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    </li>

    <li class="nav-item {{ request()->is('skprd/self') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('skprd.self') }}">
            <i class="fas fa-bullhorn"></i>
            <span>Self Assessment</span>
        </a>
    </li>

    <li class="nav-item {{ request()->is('skprd/office') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('skprd.office') }}">
            <i class="fas fa-building"></i>
            <span>Office Assessment</span>
        </a>
    </li>

    <!-- <li class="nav-item {{ request()->is('pbb') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('pbb.index') }}">
            <i class="fas fa-hotel"></i>
            <span>PBB-P2</span>
        </a>
    </li> -->

    <hr class="sidebar-divider d-none d-md-block">

</ul>

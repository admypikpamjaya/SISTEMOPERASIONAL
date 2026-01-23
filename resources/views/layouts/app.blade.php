<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Operasional Yayasan YPIK</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <!-- SweetAlert -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Extras -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div id="loading-overlay">
    <i class="fas fa-2x fa-spinner fa-spin"></i>
</div>

<div class="wrapper">

    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link">
                    @if(Auth::check())
                        {{ Auth::user()->name }} ({{ Auth::user()->role }})
                    @endif
                </a>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <!-- BRAND -->
        <a href="#" class="brand-link">
            <img src="{{ asset('images/logo_ypik.webp') }}"
                 class="brand-image img-circle elevation-3"
                 style="opacity:.8">
            <span class="brand-text font-weight-light">SOY YPIK PAM JAYA</span>
        </a>

        <!-- SIDEBAR MENU -->
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false">

                    @foreach(config('menu') as $menu)

                        @php
                            $hasChildren = isset($menu['children']);
                            $isActiveParent = false;

                            if ($hasChildren) {
                                $isActiveParent = collect($menu['children'])
                                    ->pluck('route')
                                    ->contains(fn($r) => request()->routeIs($r));
                            }
                        @endphp

                        @if(
                            empty($menu['module_name']) ||
                            app(\App\Services\AccessControl\PermissionService::class)
                                ->checkAccess(
                                    auth()->user(),
                                    \App\Enums\Portal\PortalPermission::from($menu['module_name'] . '.read')->value
                                )
                        )

                            <li class="nav-item {{ $hasChildren ? 'has-treeview' : '' }} {{ $isActiveParent ? 'menu-open' : '' }}">

                                <a href="{{ $hasChildren ? route($menu['route']) : route($menu['route']) }}"
                                   class="nav-link {{ (!$hasChildren && request()->routeIs($menu['route'])) || $isActiveParent ? 'active' : '' }}">

                                    <i class="nav-icon {{ $menu['icon'] }}"></i>
                                    <p>
                                        {{ $menu['label'] }}
                                        @if($hasChildren)
                                            <i class="right fas fa-angle-left"></i>
                                        @endif
                                    </p>
                                </a>

                                {{-- CHILDREN --}}
                                @if($hasChildren)
                                    <ul class="nav nav-treeview">
                                        @foreach($menu['children'] as $child)
                                            <li class="nav-item">
                                                <a href="{{ route($child['route']) }}"
                                                   class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                                    <i class="nav-icon {{ $child['icon'] }}"></i>
                                                    <p>{{ $child['label'] }}</p>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                            </li>
                        @endif

                    @endforeach

                </ul>
            </nav>
        </div>
    </aside>

    <!-- CONTENT -->
    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">@yield('section_name')</h1>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>

    </div>
</div>

<!-- JS -->
<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('js/helper.js') }}"></script>

@stack('component_js')
@yield('js')

@if(session()->has('success'))
<script>
    Notification.success("{{ session('success') }}");
</script>
@endif

</body>
</html>
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
                            $isHiddenOnCurrentRoute = false;

                            if ($hasChildren) {
                                $isActiveParent = collect($menu['children'])
                                    ->pluck('route')
                                    ->contains(fn($r) => request()->routeIs($r));
                            }

                            if (!empty($menu['hide_on_routes'])) {
                                $hidePatterns = is_array($menu['hide_on_routes'])
                                    ? $menu['hide_on_routes']
                                    : [$menu['hide_on_routes']];

                                $isHiddenOnCurrentRoute = collect($hidePatterns)
                                    ->contains(fn($pattern) => request()->routeIs($pattern));
                            }
                        @endphp

                        @if(
                            !$isHiddenOnCurrentRoute &&
                            (
                                empty($menu['module_name']) ||
                                app(\App\Services\AccessControl\PermissionService::class)
                                    ->checkAccess(
                                        auth()->user(),
                                        \App\Enums\Portal\PortalPermission::from($menu['module_name'] . '.read')->value
                                    )
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
                                            @php
                                                $canAccessChild = empty($child['module_name']) || app(\App\Services\AccessControl\PermissionService::class)
                                                    ->checkAccess(
                                                        auth()->user(),
                                                        \App\Enums\Portal\PortalPermission::from($child['module_name'] . '.read')->value
                                                    );
                                            @endphp

                                            @if($canAccessChild)
                                                <li class="nav-item">
                                                    <a href="{{ route($child['route']) }}"
                                                       class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                                        <i class="nav-icon {{ $child['icon'] }}"></i>
                                                        <p>{{ $child['label'] }}</p>
                                                    </a>
                                                </li>
                                            @endif
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

@php
    $canReadReminder = Auth::check()
        && app(\App\Services\AccessControl\PermissionService::class)->checkAccess(
            auth()->user(),
            \App\Enums\Portal\PortalPermission::ADMIN_REMINDER_READ->value
        );
@endphp

@if($canReadReminder)
<script>
    (function () {
        const alertEndpoint = @json(route('admin.reminders.alerts'));
        const reminderPageUrl = @json(route('admin.reminders.index'));
        const announcementPageUrl = @json(route('admin.announcements.index'));

        const pollIntervalMs = 60000;
        const cooldownMs = {
            upcoming: 10 * 60 * 1000,
            due: 2 * 60 * 1000
        };
        const lastShownAt = {};
        let isPolling = false;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function canShowAlert(primaryAlert) {
            if (!primaryAlert) {
                return false;
            }

            const signature = `${primaryAlert.id}:${primaryAlert.state}`;
            const now = Date.now();
            const requiredCooldown = primaryAlert.state === 'due'
                ? cooldownMs.due
                : cooldownMs.upcoming;

            if ((lastShownAt[signature] ?? 0) + requiredCooldown > now) {
                return false;
            }

            lastShownAt[signature] = now;
            return true;
        }

        function buildAlertHtml(alerts) {
            const listItems = alerts.slice(0, 5).map((alert) => {
                const title = escapeHtml(alert.title);
                const hint = escapeHtml(alert.hint);
                const schedule = escapeHtml(alert.remind_at_label);
                return `<li><strong>${title}</strong><br><small>${hint} (Jadwal: ${schedule})</small></li>`;
            });

            const hiddenCount = Math.max(0, alerts.length - 5);
            const hiddenSummary = hiddenCount > 0
                ? `<p class="mt-2 mb-0 text-muted">+${hiddenCount} reminder lain juga aktif.</p>`
                : '';

            return `<ul class="text-left pl-3 mb-0">${listItems.join('')}</ul>${hiddenSummary}`;
        }

        function showReminderPopup(alerts) {
            const dueAlert = alerts.find((alert) => alert.state === 'due');
            const primaryAlert = dueAlert ?? alerts[0];

            if (!canShowAlert(primaryAlert) || Swal.isVisible()) {
                return;
            }

            const hasAnnouncementReminder = alerts.some(
                (alert) => alert.type === 'ANNOUNCEMENT'
            );
            const announcementRedirectUrl = (dueAlert && dueAlert.announcement_url)
                ? dueAlert.announcement_url
                : ((alerts.find((alert) => alert.announcement_url) || {}).announcement_url || announcementPageUrl);

            Swal.fire({
                title: dueAlert ? 'Reminder Hari-H Aktif' : 'Reminder Mendekati Waktu',
                html: buildAlertHtml(alerts),
                icon: 'warning',
                width: '32em',
                confirmButtonText: 'Kelola Reminder',
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                showDenyButton: hasAnnouncementReminder,
                denyButtonText: 'Buka Announcement'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = reminderPageUrl;
                    return;
                }

                if (result.isDenied) {
                    window.location.href = announcementRedirectUrl;
                }
            });
        }

        function pollReminderAlerts() {
            if (isPolling) {
                return;
            }

            isPolling = true;

            Http.get(alertEndpoint)
                .done((response) => {
                    const alerts = response && Array.isArray(response.alerts)
                        ? response.alerts
                        : [];

                    if (alerts.length > 0) {
                        showReminderPopup(alerts);
                    }
                })
                .always(() => {
                    isPolling = false;
                });
        }

        pollReminderAlerts();
        setInterval(pollReminderAlerts, pollIntervalMs);
    })();
</script>
@endif

@stack('component_js')
@yield('js')

@if(session()->has('success'))
<script>
    Notification.success("{{ session('success') }}");
</script>
@endif

@if(session()->has('error'))
<script>
    Notification.error(@json(session('error')), 15000);
</script>
@endif

</body>
</html>

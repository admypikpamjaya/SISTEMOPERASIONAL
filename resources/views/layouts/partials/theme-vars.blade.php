@php($websiteThemeVariables = app(\App\Services\Theme\ThemeService::class)->cssVariables())
@php($websiteThemeDarkBrandVariables = collect($websiteThemeVariables)->only([
    '--app-accent',
    '--app-accent-strong',
    '--app-sidebar-bg',
    '--app-sidebar-strong',
    '--app-row-hover',
    '--app-row-selected',
    '--app-row-selected-strong',
    '--blue-primary',
    '--blue-mid',
    '--blue-dark',
    '--blue-deeper',
    '--blue-glow',
    '--accent',
    '--accent-cyan',
    '--accent-green',
    '--primary',
    '--primary-color',
    '--primary-hover',
    '--primary-dark',
    '--grad',
    '--grad-hero',
    '--ypk-blue',
    '--ypk-blue-dark',
    '--fa-blue',
    '--fa-blue-dark',
    '--tg-primary',
    '--tg-primary-soft',
])->all())
<style id="website-theme-vars">
    :root {
        @foreach($websiteThemeVariables as $name => $value)
            {{ $name }}: {{ $value }};
        @endforeach
    }

    body.dark-mode {
        @foreach($websiteThemeDarkBrandVariables as $name => $value)
            {{ $name }}: {{ $value }};
        @endforeach
    }

    .main-sidebar .nav-sidebar .nav-link.active,
    body.dark-mode .main-sidebar .nav-sidebar .nav-link.active,
    .btn-primary,
    .btn-primary:not(:disabled):not(.disabled).active,
    .btn-primary:not(:disabled):not(.disabled):active,
    .show > .btn-primary.dropdown-toggle {
        background: linear-gradient(135deg, var(--app-accent), var(--app-accent-strong)) !important;
        border-color: var(--app-accent-strong) !important;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        border-color: var(--app-accent-strong) !important;
        box-shadow: 0 10px 24px var(--app-row-selected-strong) !important;
    }

    a,
    .page-link,
    .text-primary {
        color: var(--app-accent-strong);
    }

    .page-item.active .page-link {
        background: var(--app-accent);
        border-color: var(--app-accent);
    }

    .badge-primary,
    .bg-primary {
        background-color: var(--app-accent) !important;
    }

    .border-primary {
        border-color: var(--app-accent) !important;
    }
</style>

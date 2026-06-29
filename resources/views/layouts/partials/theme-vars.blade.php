@php($websiteThemeService = app(\App\Services\Theme\ThemeService::class))
@php($websiteTheme = $websiteTheme ?? $websiteThemeService->current())
@php($websiteThemeVariables = $websiteThemeService->cssVariables($websiteTheme))
@php($websiteThemeImportantSuffix = !empty($websiteThemeImportant) ? ' !important' : '')
@php($websiteThemeStyleId = $websiteThemeStyleId ?? 'website-theme-vars')
@php($websiteThemeBrandVariables = collect($websiteThemeVariables)->only([
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
@php($websiteThemeDarkVariables = ($websiteTheme['source'] ?? 'default') === 'default' ? $websiteThemeBrandVariables : $websiteThemeVariables)
<style id="{{ $websiteThemeStyleId }}">
    :root {
        @foreach($websiteThemeVariables as $name => $value)
            {{ $name }}: {{ $value }}{{ $websiteThemeImportantSuffix }};
        @endforeach
    }

    body.dark-mode {
        @foreach($websiteThemeDarkVariables as $name => $value)
            {{ $name }}: {{ $value }}{{ $websiteThemeImportantSuffix }};
        @endforeach
    }

    body,
    body .content-wrapper {
        background: var(--app-bg) !important;
        color: var(--app-text) !important;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
        color: var(--app-text) !important;
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

    .main-header.navbar {
        border-bottom-color: var(--app-border) !important;
    }

    .card,
    .modal-content,
    .dropdown-menu,
    .theme-panel,
    .theme-hero,
    body.dark-mode .card,
    body.dark-mode .modal-content,
    body.dark-mode .dropdown-menu,
    body.dark-mode .theme-panel,
    body.dark-mode .theme-hero {
        border-color: var(--app-border) !important;
    }
</style>

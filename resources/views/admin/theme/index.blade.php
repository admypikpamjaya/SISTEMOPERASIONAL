@extends('layouts.app')

@section('section_name', __('app.website_theme.title'))

@section('content')
@php
    $colors = $theme['colors'];
    $defaultColors = $defaults['colors'];
    $swatches = [
        'primary' => ['icon' => 'fas fa-bolt', 'label' => __('app.website_theme.primary')],
        'secondary' => ['icon' => 'fas fa-water', 'label' => __('app.website_theme.secondary')],
        'accent' => ['icon' => 'fas fa-check-circle', 'label' => __('app.website_theme.accent')],
        'sidebar' => ['icon' => 'fas fa-bars', 'label' => __('app.website_theme.sidebar')],
        'background' => ['icon' => 'fas fa-layer-group', 'label' => __('app.website_theme.background')],
        'surface' => ['icon' => 'fas fa-square', 'label' => __('app.website_theme.surface')],
    ];
@endphp

<style>
    .theme-page {
        color: var(--app-text);
        padding-bottom: 2rem;
    }

    .theme-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid var(--app-border);
        border-radius: 16px;
        background:
            linear-gradient(135deg, var(--app-row-selected), transparent 44%),
            var(--app-surface);
        box-shadow: var(--app-shadow);
    }

    .theme-title {
        display: flex;
        align-items: center;
        gap: .9rem;
    }

    .theme-title-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 16px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--app-accent), var(--app-accent-strong));
        box-shadow: 0 16px 30px var(--app-row-selected-strong);
        font-size: 1.25rem;
    }

    .theme-title h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .theme-title p {
        margin: .18rem 0 0;
        color: var(--app-text-muted);
        font-weight: 600;
    }

    .theme-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .48rem .75rem;
        border-radius: 999px;
        background: var(--app-row-selected);
        color: var(--app-accent-strong);
        border: 1px solid var(--app-row-selected-strong);
        font-weight: 800;
        font-size: .82rem;
    }

    .theme-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
        gap: 1rem;
        align-items: start;
    }

    .theme-panel {
        border: 1px solid var(--app-border);
        border-radius: 16px;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
        overflow: hidden;
    }

    .theme-panel-head {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--app-border);
        background: var(--app-surface-soft);
    }

    .theme-panel-head i {
        width: 2.15rem;
        height: 2.15rem;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: var(--app-accent-strong);
        background: var(--app-row-selected);
    }

    .theme-panel-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .theme-panel-head p {
        margin: .12rem 0 0;
        color: var(--app-text-muted);
        font-size: .86rem;
        font-weight: 600;
    }

    .theme-panel-body {
        padding: 1.15rem;
    }

    .theme-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }

    .theme-color-field {
        border: 1px solid var(--app-border);
        border-radius: 14px;
        padding: .85rem;
        background: var(--app-surface-soft);
    }

    .theme-color-field label {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .65rem;
        color: var(--app-text-muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .theme-color-row {
        display: grid;
        grid-template-columns: 3rem minmax(0, 1fr);
        gap: .65rem;
        align-items: center;
    }

    .theme-color-row input[type="color"] {
        width: 3rem;
        height: 2.65rem;
        padding: .18rem;
        border: 1px solid var(--app-border);
        border-radius: 11px;
        background: var(--app-surface);
        cursor: pointer;
    }

    .theme-color-row input[type="text"],
    .theme-upload input[type="file"] {
        width: 100%;
        min-height: 2.65rem;
        border: 1px solid var(--app-border);
        border-radius: 11px;
        background: var(--app-surface);
        color: var(--app-text);
        padding: .7rem .8rem;
        font-weight: 700;
    }

    .theme-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin-top: 1rem;
    }

    .theme-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 2.7rem;
        padding: .65rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--app-border);
        color: var(--app-text);
        background: var(--app-surface-soft);
        font-weight: 800;
    }

    .theme-btn:hover {
        color: var(--app-text);
        text-decoration: none;
        background: var(--app-surface-muted);
    }

    .theme-btn.primary {
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--app-accent), var(--app-accent-strong));
        box-shadow: 0 14px 26px var(--app-row-selected-strong);
    }

    .theme-btn.danger {
        border-color: rgba(239, 68, 68, .32);
        color: #f87171;
        background: rgba(239, 68, 68, .08);
    }

    .theme-preview {
        display: grid;
        grid-template-columns: 7rem minmax(0, 1fr);
        min-height: 19rem;
        overflow: hidden;
        border: 1px solid var(--app-border);
        border-radius: 16px;
        background: var(--app-bg);
    }

    .theme-preview-sidebar {
        padding: .9rem .7rem;
        background: linear-gradient(180deg, var(--app-sidebar-bg), var(--app-sidebar-strong));
    }

    .theme-preview-dot,
    .theme-preview-menu {
        border-radius: 999px;
        background: rgba(255, 255, 255, .22);
    }

    .theme-preview-dot {
        width: 2.2rem;
        height: 2.2rem;
        margin-bottom: 1rem;
    }

    .theme-preview-menu {
        height: .75rem;
        margin-bottom: .75rem;
    }

    .theme-preview-menu.active {
        height: 2.35rem;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--app-accent), var(--app-accent-strong));
    }

    .theme-preview-main {
        padding: 1rem;
    }

    .theme-preview-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        margin-bottom: 1rem;
    }

    .theme-preview-title {
        width: 55%;
        height: 1.25rem;
        border-radius: 999px;
        background: var(--app-text);
        opacity: .86;
    }

    .theme-preview-button {
        width: 6.5rem;
        height: 2.25rem;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--app-accent), var(--app-accent-strong));
    }

    .theme-preview-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
        margin-bottom: .85rem;
    }

    .theme-preview-card {
        min-height: 4.8rem;
        border: 1px solid var(--app-border);
        border-radius: 14px;
        background: var(--app-surface);
        padding: .75rem;
    }

    .theme-preview-card::before,
    .theme-preview-card::after {
        content: "";
        display: block;
        border-radius: 999px;
        background: var(--app-text-muted);
        opacity: .55;
    }

    .theme-preview-card::before {
        width: 55%;
        height: .55rem;
        margin-bottom: .8rem;
    }

    .theme-preview-card::after {
        width: 78%;
        height: 1.1rem;
        background: var(--app-accent);
        opacity: .92;
    }

    .theme-preview-table {
        min-height: 8rem;
        border: 1px solid var(--app-border);
        border-radius: 14px;
        background: var(--app-surface);
        overflow: hidden;
    }

    .theme-preview-table div {
        height: 2rem;
        border-bottom: 1px solid var(--app-border);
        background: var(--app-surface-soft);
    }

    .theme-preview-table div:nth-child(2),
    .theme-preview-table div:nth-child(4) {
        background: var(--app-row-hover);
    }

    .theme-upload {
        display: grid;
        gap: .75rem;
    }

    .theme-help {
        margin: 0;
        color: var(--app-text-muted);
        font-size: .9rem;
        font-weight: 600;
        line-height: 1.55;
    }

    .theme-default-swatches {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: .45rem;
        margin-top: .8rem;
    }

    .theme-default-swatches span {
        height: 2rem;
        border-radius: 10px;
        border: 1px solid var(--app-border);
    }

    @media (max-width: 991.98px) {
        .theme-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .theme-hero,
        .theme-preview-bar,
        .theme-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .theme-form-grid,
        .theme-preview-cards {
            grid-template-columns: 1fr;
        }

        .theme-preview {
            grid-template-columns: 4.5rem minmax(0, 1fr);
        }
    }
</style>

<div class="theme-page">
    <div class="theme-hero">
        <div class="theme-title">
            <div class="theme-title-icon">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h2>{{ __('app.website_theme.title') }}</h2>
                <p>{{ __('app.website_theme.subtitle') }}</p>
            </div>
        </div>
        <span class="theme-badge">
            <i class="fas fa-circle"></i>
            {{ __('app.website_theme.source_' . ($theme['source'] ?? 'default')) }}
        </span>
    </div>

    <div class="theme-grid">
        <div class="theme-panel">
            <div class="theme-panel-head">
                <i class="fas fa-sliders-h"></i>
                <div>
                    <h3>{{ __('app.website_theme.custom_palette') }}</h3>
                    <p>{{ __('app.website_theme.custom_palette_hint') }}</p>
                </div>
            </div>
            <div class="theme-panel-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>{{ __('app.website_theme.validation_failed') }}</strong>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.theme.update') }}" id="theme-form">
                    @csrf
                    @method('PUT')

                    <div class="theme-form-grid">
                        @foreach($swatches as $key => $meta)
                            <div class="theme-color-field">
                                <label for="theme-{{ $key }}">
                                    <i class="{{ $meta['icon'] }}"></i>
                                    {{ $meta['label'] }}
                                </label>
                                <div class="theme-color-row">
                                    <input
                                        type="color"
                                        id="theme-{{ $key }}"
                                        data-theme-color="{{ $key }}"
                                        value="{{ old($key, $colors[$key]) }}"
                                        aria-label="{{ $meta['label'] }}"
                                    >
                                    <input
                                        type="text"
                                        name="{{ $key }}"
                                        data-theme-text="{{ $key }}"
                                        value="{{ old($key, $colors[$key]) }}"
                                        maxlength="7"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        required
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="theme-actions">
                        <button type="submit" class="theme-btn primary">
                            <i class="fas fa-save"></i>
                            {{ __('app.website_theme.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="theme-panel">
            <div class="theme-panel-head">
                <i class="fas fa-eye"></i>
                <div>
                    <h3>{{ __('app.website_theme.preview') }}</h3>
                    <p>{{ __('app.website_theme.preview_hint') }}</p>
                </div>
            </div>
            <div class="theme-panel-body">
                <div class="theme-preview" id="theme-preview">
                    <div class="theme-preview-sidebar">
                        <div class="theme-preview-dot"></div>
                        <div class="theme-preview-menu active"></div>
                        <div class="theme-preview-menu"></div>
                        <div class="theme-preview-menu"></div>
                        <div class="theme-preview-menu"></div>
                    </div>
                    <div class="theme-preview-main">
                        <div class="theme-preview-bar">
                            <div class="theme-preview-title"></div>
                            <div class="theme-preview-button"></div>
                        </div>
                        <div class="theme-preview-cards">
                            <div class="theme-preview-card"></div>
                            <div class="theme-preview-card"></div>
                            <div class="theme-preview-card"></div>
                        </div>
                        <div class="theme-preview-table">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>

                <div class="theme-default-swatches" aria-label="{{ __('app.website_theme.default_theme') }}">
                    @foreach(['primary', 'secondary', 'accent', 'sidebar', 'background', 'surface'] as $key)
                        <span style="background: {{ $defaultColors[$key] }}"></span>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.theme.reset') }}" class="theme-actions">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="theme-btn danger" data-confirm-reset="{{ __('app.website_theme.reset_confirm') }}">
                        <i class="fas fa-undo"></i>
                        {{ __('app.website_theme.reset') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="theme-panel">
            <div class="theme-panel-head">
                <i class="fas fa-image"></i>
                <div>
                    <h3>{{ __('app.website_theme.image_palette') }}</h3>
                    <p>{{ __('app.website_theme.image_palette_hint') }}</p>
                </div>
            </div>
            <div class="theme-panel-body">
                <form method="POST" action="{{ route('admin.theme.image') }}" enctype="multipart/form-data" class="theme-upload">
                    @csrf
                    <input type="file" name="theme_image" accept="image/png,image/jpeg,image/webp" required>
                    <p class="theme-help">{{ __('app.website_theme.image_help') }}</p>
                    <div class="theme-actions mt-0">
                        <button type="submit" class="theme-btn primary">
                            <i class="fas fa-magic"></i>
                            {{ __('app.website_theme.apply_image') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="theme-panel">
            <div class="theme-panel-head">
                <i class="fas fa-info-circle"></i>
                <div>
                    <h3>{{ __('app.website_theme.behavior_title') }}</h3>
                    <p>{{ __('app.website_theme.behavior_hint') }}</p>
                </div>
            </div>
            <div class="theme-panel-body">
                <p class="theme-help">{{ __('app.website_theme.behavior_desc') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('component_js')
<script>
    (function () {
        const form = document.getElementById('theme-form');
        if (!form) {
            return;
        }

        const preview = document.getElementById('theme-preview');
        const textInputs = form.querySelectorAll('[data-theme-text]');
        const colorInputs = form.querySelectorAll('[data-theme-color]');

        function setPreviewVariable(name, value) {
            if (!preview || !/^#[0-9A-Fa-f]{6}$/.test(value)) {
                return;
            }

            const map = {
                primary: '--app-accent',
                sidebar: '--app-sidebar-bg',
                background: '--app-bg',
                surface: '--app-surface'
            };

            if (map[name]) {
                preview.style.setProperty(map[name], value);
            }

            if (name === 'primary') {
                preview.style.setProperty('--app-accent-strong', value);
            }
        }

        colorInputs.forEach((input) => {
            input.addEventListener('input', () => {
                const key = input.dataset.themeColor;
                const text = form.querySelector(`[data-theme-text="${key}"]`);

                if (text) {
                    text.value = input.value.toUpperCase();
                }

                setPreviewVariable(key, input.value);
            });
        });

        textInputs.forEach((input) => {
            input.addEventListener('input', () => {
                const value = input.value.trim().toUpperCase();
                const key = input.dataset.themeText;
                const color = form.querySelector(`[data-theme-color="${key}"]`);

                if (/^#[0-9A-F]{6}$/.test(value) && color) {
                    color.value = value;
                    setPreviewVariable(key, value);
                }
            });
        });

        document.querySelectorAll('[data-confirm-reset]').forEach((button) => {
            button.closest('form')?.addEventListener('submit', (event) => {
                if (!confirm(button.dataset.confirmReset)) {
                    event.preventDefault();
                }
            });
        });
    })();
</script>
@endpush

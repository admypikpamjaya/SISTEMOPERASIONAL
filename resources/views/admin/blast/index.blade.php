@extends('layouts.app')

@section('title', __('app.blast.menu_title'))
@section('section_name', __('app.blast.menu_title'))

@section('content')
<div class="blast-menu-page">
    <section class="blast-menu-card">
        <div class="blast-menu-head">
            <div>
                <h2 class="blast-menu-title">{{ __('app.blast.menu_title') }}</h2>
                <p class="blast-menu-subtitle">{{ __('app.blast.menu_subtitle') }}</p>
            </div>
            <span class="blast-menu-badge">Blast Center</span>
        </div>

        <div class="blast-menu-actions">
            <a href="{{ route('admin.blast.whatsapp') }}" class="blast-btn">
                <span class="icon"><i class="fab fa-whatsapp"></i></span>
                <span class="text">
                    <strong>{{ __('app.blast.whatsapp_mass_send') }}</strong>
                    <small>{{ __('app.blast.whatsapp_mass_desc') }}</small>
                </span>
            </a>

            <a href="{{ route('admin.blast.email') }}" class="blast-btn">
                <span class="icon"><i class="fas fa-envelope"></i></span>
                <span class="text">
                    <strong>{{ __('app.blast.email_mass_send') }}</strong>
                    <small>{{ __('app.blast.email_mass_desc') }}</small>
                </span>
            </a>
        </div>
    </section>
</div>

<style>
    .blast-menu-page { min-height:60vh; padding:18px; display:grid; place-items:center; }
    .blast-menu-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:20px; }
    .blast-menu-title { margin:0; font-size:24px; font-weight:800; letter-spacing:0; }
    .blast-menu-subtitle { margin:6px 0 0; color:var(--app-text-muted); line-height:1.45; }
    .blast-menu-badge { display:inline-flex; padding:7px 10px; border-radius:999px; background:var(--app-surface-soft); color:var(--app-accent-strong); font-size:12px; font-weight:800; white-space:nowrap; }
    .blast-btn .icon { width:42px; height:42px; display:grid; place-items:center; border-radius:8px; background:var(--app-surface-soft); color:var(--app-accent-strong); font-size:18px; flex:none; }
    .blast-btn .text { min-width:0; }
    .blast-btn strong { display:block; font-size:15px; color:var(--app-text); }
    .blast-btn small { display:block; margin-top:3px; color:var(--app-text-muted); line-height:1.35; }
    @media (max-width:640px){ .blast-menu-head{flex-direction:column;} }
</style>
@endsection

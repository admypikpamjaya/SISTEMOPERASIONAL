@extends('layouts.app')

@section('title', __('app.blast.manage_email_accounts_title'))

@section('content')

<style>
.email-control-page {
    --email-primary: var(--app-accent, #2563eb);
    --email-primary-strong: var(--app-accent-strong, #1d4ed8);
    --email-bg: var(--app-bg, #f4f7fb);
    --email-surface: var(--app-surface, #ffffff);
    --email-soft: var(--app-surface-soft, #f8fbff);
    --email-border: var(--app-border, #dbe4f0);
    --email-text: var(--app-text, #0f172a);
    --email-muted: var(--app-text-muted, #64748b);
    --email-success: #16a34a;
    --email-warning: #d97706;
    --email-danger: #dc2626;
    min-height: 100vh;
    padding: 18px;
    background: var(--email-bg);
    color: var(--email-text);
}
.ec-header,
.ec-panel,
.ec-account {
    background: var(--email-surface);
    border: 1px solid var(--email-border);
    border-radius: 8px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}
.ec-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 20px;
    margin-bottom: 14px;
}
.ec-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.ec-icon {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, .1);
    color: var(--email-primary);
    flex: 0 0 auto;
}
.ec-title {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
}
.ec-subtitle {
    margin-top: 3px;
    color: var(--email-muted);
    font-size: 12.5px;
}
.ec-actions,
.ec-row-actions,
.ec-inline-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ec-btn {
    border: 1px solid transparent;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    text-decoration: none;
    cursor: pointer;
    transition: .15s ease;
    white-space: nowrap;
}
.ec-btn.primary { background: var(--email-primary); color: #fff; }
.ec-btn.light { background: var(--email-soft); color: var(--email-text); border-color: var(--email-border); }
.ec-btn.success { background: var(--email-success); color: #fff; }
.ec-btn.danger { background: var(--email-danger); color: #fff; }
.ec-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(15, 23, 42, .08); }
.ec-alert {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
}
.ec-alert.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.ec-alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.ec-alert i { margin-top: 2px; }
.ec-guide {
    border: 1px solid rgba(37, 99, 235, .18);
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 14px;
    background: linear-gradient(180deg, #ffffff, #f8fbff);
    display: flex;
    justify-content: space-between;
    gap: 14px;
}
.ec-guide-title {
    font-weight: 800;
    color: var(--email-primary-strong);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.ec-guide-note {
    color: var(--email-muted);
    font-size: 12.5px;
    line-height: 1.5;
}
.ec-guide-preset {
    display: grid;
    grid-template-columns: repeat(3, minmax(110px, 1fr));
    gap: 8px;
    min-width: 380px;
}
.ec-preset-item {
    border: 1px solid var(--email-border);
    border-radius: 8px;
    background: var(--email-soft);
    padding: 8px 10px;
}
.ec-preset-label {
    color: var(--email-muted);
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.ec-preset-value {
    margin-top: 2px;
    color: var(--email-text);
    font-size: 12px;
    font-weight: 800;
}
.ec-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}
.ec-stat {
    border: 1px solid var(--email-border);
    border-radius: 8px;
    background: var(--email-surface);
    padding: 13px 14px;
}
.ec-stat-label {
    color: var(--email-muted);
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
}
.ec-stat-value {
    margin-top: 5px;
    font-size: 19px;
    font-weight: 800;
    color: var(--email-text);
    overflow-wrap: anywhere;
}
.ec-main {
    display: grid;
    grid-template-columns: minmax(310px, 420px) minmax(0, 1fr);
    gap: 14px;
    align-items: start;
}
.ec-panel {
    padding: 16px;
}
.ec-panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
}
.ec-panel-title {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ec-panel-note {
    margin-top: 3px;
    color: var(--email-muted);
    font-size: 12px;
}
.ec-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 11px;
}
.ec-form-group.full { grid-column: 1 / -1; }
.ec-label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 11.5px;
    font-weight: 800;
}
.ec-input,
.ec-select {
    width: 100%;
    border: 1px solid var(--email-border);
    border-radius: 8px;
    background: var(--email-soft);
    color: var(--email-text);
    padding: 9px 10px;
    font-size: 13px;
    outline: none;
}
.ec-input:focus,
.ec-select:focus {
    border-color: var(--email-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    background: #fff;
}
.ec-help {
    display: block;
    margin-top: 5px;
    color: var(--email-muted);
    font-size: 11px;
    line-height: 1.4;
}
.ec-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    font-size: 12.5px;
    font-weight: 700;
}
.ec-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.ec-account {
    padding: 14px;
}
.ec-account.active {
    border-color: rgba(37, 99, 235, .55);
    box-shadow: 0 12px 28px rgba(37, 99, 235, .1);
}
.ec-account-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.ec-account-main {
    display: flex;
    gap: 11px;
    min-width: 0;
}
.ec-avatar {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, .1);
    color: var(--email-primary);
    font-weight: 900;
    flex: 0 0 auto;
}
.ec-account-name {
    font-size: 14px;
    font-weight: 800;
    overflow-wrap: anywhere;
}
.ec-account-email {
    margin-top: 2px;
    color: var(--email-muted);
    font-size: 12px;
    overflow-wrap: anywhere;
}
.ec-badges {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    flex-wrap: wrap;
}
.ec-badge {
    border-radius: 999px;
    padding: 4px 8px;
    font-size: 10.5px;
    font-weight: 800;
}
.ec-badge.primary { background: rgba(37, 99, 235, .1); color: var(--email-primary-strong); }
.ec-badge.success { background: #dcfce7; color: #15803d; }
.ec-badge.warning { background: #fef3c7; color: #b45309; }
.ec-badge.danger { background: #fee2e2; color: #b91c1c; }
.ec-badge.muted { background: #f1f5f9; color: #64748b; }
.ec-meta {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 12px;
}
.ec-meta-item {
    border: 1px solid var(--email-border);
    border-radius: 8px;
    background: var(--email-soft);
    padding: 8px 9px;
    min-width: 0;
}
.ec-meta-label {
    color: var(--email-muted);
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.ec-meta-value {
    margin-top: 3px;
    color: var(--email-text);
    font-size: 12px;
    font-weight: 800;
    overflow-wrap: anywhere;
}
.ec-progress {
    height: 6px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    margin-top: 7px;
}
.ec-progress-bar {
    height: 100%;
    width: var(--usage, 0%);
    background: var(--email-primary);
}
.ec-test-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 8px;
    margin-bottom: 10px;
}
.ec-details {
    border-top: 1px solid var(--email-border);
    padding-top: 10px;
}
.ec-details summary {
    cursor: pointer;
    color: var(--email-primary-strong);
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 10px;
}
.ec-empty {
    border: 1px dashed var(--email-border);
    border-radius: 8px;
    background: var(--email-soft);
    padding: 28px;
    text-align: center;
}
.ec-empty-title {
    color: var(--email-text);
    font-size: 15px;
    font-weight: 800;
}
.ec-empty-body {
    margin-top: 6px;
    color: var(--email-muted);
    font-size: 12.5px;
}
@media (max-width: 1180px) {
    .ec-main,
    .ec-guide {
        grid-template-columns: 1fr;
    }
    .ec-guide {
        display: block;
    }
    .ec-guide-preset {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        min-width: 0;
        margin-top: 12px;
    }
    .ec-meta,
    .ec-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 720px) {
    .email-control-page { padding: 12px; }
    .ec-header,
    .ec-account-top {
        flex-direction: column;
        align-items: stretch;
    }
    .ec-actions,
    .ec-badges {
        justify-content: flex-start;
    }
    .ec-guide-preset,
    .ec-form-grid,
    .ec-meta,
    .ec-stats,
    .ec-test-form {
        grid-template-columns: 1fr;
    }
}
</style>

@php
    $stats = $emailAccountStats ?? [];
    $gmailDefaults = $gmailDefaults ?? [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'smtp_timeout' => 30,
    ];
    $oldProvider = old('provider', config('blast.email_accounts.default_provider', 'gmail'));
@endphp

<div class="email-control-page">
    <header class="ec-header">
        <div class="ec-title-wrap">
            <div class="ec-icon"><i class="fas fa-at"></i></div>
            <div>
                <h1 class="ec-title">{{ __('app.blast.manage_email_accounts_title') }}</h1>
                <div class="ec-subtitle">{{ __('app.blast.manage_email_accounts_subtitle') }}</div>
            </div>
        </div>
        <div class="ec-actions">
            <a href="{{ route('admin.blast.email') }}" class="ec-btn light">
                <i class="fas fa-arrow-left"></i> {{ __('app.blast.back_to_email') }}
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="ec-alert success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="ec-alert error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="ec-guide">
        <div>
            <div class="ec-guide-title">
                <i class="fab fa-google"></i> {{ __('app.blast.gmail_control_title') }}
            </div>
            <div class="ec-guide-note">{{ __('app.blast.gmail_control_note') }}</div>
        </div>
        <div class="ec-guide-preset">
            <div class="ec-preset-item">
                <div class="ec-preset-label">{{ __('app.blast.smtp_host') }}</div>
                <div class="ec-preset-value">{{ $gmailDefaults['host'] }}</div>
            </div>
            <div class="ec-preset-item">
                <div class="ec-preset-label">{{ __('app.blast.smtp_port') }}</div>
                <div class="ec-preset-value">{{ $gmailDefaults['port'] }}</div>
            </div>
            <div class="ec-preset-item">
                <div class="ec-preset-label">{{ __('app.blast.smtp_encryption') }}</div>
                <div class="ec-preset-value">{{ strtoupper((string) $gmailDefaults['encryption']) }}</div>
            </div>
        </div>
    </section>

    <section class="ec-stats">
        <div class="ec-stat">
            <div class="ec-stat-label">{{ __('app.blast.active_sender') }}</div>
            <div class="ec-stat-value">{{ $activeEmailAccount?->senderLabel() ?? __('app.blast.email_sender_default_config') }}</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-label">{{ __('app.blast.healthy_accounts') }}</div>
            <div class="ec-stat-value"><span data-email-stat="ready">{{ $stats['ready'] ?? 0 }}</span> / <span data-email-stat="enabled">{{ $stats['enabled'] ?? 0 }}</span></div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-label">{{ __('app.blast.sent_today') }}</div>
            <div class="ec-stat-value" data-email-stat="sent_today">{{ number_format((int) ($stats['sent_today'] ?? 0), 0, ',', '.') }}</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-label">{{ __('app.blast.failed_today') }}</div>
            <div class="ec-stat-value" data-email-stat="failed_today">{{ number_format((int) ($stats['failed_today'] ?? 0), 0, ',', '.') }}</div>
        </div>
    </section>

    <div class="ec-main">
        <section class="ec-panel">
            <div class="ec-panel-head">
                <div>
                    <h2 class="ec-panel-title"><i class="fas fa-plus-circle"></i> {{ __('app.blast.add_email_account') }}</h2>
                    <div class="ec-panel-note">{{ __('app.blast.email_provider_help') }}</div>
                </div>
                <button type="button" class="ec-btn light" id="gmailPresetBtn">
                    <i class="fab fa-google"></i> {{ __('app.blast.use_gmail_preset') }}
                </button>
            </div>

            <form method="POST" action="{{ route('admin.blast.email.accounts.store') }}" id="emailAccountCreateForm">
                @csrf
                <div class="ec-form-grid">
                    <div class="ec-form-group full">
                        <label class="ec-label">{{ __('app.blast.email_provider') }}</label>
                        <select name="provider" class="ec-select" data-provider-select>
                            <option value="gmail" @selected($oldProvider === 'gmail')>{{ __('app.blast.email_provider_gmail') }}</option>
                            <option value="custom" @selected($oldProvider === 'custom')>{{ __('app.blast.email_provider_custom') }}</option>
                        </select>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.email_account_label') }}</label>
                        <input type="text" name="label" class="ec-input" value="{{ old('label') }}" placeholder="{{ __('app.blast.email_account_label_placeholder') }}" required>
                        <small class="ec-help">{{ __('app.blast.email_account_label_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.sender_email') }}</label>
                        <input type="email" name="email_address" class="ec-input" value="{{ old('email_address') }}" placeholder="nama@gmail.com" required data-email-address-input>
                        <small class="ec-help">{{ __('app.blast.sender_email_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.from_name') }}</label>
                        <input type="text" name="from_name" class="ec-input" value="{{ old('from_name') }}" placeholder="{{ config('app.name') }}">
                        <small class="ec-help">{{ __('app.blast.from_name_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.reply_to_address') }}</label>
                        <input type="email" name="reply_to_address" class="ec-input" value="{{ old('reply_to_address') }}" placeholder="balasan@gmail.com">
                        <small class="ec-help">{{ __('app.blast.reply_to_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.smtp_host') }}</label>
                        <input type="text" name="host" class="ec-input" value="{{ old('host', $gmailDefaults['host']) }}" required data-host-input>
                        <small class="ec-help">{{ __('app.blast.smtp_host_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.smtp_port') }}</label>
                        <input type="number" name="port" class="ec-input" value="{{ old('port', $gmailDefaults['port']) }}" min="1" max="65535" required data-port-input>
                        <small class="ec-help">{{ __('app.blast.smtp_port_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.smtp_encryption') }}</label>
                        <select name="encryption" class="ec-select" data-encryption-select>
                            <option value="tls" @selected(old('encryption', $gmailDefaults['encryption']) === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('encryption') === 'ssl')>SSL</option>
                            <option value="none" @selected(old('encryption') === 'none')>{{ __('app.blast.none') }}</option>
                        </select>
                        <small class="ec-help">{{ __('app.blast.smtp_encryption_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.smtp_timeout') }}</label>
                        <input type="number" name="smtp_timeout" class="ec-input" value="{{ old('smtp_timeout', $gmailDefaults['smtp_timeout']) }}" min="5" max="120">
                        <small class="ec-help">{{ __('app.blast.smtp_timeout_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.smtp_username') }}</label>
                        <input type="text" name="username" class="ec-input" value="{{ old('username') }}" placeholder="nama@gmail.com" data-username-input>
                        <small class="ec-help">{{ __('app.blast.smtp_username_help') }}</small>
                    </div>
                    <div class="ec-form-group">
                        <label class="ec-label">{{ __('app.blast.daily_limit') }}</label>
                        <input type="number" name="daily_limit" class="ec-input" value="{{ old('daily_limit') }}" min="1" max="100000" placeholder="Opsional">
                        <small class="ec-help">{{ __('app.blast.daily_limit_help') }}</small>
                    </div>
                    <div class="ec-form-group full">
                        <label class="ec-label">{{ __('app.blast.smtp_password') }}</label>
                        <input type="password" name="password" class="ec-input" autocomplete="new-password" required>
                        <small class="ec-help">{{ __('app.blast.smtp_password_help') }}</small>
                    </div>
                    <label class="ec-checkbox">
                        <input type="checkbox" name="is_enabled" value="1" checked>
                        {{ __('app.blast.email_account_enabled') }}
                    </label>
                    <label class="ec-checkbox">
                        <input type="checkbox" name="make_active" value="1" @checked(!$activeEmailAccount)>
                        {{ __('app.blast.make_active_sender') }}
                    </label>
                </div>
                <div style="margin-top:14px;">
                    <button type="submit" class="ec-btn primary">
                        <i class="fas fa-save"></i> {{ __('app.blast.save_account') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="ec-panel">
            <div class="ec-panel-head">
                <div>
                    <h2 class="ec-panel-title"><i class="fas fa-envelope"></i> {{ __('app.blast.email_account_list') }}</h2>
                    <div class="ec-panel-note">{{ __('app.blast.saved_email_accounts') }}: {{ $emailAccounts->count() }}</div>
                </div>
            </div>

            <div class="ec-list">
                @forelse($emailAccounts as $account)
                    @php
                        $healthTone = $account->healthTone();
                        $providerInitial = $account->isGmail() ? 'G' : 'S';
                    @endphp
                    <article class="ec-account {{ $account->is_active ? 'active' : '' }}" data-email-account-id="{{ $account->id }}">
                        <div class="ec-account-top">
                            <div class="ec-account-main">
                                <div class="ec-avatar">{{ $providerInitial }}</div>
                                <div>
                                    <div class="ec-account-name">{{ $account->label }}</div>
                                    <div class="ec-account-email">{{ $account->email_address }}</div>
                                </div>
                            </div>
                            <div class="ec-badges">
                                <span class="ec-badge primary">{{ $account->providerLabel() }}</span>
                                <span class="ec-badge {{ $account->is_enabled ? 'success' : 'muted' }}">
                                    {{ $account->is_enabled ? __('app.blast.email_account_status_enabled') : __('app.blast.email_account_status_disabled') }}
                                </span>
                                @if($account->is_active)
                                    <span class="ec-badge primary">{{ __('app.blast.email_account_current_sender') }}</span>
                                @endif
                                <span class="ec-badge {{ $healthTone }}" data-email-account-health>{{ $account->healthLabel() }}</span>
                            </div>
                        </div>

                        <div class="ec-meta">
                            <div class="ec-meta-item">
                                <div class="ec-meta-label">{{ __('app.blast.smtp_host') }}</div>
                                <div class="ec-meta-value">{{ $account->host }}:{{ $account->port }}</div>
                            </div>
                            <div class="ec-meta-item">
                                <div class="ec-meta-label">{{ __('app.blast.daily_usage') }}</div>
                                <div class="ec-meta-value" data-email-account-usage>{{ $account->usageLabel() }}</div>
                                <div class="ec-progress"><div class="ec-progress-bar" style="--usage: {{ $account->usagePercent() }}%;"></div></div>
                            </div>
                            <div class="ec-meta-item">
                                <div class="ec-meta-label">{{ __('app.blast.last_test') }}</div>
                                <div class="ec-meta-value" data-email-account-tested>{{ $account->last_tested_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? __('app.blast.never_tested') }}</div>
                            </div>
                            <div class="ec-meta-item">
                                <div class="ec-meta-label">{{ __('app.blast.last_delivery') }}</div>
                                <div class="ec-meta-value" data-email-account-used>{{ $account->last_used_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>

                        @if($account->last_send_status === 'failed' && $account->last_send_message)
                            <div class="ec-alert error" style="margin-bottom:10px;">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>{{ \Illuminate\Support\Str::limit($account->last_send_message, 180) }}</span>
                            </div>
                        @endif

                        <div class="ec-row-actions">
                            <form
                                method="POST"
                                action="{{ route('admin.blast.email.accounts.enabled', $account) }}"
                                data-confirm-message="{{ $account->is_enabled ? __('app.blast.email_account_disable_confirm', ['account' => $account->senderLabel()]) : __('app.blast.email_account_enable_confirm', ['account' => $account->senderLabel()]) }}"
                                onsubmit="return confirm(this.dataset.confirmMessage)"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_enabled" value="{{ $account->is_enabled ? 0 : 1 }}">
                                <button type="submit" class="ec-btn {{ $account->is_enabled ? 'light' : 'success' }}">
                                    <i class="fas {{ $account->is_enabled ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                    {{ $account->is_enabled ? __('app.blast.disable_account') : __('app.blast.enable_account') }}
                                </button>
                            </form>
                            @if(!$account->is_active && $account->is_enabled)
                                <form method="POST" action="{{ route('admin.blast.email.accounts.activate', $account) }}">
                                    @csrf
                                    <button type="submit" class="ec-btn success">
                                        <i class="fas fa-check"></i> {{ __('app.blast.activate') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.blast.email.accounts.destroy', $account) }}" data-confirm-message="{{ __('app.blast.email_account_delete_confirm', ['account' => $account->senderLabel()]) }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ec-btn danger">
                                    <i class="fas fa-trash"></i> {{ __('app.blast.delete') }}
                                </button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.blast.email.accounts.test', $account) }}" class="ec-test-form">
                            @csrf
                            <input type="email" name="test_email" class="ec-input" placeholder="{{ __('app.blast.test_email_placeholder') }}" required @disabled(!$account->is_enabled)>
                            <button type="submit" class="ec-btn light" @disabled(!$account->is_enabled)>
                                <i class="fas fa-paper-plane"></i> {{ __('app.blast.test_send') }}
                            </button>
                        </form>

                        <details class="ec-details">
                            <summary>{{ __('app.blast.edit_email_account') }}</summary>
                            <form method="POST" action="{{ route('admin.blast.email.accounts.update', $account) }}">
                                @csrf
                                @method('PUT')
                                <div class="ec-form-grid">
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.email_provider') }}</label>
                                        <select name="provider" class="ec-select">
                                            <option value="gmail" @selected(old('provider', $account->providerKey()) === 'gmail')>{{ __('app.blast.email_provider_gmail') }}</option>
                                            <option value="custom" @selected(old('provider', $account->providerKey()) === 'custom')>{{ __('app.blast.email_provider_custom') }}</option>
                                        </select>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.email_account_label') }}</label>
                                        <input type="text" name="label" class="ec-input" value="{{ old('label', $account->label) }}" required>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.sender_email') }}</label>
                                        <input type="email" name="email_address" class="ec-input" value="{{ old('email_address', $account->email_address) }}" required>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.from_name') }}</label>
                                        <input type="text" name="from_name" class="ec-input" value="{{ old('from_name', $account->from_name) }}">
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.reply_to_address') }}</label>
                                        <input type="email" name="reply_to_address" class="ec-input" value="{{ old('reply_to_address', $account->reply_to_address) }}">
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.smtp_host') }}</label>
                                        <input type="text" name="host" class="ec-input" value="{{ old('host', $account->host) }}" required>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.smtp_port') }}</label>
                                        <input type="number" name="port" class="ec-input" value="{{ old('port', $account->port) }}" min="1" max="65535" required>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.smtp_encryption') }}</label>
                                        <select name="encryption" class="ec-select">
                                            <option value="tls" @selected(old('encryption', $account->encryption ?: 'tls') === 'tls')>TLS</option>
                                            <option value="ssl" @selected(old('encryption', $account->encryption) === 'ssl')>SSL</option>
                                            <option value="none" @selected(old('encryption', $account->encryption ?: 'none') === 'none')>{{ __('app.blast.none') }}</option>
                                        </select>
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.smtp_timeout') }}</label>
                                        <input type="number" name="smtp_timeout" class="ec-input" value="{{ old('smtp_timeout', $account->smtp_timeout ?: 30) }}" min="5" max="120">
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.smtp_username') }}</label>
                                        <input type="text" name="username" class="ec-input" value="{{ old('username', $account->username) }}">
                                    </div>
                                    <div class="ec-form-group">
                                        <label class="ec-label">{{ __('app.blast.daily_limit') }}</label>
                                        <input type="number" name="daily_limit" class="ec-input" value="{{ old('daily_limit', $account->daily_limit) }}" min="1" max="100000">
                                    </div>
                                    <div class="ec-form-group full">
                                        <label class="ec-label">{{ __('app.blast.smtp_password') }}</label>
                                        <input type="password" name="password" class="ec-input" autocomplete="new-password" placeholder="{{ __('app.blast.smtp_password_keep') }}">
                                    </div>
                                    <label class="ec-checkbox">
                                        <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $account->is_enabled))>
                                        {{ __('app.blast.email_account_enabled') }}
                                    </label>
                                </div>
                                <div style="margin-top:14px;">
                                    <button type="submit" class="ec-btn primary">
                                        <i class="fas fa-save"></i> {{ __('app.blast.update_account') }}
                                    </button>
                                </div>
                            </form>
                        </details>
                    </article>
                @empty
                    <div class="ec-empty">
                        <div class="ec-empty-title">{{ __('app.blast.email_account_empty_title') }}</div>
                        <div class="ec-empty-body">{{ __('app.blast.email_account_empty_body') }}</div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const gmailDefaults = @json($gmailDefaults);
    const statusUrl = @json(route('admin.blast.email.accounts.status'));
    const createForm = document.getElementById('emailAccountCreateForm');
    const gmailPresetBtn = document.getElementById('gmailPresetBtn');

    function applyGmailPreset(form) {
        if (!form) return;
        const provider = form.querySelector('[name="provider"]');
        const host = form.querySelector('[name="host"]');
        const port = form.querySelector('[name="port"]');
        const encryption = form.querySelector('[name="encryption"]');
        const timeout = form.querySelector('[name="smtp_timeout"]');
        const email = form.querySelector('[name="email_address"]');
        const username = form.querySelector('[name="username"]');

        if (provider) provider.value = 'gmail';
        if (host) host.value = gmailDefaults.host || 'smtp.gmail.com';
        if (port) port.value = gmailDefaults.port || 587;
        if (encryption) encryption.value = gmailDefaults.encryption || 'tls';
        if (timeout) timeout.value = gmailDefaults.smtp_timeout || 30;
        if (username && email && !username.value.trim()) username.value = email.value.trim();
    }

    if (gmailPresetBtn) {
        gmailPresetBtn.addEventListener('click', function () {
            applyGmailPreset(createForm);
        });
    }

    if (createForm) {
        const emailInput = createForm.querySelector('[name="email_address"]');
        const usernameInput = createForm.querySelector('[name="username"]');
        const providerSelect = createForm.querySelector('[name="provider"]');

        if (providerSelect) {
            providerSelect.addEventListener('change', function () {
                if (this.value === 'gmail') applyGmailPreset(createForm);
            });
        }

        if (emailInput && usernameInput) {
            emailInput.addEventListener('blur', function () {
                if (!usernameInput.value.trim() && providerSelect?.value === 'gmail') {
                    usernameInput.value = emailInput.value.trim();
                }
            });
        }
    }

    async function refreshAccountStatus() {
        if (!statusUrl) return;
        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (!payload?.success) return;

            Object.entries(payload.stats || {}).forEach(([key, value]) => {
                document.querySelectorAll(`[data-email-stat="${key}"]`).forEach((el) => {
                    el.textContent = Number(value || 0).toLocaleString('id-ID');
                });
            });

            (payload.accounts || []).forEach((account) => {
                const card = document.querySelector(`[data-email-account-id="${account.id}"]`);
                if (!card) return;
                const health = card.querySelector('[data-email-account-health]');
                const usage = card.querySelector('[data-email-account-usage]');
                const tested = card.querySelector('[data-email-account-tested]');
                const used = card.querySelector('[data-email-account-used]');
                const progress = card.querySelector('.ec-progress-bar');

                if (health) {
                    health.className = `ec-badge ${account.healthTone || 'muted'}`;
                    health.textContent = account.healthLabel || '-';
                }
                if (usage) usage.textContent = account.usageLabel || '-';
                if (tested) tested.textContent = account.lastTestedAt || @json(__('app.blast.never_tested'));
                if (used) used.textContent = account.lastUsedAt || '-';
                if (progress) progress.style.setProperty('--usage', `${Number(account.usagePercent || 0)}%`);
            });
        } catch (error) {
            // Status refresh is only informational.
        }
    }

    refreshAccountStatus();
    setInterval(function () {
        if (document.visibilityState !== 'hidden') refreshAccountStatus();
    }, 15000);
});
</script>
@endsection

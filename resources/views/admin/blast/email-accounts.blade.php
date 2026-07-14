@extends('layouts.app')

@section('title', __('app.blast.manage_email_accounts_title'))

@section('content')

<style>
.email-control-page {
    min-height: 100vh;
    padding: 18px;
    background: var(--app-bg, #eef3fb);
    color: var(--app-text, #0f172a);
}
.email-control-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    margin-bottom: 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--app-sidebar-bg, #1f2937), var(--app-accent, #2563eb));
    box-shadow: var(--app-shadow, 0 18px 38px rgba(15, 23, 42, .08));
}
.email-control-title-wrap { display: flex; align-items: center; gap: 14px; min-width: 0; }
.email-control-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: rgba(255, 255, 255, .14);
    border: 1px solid rgba(255, 255, 255, .18);
}
.email-control-title { color: #fff; font-size: 21px; font-weight: 800; line-height: 1.15; }
.email-control-subtitle { color: rgba(255,255,255,.72); font-size: 12.5px; margin-top: 3px; }
.email-control-back,
.email-control-btn {
    border: 1px solid transparent;
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    text-decoration: none;
    transition: .16s ease;
}
.email-control-back { background: #fff; color: var(--app-sidebar-bg, #1f2937); }
.email-control-btn.primary { background: var(--app-accent, #2563eb); color: #fff; }
.email-control-btn.light { background: var(--app-surface-soft, #f7faff); color: var(--app-text, #0f172a); border-color: var(--app-border, #d7e0ee); }
.email-control-btn.success { background: #16a34a; color: #fff; }
.email-control-btn.danger { background: #dc2626; color: #fff; }
.email-control-alert {
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    gap: 8px;
    align-items: center;
}
.email-control-alert.success { color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; }
.email-control-alert.error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
.email-control-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}
.email-control-stat,
.email-control-panel,
.email-account-card {
    border: 1px solid var(--app-border, #d7e0ee);
    background: var(--app-surface, #fff);
    box-shadow: var(--app-shadow, 0 18px 38px rgba(15, 23, 42, .08));
    border-radius: 12px;
}
.email-control-stat { padding: 14px 16px; }
.email-control-stat-label { color: var(--app-text-muted, #64748b); font-size: 11px; font-weight: 800; text-transform: uppercase; }
.email-control-stat-value { color: var(--app-text, #0f172a); font-size: 18px; font-weight: 800; margin-top: 4px; overflow-wrap: anywhere; }
.email-control-grid { display: grid; grid-template-columns: minmax(280px, .86fr) minmax(0, 1.14fr); gap: 14px; align-items: start; }
.email-control-panel { padding: 18px; }
.email-control-panel-title { font-size: 15px; font-weight: 800; color: var(--app-text, #0f172a); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.email-guide-panel {
    border: 1px solid var(--app-border, #d7e0ee);
    background: var(--app-surface, #fff);
    box-shadow: var(--app-shadow, 0 18px 38px rgba(15, 23, 42, .08));
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 14px;
}
.email-guide-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 12px;
}
.email-guide-title { font-size: 15px; font-weight: 800; color: var(--app-text, #0f172a); display: flex; align-items: center; gap: 8px; }
.email-guide-note { font-size: 12px; color: var(--app-text-muted, #64748b); margin-top: 4px; }
.email-guide-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.email-guide-item {
    border: 1px solid var(--app-border, #d7e0ee);
    background: var(--app-surface-soft, #f7faff);
    border-radius: 10px;
    padding: 10px 12px;
}
.email-guide-label { color: var(--app-text-muted, #64748b); font-size: 10.5px; font-weight: 800; text-transform: uppercase; }
.email-guide-value { color: var(--app-text, #0f172a); font-size: 12.5px; font-weight: 800; margin-top: 3px; overflow-wrap: anywhere; }
.email-guide-desc { color: var(--app-text-soft, #334155); font-size: 11.5px; line-height: 1.45; margin-top: 5px; }
.email-form-help { display: block; color: var(--app-text-muted, #64748b); font-size: 11px; line-height: 1.4; margin-top: 5px; }
.email-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.email-form-group.full { grid-column: 1 / -1; }
.email-form-label { display: block; font-size: 11.5px; font-weight: 800; color: var(--app-text-soft, #334155); margin-bottom: 6px; }
.email-form-input,
.email-form-select {
    width: 100%;
    border: 1px solid var(--app-border, #d7e0ee);
    border-radius: 8px;
    background: var(--app-surface-soft, #f7faff);
    color: var(--app-text, #0f172a);
    padding: 9px 10px;
    font-size: 13px;
    outline: none;
}
.email-form-input:focus,
.email-form-select:focus { border-color: var(--app-accent, #2563eb); box-shadow: 0 0 0 3px var(--app-row-selected, rgba(37, 99, 235, .1)); }
.email-checkbox-row { display: flex; align-items: center; gap: 8px; color: var(--app-text-soft, #334155); font-size: 12.5px; font-weight: 700; }
.email-account-list { display: flex; flex-direction: column; gap: 10px; }
.email-account-card { padding: 14px; }
.email-account-card.is-active { border-color: var(--app-accent, #2563eb); }
.email-account-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.email-account-name { font-size: 14px; font-weight: 800; color: var(--app-text, #0f172a); overflow-wrap: anywhere; }
.email-account-address { color: var(--app-text-muted, #64748b); font-size: 12px; margin-top: 2px; overflow-wrap: anywhere; }
.email-badges { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
.email-badge { border-radius: 999px; padding: 4px 8px; font-size: 10.5px; font-weight: 800; }
.email-badge.active { background: rgba(34, 197, 94, .12); color: #16a34a; }
.email-badge.inactive { background: rgba(100, 116, 139, .12); color: var(--app-text-muted, #64748b); }
.email-badge.failed { background: rgba(239, 68, 68, .12); color: #dc2626; }
.email-account-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 12px; }
.email-account-meta-item { border: 1px solid var(--app-border, #d7e0ee); background: var(--app-surface-soft, #f7faff); border-radius: 8px; padding: 8px 10px; }
.email-account-meta-label { color: var(--app-text-muted, #64748b); font-size: 10.5px; font-weight: 800; text-transform: uppercase; }
.email-account-meta-value { color: var(--app-text, #0f172a); font-size: 12.5px; font-weight: 700; margin-top: 2px; overflow-wrap: anywhere; }
.email-account-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 10px; }
.email-test-form { display: flex; gap: 8px; margin-bottom: 10px; }
.email-test-form .email-form-input { flex: 1; }
.email-account-details { border-top: 1px solid var(--app-border, #d7e0ee); padding-top: 10px; }
.email-account-details summary { cursor: pointer; color: var(--app-accent-strong, #1d4ed8); font-size: 12px; font-weight: 800; margin-bottom: 10px; }
.email-empty { border: 1px dashed var(--app-border, #d7e0ee); border-radius: 10px; padding: 24px; text-align: center; color: var(--app-text-muted, #64748b); font-size: 13px; }
@media (max-width: 1100px) {
    .email-control-grid,
    .email-guide-grid,
    .email-control-stats { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
    .email-control-page { padding: 12px; }
    .email-control-header { align-items: flex-start; flex-direction: column; }
    .email-form-grid,
    .email-account-meta { grid-template-columns: 1fr; }
    .email-test-form { flex-direction: column; }
}
</style>

<div class="email-control-page">
    <div class="email-control-header">
        <div class="email-control-title-wrap">
            <div class="email-control-icon"><i class="fas fa-at"></i></div>
            <div>
                <div class="email-control-title">{{ __('app.blast.manage_email_accounts_title') }}</div>
                <div class="email-control-subtitle">{{ __('app.blast.manage_email_accounts_subtitle') }}</div>
            </div>
        </div>
        <a href="{{ route('admin.blast.email') }}" class="email-control-back">
            <i class="fas fa-arrow-left"></i> {{ __('app.blast.back_to_email') }}
        </a>
    </div>

    @if(session('success'))
        <div class="email-control-alert success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="email-control-alert error">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="email-control-stats">
        <div class="email-control-stat">
            <div class="email-control-stat-label">{{ __('app.blast.active_sender') }}</div>
            <div class="email-control-stat-value">{{ $activeEmailAccount?->senderLabel() ?? __('app.blast.email_sender_default_config') }}</div>
        </div>
        <div class="email-control-stat">
            <div class="email-control-stat-label">{{ __('app.blast.saved_email_accounts') }}</div>
            <div class="email-control-stat-value">{{ $emailAccounts->count() }}</div>
        </div>
        <div class="email-control-stat">
            <div class="email-control-stat-label">{{ __('app.blast.fallback_mail_config') }}</div>
            <div class="email-control-stat-value">{{ ($mailConfigSummary['from_address'] ?? '-') . ' / ' . ($mailConfigSummary['host'] ?? '-') }}</div>
        </div>
    </div>

    <section class="email-guide-panel">
        <div class="email-guide-head">
            <div>
                <div class="email-guide-title">
                    <i class="fas fa-circle-info"></i> {{ __('app.blast.email_account_fill_guide_title') }}
                </div>
                <div class="email-guide-note">{{ __('app.blast.email_account_fill_guide_note') }}</div>
            </div>
        </div>
        <div class="email-guide-grid">
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.smtp_host') }}</div>
                <div class="email-guide-value">smtp.gmail.com</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_host_help') }}</div>
            </div>
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.smtp_port') }} / {{ __('app.blast.smtp_encryption') }}</div>
                <div class="email-guide-value">587 / TLS</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_port_help') }}</div>
            </div>
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.smtp_username') }}</div>
                <div class="email-guide-value">nama@gmail.com</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_username_help') }}</div>
            </div>
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.smtp_password') }}</div>
                <div class="email-guide-value">{{ __('app.blast.google_app_password') }}</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_password_help') }}</div>
            </div>
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.sender_email') }}</div>
                <div class="email-guide-value">nama@gmail.com</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_sender_help') }}</div>
            </div>
            <div class="email-guide-item">
                <div class="email-guide-label">{{ __('app.blast.email_account_label') }}</div>
                <div class="email-guide-value">{{ __('app.blast.email_account_label_example') }}</div>
                <div class="email-guide-desc">{{ __('app.blast.email_account_fill_label_help') }}</div>
            </div>
        </div>
    </section>

    <div class="email-control-grid">
        <section class="email-control-panel">
            <div class="email-control-panel-title">
                <i class="fas fa-plus-circle"></i> {{ __('app.blast.add_email_account') }}
            </div>
            <form method="POST" action="{{ route('admin.blast.email.accounts.store') }}">
                @csrf
                <div class="email-form-grid">
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.email_account_label') }}</label>
                        <input type="text" name="label" class="email-form-input" value="{{ old('label') }}" placeholder="{{ __('app.blast.email_account_label_placeholder') }}" required>
                        <small class="email-form-help">{{ __('app.blast.email_account_label_help') }}</small>
                    </div>
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.sender_email') }}</label>
                        <input type="email" name="email_address" class="email-form-input" value="{{ old('email_address') }}" placeholder="noreply@example.com" required>
                        <small class="email-form-help">{{ __('app.blast.sender_email_help') }}</small>
                    </div>
                    <div class="email-form-group full">
                        <label class="email-form-label">{{ __('app.blast.from_name') }}</label>
                        <input type="text" name="from_name" class="email-form-input" value="{{ old('from_name') }}" placeholder="{{ config('app.name') }}">
                        <small class="email-form-help">{{ __('app.blast.from_name_help') }}</small>
                    </div>
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.smtp_host') }}</label>
                        <input type="text" name="host" class="email-form-input" value="{{ old('host') }}" placeholder="smtp.example.com" required>
                        <small class="email-form-help">{{ __('app.blast.smtp_host_help') }}</small>
                    </div>
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.smtp_port') }}</label>
                        <input type="number" name="port" class="email-form-input" value="{{ old('port', 587) }}" min="1" max="65535" required>
                        <small class="email-form-help">{{ __('app.blast.smtp_port_help') }}</small>
                    </div>
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.smtp_encryption') }}</label>
                        <select name="encryption" class="email-form-select">
                            <option value="tls" @selected(old('encryption', 'tls') === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('encryption') === 'ssl')>SSL</option>
                            <option value="none" @selected(old('encryption') === 'none')>{{ __('app.blast.none') }}</option>
                        </select>
                        <small class="email-form-help">{{ __('app.blast.smtp_encryption_help') }}</small>
                    </div>
                    <div class="email-form-group">
                        <label class="email-form-label">{{ __('app.blast.smtp_username') }}</label>
                        <input type="text" name="username" class="email-form-input" value="{{ old('username') }}" placeholder="user@example.com">
                        <small class="email-form-help">{{ __('app.blast.smtp_username_help') }}</small>
                    </div>
                    <div class="email-form-group full">
                        <label class="email-form-label">{{ __('app.blast.smtp_password') }}</label>
                        <input type="password" name="password" class="email-form-input" autocomplete="new-password" required>
                        <small class="email-form-help">{{ __('app.blast.smtp_password_help') }}</small>
                    </div>
                    <label class="email-checkbox-row">
                        <input type="checkbox" name="is_enabled" value="1" checked>
                        {{ __('app.blast.email_account_enabled') }}
                    </label>
                    <label class="email-checkbox-row">
                        <input type="checkbox" name="make_active" value="1" @checked(!$activeEmailAccount)>
                        {{ __('app.blast.make_active_sender') }}
                    </label>
                </div>
                <div style="margin-top:14px;">
                    <button type="submit" class="email-control-btn primary">
                        <i class="fas fa-save"></i> {{ __('app.blast.save_account') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="email-control-panel">
            <div class="email-control-panel-title">
                <i class="fas fa-envelope"></i> {{ __('app.blast.email_account_list') }}
            </div>
            <div class="email-account-list">
                @forelse($emailAccounts as $account)
                    <article class="email-account-card {{ $account->is_active ? 'is-active' : '' }}">
                        <div class="email-account-top">
                            <div>
                                <div class="email-account-name">{{ $account->label }}</div>
                                <div class="email-account-address">{{ $account->email_address }}</div>
                            </div>
                            <div class="email-badges">
                                <span class="email-badge {{ $account->is_active ? 'active' : 'inactive' }}">
                                    {{ $account->is_active ? __('app.blast.active') : __('app.blast.inactive') }}
                                </span>
                                <span class="email-badge {{ $account->is_enabled ? 'active' : 'inactive' }}">
                                    {{ $account->is_enabled ? __('app.blast.email_account_enabled_short') : __('app.blast.email_account_disabled_short') }}
                                </span>
                                @if($account->last_test_status)
                                    <span class="email-badge {{ $account->last_test_status === 'success' ? 'active' : 'failed' }}">
                                        {{ $account->last_test_status === 'success' ? __('app.blast.test_success') : __('app.blast.test_failed') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="email-account-meta">
                            <div class="email-account-meta-item">
                                <div class="email-account-meta-label">{{ __('app.blast.smtp_host') }}</div>
                                <div class="email-account-meta-value">{{ $account->host }}:{{ $account->port }}</div>
                            </div>
                            <div class="email-account-meta-item">
                                <div class="email-account-meta-label">{{ __('app.blast.smtp_encryption') }}</div>
                                <div class="email-account-meta-value">{{ strtoupper($account->encryption ?: __('app.blast.none')) }}</div>
                            </div>
                            <div class="email-account-meta-item">
                                <div class="email-account-meta-label">{{ __('app.blast.smtp_username') }}</div>
                                <div class="email-account-meta-value">{{ $account->username ?: $account->email_address }}</div>
                            </div>
                            <div class="email-account-meta-item">
                                <div class="email-account-meta-label">{{ __('app.blast.last_test') }}</div>
                                <div class="email-account-meta-value">{{ $account->last_tested_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? __('app.blast.never_tested') }}</div>
                            </div>
                        </div>

                        <div class="email-account-actions">
                            @if(!$account->is_active && $account->is_enabled)
                                <form method="POST" action="{{ route('admin.blast.email.accounts.activate', $account) }}">
                                    @csrf
                                    <button type="submit" class="email-control-btn success">
                                        <i class="fas fa-check"></i> {{ __('app.blast.activate') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.blast.email.accounts.destroy', $account) }}" data-confirm-message="{{ __('app.blast.email_account_delete_confirm', ['account' => $account->senderLabel()]) }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="email-control-btn danger">
                                    <i class="fas fa-trash"></i> {{ __('app.blast.delete') }}
                                </button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.blast.email.accounts.test', $account) }}" class="email-test-form">
                            @csrf
                            <input type="email" name="test_email" class="email-form-input" placeholder="{{ __('app.blast.test_email_placeholder') }}" required>
                            <button type="submit" class="email-control-btn light">
                                <i class="fas fa-paper-plane"></i> {{ __('app.blast.test_send') }}
                            </button>
                        </form>

                        <details class="email-account-details">
                            <summary>{{ __('app.blast.edit_email_account') }}</summary>
                            <form method="POST" action="{{ route('admin.blast.email.accounts.update', $account) }}">
                                @csrf
                                @method('PUT')
                                <div class="email-form-grid">
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.email_account_label') }}</label>
                                        <input type="text" name="label" class="email-form-input" value="{{ old('label', $account->label) }}" required>
                                    </div>
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.sender_email') }}</label>
                                        <input type="email" name="email_address" class="email-form-input" value="{{ old('email_address', $account->email_address) }}" required>
                                    </div>
                                    <div class="email-form-group full">
                                        <label class="email-form-label">{{ __('app.blast.from_name') }}</label>
                                        <input type="text" name="from_name" class="email-form-input" value="{{ old('from_name', $account->from_name) }}">
                                    </div>
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.smtp_host') }}</label>
                                        <input type="text" name="host" class="email-form-input" value="{{ old('host', $account->host) }}" required>
                                    </div>
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.smtp_port') }}</label>
                                        <input type="number" name="port" class="email-form-input" value="{{ old('port', $account->port) }}" min="1" max="65535" required>
                                    </div>
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.smtp_encryption') }}</label>
                                        <select name="encryption" class="email-form-select">
                                            <option value="tls" @selected(old('encryption', $account->encryption ?: 'tls') === 'tls')>TLS</option>
                                            <option value="ssl" @selected(old('encryption', $account->encryption) === 'ssl')>SSL</option>
                                            <option value="none" @selected(old('encryption', $account->encryption ?: 'none') === 'none')>{{ __('app.blast.none') }}</option>
                                        </select>
                                    </div>
                                    <div class="email-form-group">
                                        <label class="email-form-label">{{ __('app.blast.smtp_username') }}</label>
                                        <input type="text" name="username" class="email-form-input" value="{{ old('username', $account->username) }}">
                                    </div>
                                    <div class="email-form-group full">
                                        <label class="email-form-label">{{ __('app.blast.smtp_password') }}</label>
                                        <input type="password" name="password" class="email-form-input" autocomplete="new-password" placeholder="{{ __('app.blast.smtp_password_keep') }}">
                                    </div>
                                    <label class="email-checkbox-row">
                                        <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $account->is_enabled))>
                                        {{ __('app.blast.email_account_enabled') }}
                                    </label>
                                </div>
                                <div style="margin-top:14px;">
                                    <button type="submit" class="email-control-btn primary">
                                        <i class="fas fa-save"></i> {{ __('app.blast.update_account') }}
                                    </button>
                                </div>
                            </form>
                        </details>
                    </article>
                @empty
                    <div class="email-empty">{{ __('app.blast.no_email_accounts') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@endsection

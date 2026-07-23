@extends('layouts.app')

@section('content')
@include('shared.modal')

@php
    $currentUser = auth()->user();
    $shownUsers = $users->count();
    $totalUsers = $users->total();
    $visibleRoles = $users->getCollection()->pluck('role')->filter()->unique()->count();
@endphp

<style>
    .um-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        color: var(--app-text);
    }

    .um-hero,
    .um-panel {
        background: var(--app-surface);
        border: 1px solid var(--app-border);
        border-radius: 8px;
        box-shadow: var(--app-shadow);
    }

    .um-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem;
    }

    .um-title-wrap {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-width: 0;
    }

    .um-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%);
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, .18);
    }

    .um-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.15;
        color: var(--app-text);
        letter-spacing: 0;
    }

    .um-subtitle {
        margin: .25rem 0 0;
        color: var(--app-text-muted);
        font-size: .95rem;
        line-height: 1.4;
    }

    .um-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: .55rem;
    }

    .um-btn {
        min-height: 40px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        font-weight: 700;
        border: 1px solid transparent;
        padding: .5rem .85rem;
        white-space: nowrap;
    }

    .um-btn-primary {
        background: var(--app-accent-strong);
        color: #fff;
        box-shadow: 0 14px 26px rgba(37, 99, 235, .22);
    }

    .um-btn-primary:hover,
    .um-btn-primary:focus {
        color: #fff;
        filter: brightness(1.04);
    }

    .um-btn-secondary {
        background: var(--app-surface-soft);
        color: var(--app-text);
        border-color: var(--app-border);
    }

    .um-btn-secondary:hover,
    .um-btn-secondary:focus {
        color: var(--app-text);
        border-color: var(--app-accent);
    }

    .um-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }

    .um-stat {
        background: var(--app-surface);
        border: 1px solid var(--app-border);
        border-radius: 8px;
        padding: .95rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .um-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--app-surface-soft);
        color: var(--app-accent);
        flex: 0 0 auto;
    }

    .um-stat-label {
        margin: 0;
        color: var(--app-text-muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .um-stat-value {
        margin: .15rem 0 0;
        color: var(--app-text);
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .um-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .95rem 1rem;
        border-bottom: 1px solid var(--app-border);
    }

    .um-panel-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        min-width: 0;
        margin: 0;
        color: var(--app-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .um-panel-title i {
        color: var(--app-accent);
    }

    .um-panel-hint {
        margin: 0;
        color: var(--app-text-muted);
        font-size: .85rem;
    }

    .um-filter {
        padding: 1rem;
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto auto;
        gap: .75rem;
        align-items: end;
    }

    .um-field {
        display: flex;
        flex-direction: column;
        gap: .35rem;
        min-width: 0;
    }

    .um-label {
        color: var(--app-text-muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
        margin: 0;
    }

    .um-control,
    .um-modal-form .form-control {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid var(--app-border);
        background: var(--app-surface-soft);
        color: var(--app-text);
        box-shadow: none;
    }

    .um-control:focus,
    .um-modal-form .form-control:focus {
        border-color: var(--app-accent);
        box-shadow: 0 0 0 .15rem rgba(37, 99, 235, .12);
        background: var(--app-surface);
        color: var(--app-text);
    }

    .um-table-wrap {
        overflow-x: auto;
    }

    .um-table {
        width: 100%;
        margin: 0;
        color: var(--app-text);
    }

    .um-table thead th {
        border: 0;
        border-bottom: 1px solid var(--app-border);
        color: var(--app-text-muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
        padding: .85rem 1rem;
        white-space: nowrap;
    }

    .um-table tbody td,
    .um-table tbody th {
        border-top: 1px solid var(--app-border);
        padding: .85rem 1rem;
        vertical-align: middle;
    }

    .um-table tbody tr:hover {
        background: var(--app-row-hover);
    }

    .um-user {
        display: flex;
        align-items: center;
        gap: .7rem;
        min-width: 230px;
    }

    .um-avatar {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, .12);
        color: var(--app-accent);
        font-weight: 800;
        flex: 0 0 auto;
    }

    .um-user-name {
        display: block;
        color: var(--app-text);
        font-weight: 800;
        line-height: 1.15;
    }

    .um-user-email {
        display: block;
        margin-top: .2rem;
        color: var(--app-text-muted);
        font-size: .86rem;
        line-height: 1.2;
        word-break: break-word;
    }

    .um-role {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .35rem .65rem;
        font-size: .82rem;
        font-weight: 800;
        background: var(--app-surface-soft);
        color: var(--app-text);
        border: 1px solid var(--app-border);
        white-space: nowrap;
    }

    .um-row-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-width: 180px;
    }

    .um-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--app-border);
        background: var(--app-surface-soft);
        color: var(--app-text-soft);
        transition: transform .15s ease, border-color .15s ease, color .15s ease;
    }

    .um-icon-btn:hover,
    .um-icon-btn:focus {
        transform: translateY(-1px);
        color: var(--app-text);
        border-color: var(--app-accent);
    }

    .um-icon-btn.is-info {
        color: #0284c7;
    }

    .um-icon-btn.is-warning {
        color: #d97706;
    }

    .um-icon-btn.is-danger {
        color: #dc2626;
    }

    .um-icon-btn.is-lock,
    .um-icon-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
        transform: none;
    }

    .um-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--app-text-muted);
    }

    .um-footer {
        padding: .85rem 1rem;
        border-top: 1px solid var(--app-border);
    }

    .um-modal-form {
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }

    .um-modal-form .form-group {
        margin-bottom: 0;
    }

    .um-password-alert {
        border: 1px solid rgba(245, 158, 11, .28);
        background: rgba(245, 158, 11, .12);
        color: var(--app-text);
        border-radius: 8px;
        padding: .75rem;
        font-size: .9rem;
        line-height: 1.4;
    }

    .um-password-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
        margin-top: .55rem;
    }

    .um-password-result {
        border: 1px solid rgba(34, 197, 94, .24);
        background: rgba(34, 197, 94, .12);
        border-radius: 8px;
        padding: .85rem;
    }

    .um-password-code {
        display: block;
        margin-top: .55rem;
        padding: .7rem;
        border-radius: 8px;
        border: 1px solid var(--app-border);
        background: var(--app-surface-soft);
        color: var(--app-text);
        word-break: break-all;
        font-weight: 800;
    }

    @media (max-width: 991.98px) {
        .um-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .um-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .um-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .um-filter {
            grid-template-columns: 1fr;
        }

        .um-filter .um-btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .um-stats {
            grid-template-columns: 1fr;
        }

        .um-title-wrap {
            align-items: flex-start;
        }

        .um-actions .um-btn {
            width: 100%;
        }
    }
</style>

<div class="um-page">
    <section class="um-hero">
        <div class="um-title-wrap">
            <span class="um-title-icon" aria-hidden="true">
                <i class="fas fa-users-cog"></i>
            </span>
            <div>
                <h2 class="um-title">{{ __('app.user_management.title') }}</h2>
                <p class="um-subtitle">{{ __('app.user_management.subtitle') }}</p>
            </div>
        </div>
        <div class="um-actions">
            <a href="{{ route('user-database.login-history') }}" class="um-btn um-btn-secondary">
                <i class="fas fa-history"></i>
                {{ __('app.user_management.login_history') }}
            </a>
            <button id="toggle-user-registration-modal" type="button" class="um-btn um-btn-primary">
                <i class="fas fa-user-plus"></i>
                {{ __('app.user_management.add_user') }}
            </button>
        </div>
    </section>

    <section class="um-stats" aria-label="{{ __('app.user_management.quick_actions') }}">
        <div class="um-stat">
            <span class="um-stat-icon"><i class="fas fa-users"></i></span>
            <div>
                <p class="um-stat-label">{{ __('app.user_management.total_users') }}</p>
                <p class="um-stat-value">{{ number_format($totalUsers, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="um-stat">
            <span class="um-stat-icon"><i class="fas fa-list-ol"></i></span>
            <div>
                <p class="um-stat-label">{{ __('app.user_management.shown_users') }}</p>
                <p class="um-stat-value">{{ number_format($shownUsers, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="um-stat">
            <span class="um-stat-icon"><i class="fas fa-user-shield"></i></span>
            <div>
                <p class="um-stat-label">{{ __('app.user_management.visible_roles') }}</p>
                <p class="um-stat-value">{{ number_format($visibleRoles, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="um-stat">
            <span class="um-stat-icon"><i class="fas fa-key"></i></span>
            <div>
                <p class="um-stat-label">{{ __('app.user_management.password') }}</p>
                <p class="um-stat-value">{{ $isSuperAdmin ? __('app.user_management.manage_password') : __('app.user_management.reset_password') }}</p>
            </div>
        </div>
    </section>

    <section class="um-panel">
        <div class="um-panel-header">
            <h3 class="um-panel-title">
                <i class="fas fa-filter"></i>
                {{ __('app.user_management.search_label') }}
            </h3>
        </div>
        <form class="um-filter" method="GET" action="{{ route('user-database.index') }}">
            <div class="um-field">
                <label class="um-label" for="keyword">{{ __('app.user_management.search_label') }}</label>
                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="um-control form-control"
                    placeholder="{{ __('app.user_management.search_placeholder') }}"
                />
            </div>
            <button type="submit" class="um-btn um-btn-primary">
                <i class="fas fa-search"></i>
                {{ __('app.user_management.search_button') }}
            </button>
            <a href="{{ route('user-database.index') }}" class="um-btn um-btn-secondary">
                <i class="fas fa-sync-alt"></i>
                {{ __('app.user_management.reset_filter') }}
            </a>
        </form>
    </section>

    <section class="um-panel">
        <div class="um-panel-header">
            <h3 class="um-panel-title">
                <i class="fas fa-address-book"></i>
                {{ __('app.user_management.user_list') }}
            </h3>
            <p class="um-panel-hint">{{ __('app.user_management.table_hint') }}</p>
        </div>

        <div class="um-table-wrap">
            <table class="um-table table mb-0">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('app.user_management.name') }}</th>
                        <th scope="col">{{ __('app.user_management.role') }}</th>
                        <th scope="col" class="text-center">{{ __('app.user_management.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $isSelf = $currentUser && $user->id === $currentUser->id;
                            $isTargetSuperAdmin = $user->role === $superAdminRole;
                            $canManagePassword = $isSuperAdmin && !$isSelf && (!$isTargetSuperAdmin || ($isSystemManagement ?? false));
                            $initial = strtoupper(substr(trim($user->name ?: $user->email), 0, 1));
                        @endphp
                        <tr>
                            <th scope="row">{{ $users->firstItem() + $loop->index }}</th>
                            <td>
                                <div class="um-user">
                                    <span class="um-avatar">{{ $initial }}</span>
                                    <span>
                                        <span class="um-user-name">{{ $user->name }}</span>
                                        <span class="um-user-email">{{ $user->email }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="um-role">
                                    <i class="fas fa-id-badge"></i>
                                    {{ $user->role ?: __('app.user_management.no_role') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($isSelf)
                                    <span class="um-role" title="{{ __('app.user_management.cannot_manage_self') }}">
                                        <i class="fas fa-lock"></i>
                                        {{ __('app.user_management.cannot_manage_self') }}
                                    </span>
                                @else
                                    <div class="um-row-actions">
                                        <button
                                            type="button"
                                            class="um-icon-btn is-info js-send-reset-password-link"
                                            data-url="{{ route('user-database.send-reset-password-link', $user->id) }}"
                                            title="{{ __('app.user_management.reset_password') }}"
                                            aria-label="{{ __('app.user_management.reset_password') }}"
                                        >
                                            <i class="fas fa-link"></i>
                                        </button>

                                        @if($canManagePassword)
                                            <button
                                                type="button"
                                                class="um-icon-btn is-info js-manage-password"
                                                data-url="{{ route('user-database.password.update', $user->id) }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->email }}"
                                                title="{{ __('app.user_management.manage_password') }}"
                                                aria-label="{{ __('app.user_management.manage_password') }}"
                                            >
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @elseif($isSuperAdmin && $isTargetSuperAdmin && !($isSystemManagement ?? false))
                                            <button
                                                type="button"
                                                class="um-icon-btn is-lock"
                                                disabled
                                                title="{{ __('app.user_management.super_admin_password_locked') }}"
                                                aria-label="{{ __('app.user_management.super_admin_password_locked') }}"
                                            >
                                                <i class="fas fa-user-lock"></i>
                                            </button>
                                        @endif

                                        <button
                                            type="button"
                                            class="um-icon-btn is-warning js-toggle-update-user"
                                            data-user-id="{{ $user->id }}"
                                            data-url="{{ route('user-database.show', $user->id) }}"
                                            title="{{ __('app.user_management.edit_user') }}"
                                            aria-label="{{ __('app.user_management.edit_user') }}"
                                        >
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="um-icon-btn is-danger js-delete-user"
                                            data-url="{{ route('user-database.delete', $user->id) }}"
                                            title="{{ __('app.user_management.delete_user') }}"
                                            aria-label="{{ __('app.user_management.delete_user') }}"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="um-empty">
                                    <i class="fas fa-search mb-2"></i>
                                    <div>{{ __('app.user_management.empty') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="um-footer">
            {{ $users->links() }}
        </div>
    </section>
</div>
@stop

@section('js')
<script>
    const userRoleOptions = @json($roleOptions);
    const userManagementI18n = @json($userManagementI18n);

    function escapeHtml(value)
    {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function constructUserForm(user = null)
    {
        const selectedRole = user?.role ?? '';
        const roleOptions = userRoleOptions
            .map((role) => `
                <option value="${escapeHtml(role.value)}" ${role.value === selectedRole ? 'selected' : ''}>
                    ${escapeHtml(role.label)}
                </option>
            `)
            .join('');

        return `
            <form id="user-form" class="um-modal-form">
                <div class="form-group">
                    <label class="um-label" for="name">${escapeHtml(userManagementI18n.name)}</label>
                    <input type="text" name="name" class="form-control" value="${escapeHtml(user?.name ?? '')}" placeholder="${escapeHtml(userManagementI18n.namePlaceholder)}" required>
                </div>
                <div class="form-group">
                    <label class="um-label" for="email">${escapeHtml(userManagementI18n.email)}</label>
                    <input type="email" name="email" class="form-control" value="${escapeHtml(user?.email ?? '')}" placeholder="${escapeHtml(userManagementI18n.emailPlaceholder)}" required>
                </div>
                <div class="form-group">
                    <label class="um-label" for="role">${escapeHtml(userManagementI18n.role)}</label>
                    <select name="role" class="form-control" required>
                        <option value="">${escapeHtml(userManagementI18n.selectRole)}</option>
                        ${roleOptions}
                    </select>
                </div>
            </form>
        `;
    }

    function generatePassword(length = 14)
    {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        const values = new Uint32Array(length);

        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(values);
            return Array.from(values, (value) => chars[value % chars.length]).join('');
        }

        return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    }

    function constructPasswordForm(user)
    {
        return `
            <form id="user-password-form" class="um-modal-form">
                <div class="um-password-alert">
                    <strong>${escapeHtml(userManagementI18n.currentPasswordUnavailable)}</strong><br>
                    ${escapeHtml(userManagementI18n.passwordNotice)}
                </div>
                <div>
                    <span class="um-user-name">${escapeHtml(user.name)}</span>
                    <span class="um-user-email">${escapeHtml(user.email)}</span>
                </div>
                <div class="form-group">
                    <label class="um-label" for="user-password-input">${escapeHtml(userManagementI18n.newPassword)}</label>
                    <input id="user-password-input" type="password" name="password" class="form-control" minlength="8" placeholder="${escapeHtml(userManagementI18n.newPasswordPlaceholder)}" required>
                    <div class="um-password-actions">
                        <button type="button" id="generate-user-password-button" class="um-btn um-btn-secondary">
                            <i class="fas fa-magic"></i>
                            ${escapeHtml(userManagementI18n.generatePassword)}
                        </button>
                        <button type="button" id="toggle-password-visibility-button" class="um-btn um-btn-secondary" data-visible="0">
                            <i class="fas fa-eye"></i>
                            <span>${escapeHtml(userManagementI18n.showPassword)}</span>
                        </button>
                        <button type="button" id="copy-user-password-button" class="um-btn um-btn-secondary">
                            <i class="fas fa-copy"></i>
                            ${escapeHtml(userManagementI18n.copyPassword)}
                        </button>
                    </div>
                </div>
            </form>
        `;
    }

    async function copyText(value)
    {
        if (!value) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
            return;
        }

        const tempInput = document.createElement('textarea');
        tempInput.value = value;
        tempInput.setAttribute('readonly', '');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
    }

    function showPasswordResult(password)
    {
        const body = `
            <div class="um-password-result">
                <strong>${escapeHtml(userManagementI18n.passwordUpdated)}</strong>
                <p class="mb-0 mt-2">${escapeHtml(userManagementI18n.passwordGeneratedNote)}</p>
                <code id="saved-password-result" class="um-password-code">${escapeHtml(password)}</code>
            </div>
        `;
        const buttons = `
            <button type="button" id="copy-saved-password-button" class="btn btn-sm btn-primary">
                <i class="fas fa-copy"></i>
                ${escapeHtml(userManagementI18n.copyPassword)}
            </button>
        `;

        modal.show(userManagementI18n.managePassword, body, buttons);
    }

    $(function() {
        $('#toggle-user-registration-modal').click(function() {
            const form = constructUserForm();
            const buttons = `
                <button id="register-user-button" class="btn btn-sm btn-primary">${escapeHtml(userManagementI18n.save)}</button>
            `;

            modal.show(@json(__('app.user_management.add_user')), form, buttons);
        });

        $(document).on('click', '#register-user-button', async function() {
            Loading.show();

            try
            {
                const form = document.getElementById('user-form');
                const formData = new FormData(form);

                await Http.post("{{ route('user-database.store') }}", formData);

                modal.hide();
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '.js-send-reset-password-link', async function() {
            try
            {
                const confirmation = await Notification.confirmation(@json(__('app.user_management.reset_password_confirm')));
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                await Http.post($(this).data('url'));
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '.js-manage-password', function() {
            const user = {
                name: $(this).data('user-name'),
                email: $(this).data('user-email'),
            };
            const form = constructPasswordForm(user);
            const buttons = `
                <button id="update-user-password-button" data-url="${escapeHtml($(this).data('url'))}" class="btn btn-sm btn-primary">
                    <i class="fas fa-save"></i>
                    ${escapeHtml(userManagementI18n.save)}
                </button>
            `;

            modal.show(userManagementI18n.managePassword, form, buttons);
            $('#user-password-input').val(generatePassword()).trigger('input');
        });

        $(document).on('click', '#generate-user-password-button', function() {
            $('#user-password-input').val(generatePassword()).trigger('input').focus();
        });

        $(document).on('click', '#toggle-password-visibility-button', function() {
            const input = $('#user-password-input');
            const isVisible = $(this).data('visible') === 1;
            input.attr('type', isVisible ? 'password' : 'text');
            $(this).data('visible', isVisible ? 0 : 1);
            $(this).find('i').toggleClass('fa-eye', isVisible).toggleClass('fa-eye-slash', !isVisible);
            $(this).find('span').text(isVisible ? userManagementI18n.showPassword : userManagementI18n.hidePassword);
        });

        $(document).on('click', '#copy-user-password-button', async function() {
            await copyText($('#user-password-input').val());
            Notification.success(userManagementI18n.passwordCopied);
        });

        $(document).on('click', '#copy-saved-password-button', async function() {
            await copyText($('#saved-password-result').text());
            Notification.success(userManagementI18n.passwordCopied);
        });

        $(document).on('click', '#update-user-password-button', async function() {
            const confirmation = await Notification.confirmation(userManagementI18n.passwordUpdateConfirm);
            if(!confirmation.isConfirmed)
                return;

            Loading.show();

            try
            {
                const form = document.getElementById('user-password-form');
                const formData = new FormData(form);
                const password = formData.get('password');

                await Http.post($(this).data('url'), formData);

                Notification.success(userManagementI18n.passwordUpdated);
                showPasswordResult(password);
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '.js-toggle-update-user', async function() {
            Loading.show();
            try
            {
                const userId = $(this).data('user-id');
                const url = $(this).data('url');

                const user = await Http.get(url);

                const form = constructUserForm(user.data);
                const buttons = `
                    <button id="update-user-button" data-user-id="${escapeHtml(userId)}" class="btn btn-sm btn-warning">${escapeHtml(userManagementI18n.save)}</button>
                `;

                modal.show(@json(__('app.user_management.update_user')), form, buttons);
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '#update-user-button', async function() {
            Loading.show();
            try
            {
                const userId = $(this).data('user-id');

                const form = document.getElementById('user-form');
                const formData = new FormData(form);

                formData.append('id', userId);
                formData.set('_method', 'PUT');

                await Http.post("{{ route('user-database.update') }}", formData);

                modal.hide();
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '.js-delete-user', async function() {
            const confirmation = await Notification.confirmation(@json(__('app.user_management.delete_confirm')));
            if(!confirmation.isConfirmed)
                return;

            Loading.show();
            try
            {
                await Http.delete($(this).data('url'));
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });
    });
</script>
@stop

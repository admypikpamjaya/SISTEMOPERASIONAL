@extends('layouts.app')

@section('title', __('app.blast.manage_whatsapp_devices_title'))

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --navy:         #1e2a4a;
    --navy-light:   #2d3d66;
    --blue-primary: #2563eb;
    --blue-mid:     #3b82f6;
    --blue-light:   #dbeafe;
    --blue-lighter: #eff6ff;
    --blue-border:  #bfdbfe;
    --wa-green:     #25d366;
    --wa-dark:      #128c7e;
    --text-dark:    #0f172a;
    --text-muted:   #64748b;
    --bg:           #f0f4fd;
    --white:        #ffffff;
    --green:        #16a34a;
    --green-bg:     #dcfce7;
    --green-border: #86efac;
    --red:          #dc2626;
    --red-bg:       #fee2e2;
    --red-border:   #fca5a5;
    --yellow:       #d97706;
    --yellow-bg:    #fef3c7;
    --yellow-border:#fcd34d;
    --shadow:       0 4px 20px rgba(15,23,42,.09);
    --shadow-lg:    0 8px 32px rgba(15,23,42,.13);
    --radius:       14px;
    --radius-sm:    9px;
}

body,
.content-wrapper,
.main-content { background: var(--bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

.wa-manage-page { padding: 20px; min-height: 100vh; color: var(--text-dark); }
.wa-page-header {
    display: flex; align-items: center; gap: 16px; padding: 20px 26px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius); margin-bottom: 18px; box-shadow: var(--shadow-lg);
}
.wa-header-icon {
    width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--wa-green), var(--wa-dark));
    box-shadow: 0 6px 20px rgba(37,211,102,.42);
}
.wa-header-title  { font-size: 22px; font-weight: 800; color: #fff; }
.wa-header-sub    { font-size: 13px; color: rgba(255,255,255,.6); font-weight: 500; margin-top: 2px; }
.wa-header-actions { margin-left: auto; display: flex; gap: 10px; }
.wa-header-btn {
    border: none; border-radius: 999px; padding: 8px 14px; text-decoration: none;
    font-size: 12px; font-weight: 800; color: var(--navy); background: #fff;
    box-shadow: 0 6px 16px rgba(15,23,42,.18); display: inline-flex; align-items: center; gap: 6px;
}

.wa-card { background: var(--white); border: 1px solid var(--blue-border); border-radius: var(--radius); box-shadow: var(--shadow); }
.wa-device-card { padding: 20px 24px; }
.wa-device-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 16px; align-items: stretch; }
.wa-device-status-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.wa-device-status-badge {
    padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: .02em;
    border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px;
}
.wa-device-status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.wa-device-status-badge.connected { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
.wa-device-status-badge.qr { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
.wa-device-status-badge.disconnected { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }
.wa-device-status-badge.init { background: var(--blue-lighter); color: var(--blue-primary); border-color: var(--blue-border); }
.wa-device-sub { font-size: 12px; color: var(--text-muted); font-weight: 500; }
.wa-device-meta { display: grid; gap: 6px; margin: 12px 0 14px; }
.wa-device-meta-row { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; }
.wa-device-meta-row .meta-label { color: var(--text-muted); font-weight: 600; }
.wa-device-meta-row .meta-value { color: var(--text-dark); font-weight: 700; text-align: right; word-break: break-all; }
.wa-device-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.wa-device-hint { font-size: 11.5px; color: var(--text-muted); }
.wa-btn {
    border: none; border-radius: 10px; padding: 8px 14px; font-size: 12px; font-weight: 800;
    color: #fff; background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    box-shadow: 0 4px 12px rgba(37,99,235,.25); cursor: pointer;
}
.wa-btn.danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 4px 12px rgba(239,68,68,.25);
}
.wa-qr-box {
    border: 1px dashed var(--blue-border); border-radius: var(--radius-sm); padding: 14px; height: 100%;
    background: var(--blue-lighter); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
}
.wa-qr-title { font-size: 12.5px; font-weight: 800; color: var(--navy); }
.wa-qr-img { max-width: 260px; width: 100%; border-radius: 10px; background: #fff; padding: 8px; border: 1px solid var(--blue-border); display: none; }
.wa-qr-placeholder { font-size: 12px; color: var(--text-muted); text-align: center; }

.wa-provider-card { padding: 16px 20px; margin-bottom: 16px; }
.wa-provider-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.wa-provider-title { font-size: 14px; font-weight: 800; color: var(--navy); }
.wa-provider-note { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
.wa-provider-badge {
    padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: .02em;
    border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px;
}
.wa-provider-badge.gateway { background: var(--blue-lighter); color: var(--blue-primary); border-color: var(--blue-border); }
.wa-provider-badge.wablas { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }

.wa-device-list {
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 12px; margin-bottom: 14px; background: #fff;
}
.device-list-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.device-list-title { font-size: 13px; font-weight: 800; color: var(--navy); }
.device-list-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.device-rename-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
.device-input {
    border: 1px solid var(--blue-border); border-radius: 8px; padding: 6px 8px;
    font-size: 12px; min-width: 140px;
}
.device-list-body { display: flex; flex-direction: column; gap: 8px; max-height: 220px; overflow: auto; }
.device-item {
    border: 1px solid var(--blue-border); border-radius: 10px; padding: 10px 12px; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    transition: .15s;
}
.device-item.active { border-color: var(--blue-primary); box-shadow: 0 4px 10px rgba(37,99,235,.15); }
.device-item-title { font-size: 12.5px; font-weight: 700; color: var(--text-dark); }
.device-item-sub { font-size: 11px; color: var(--text-muted); }
.device-item-status { font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 999px; border: 1px solid transparent; }
.device-item-status.connected { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
.device-item-status.qr { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
.device-item-status.disconnected { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }
.device-item-status.init { background: var(--blue-lighter); color: var(--blue-primary); border-color: var(--blue-border); }

@media (max-width: 1024px) {
    .wa-device-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .wa-manage-page { padding: 12px; }
    .wa-header-actions { width: 100%; justify-content: flex-start; }
}
</style>

<div class="wa-manage-page">
    <div class="wa-page-header">
        <div class="wa-header-icon">
            <svg width="28" height="28" viewBox="0 0 16 16" aria-hidden="true">
                <path fill="#ffffff" d="M13.601 2.326A7.854 7.854 0 0 0 8.05 0C3.68 0 .118 3.562.118 7.932c0 1.4.366 2.767 1.06 3.97L0 16l4.22-1.106a7.9 7.9 0 0 0 3.83.977h.003c4.37 0 7.932-3.562 7.932-7.932a7.87 7.87 0 0 0-2.384-5.613zm-5.55 12.21h-.002a6.57 6.57 0 0 1-3.35-.92l-.24-.142-2.503.656.667-2.44-.156-.25a6.56 6.56 0 0 1-1.01-3.507c0-3.62 2.947-6.567 6.57-6.567 1.753 0 3.4.683 4.64 1.924a6.52 6.52 0 0 1 1.922 4.643c-.002 3.62-2.95 6.566-6.57 6.566zm3.6-4.9c-.197-.1-1.165-.575-1.345-.64-.18-.067-.312-.1-.444.1-.132.198-.51.64-.625.773-.115.132-.23.149-.427.05-.197-.1-.832-.307-1.585-.98-.585-.52-.98-1.162-1.095-1.36-.115-.198-.012-.305.087-.404.09-.09.198-.23.296-.345.099-.116.132-.198.198-.33.066-.132.033-.248-.017-.347-.05-.1-.444-1.07-.608-1.466-.16-.387-.323-.334-.444-.34l-.378-.006a.73.73 0 0 0-.53.248c-.18.198-.69.675-.69 1.646 0 .97.706 1.91.805 2.042.099.132 1.39 2.124 3.37 2.977.47.203.837.324 1.123.415.472.15.902.129 1.242.078.379-.056 1.165-.476 1.33-.936.165-.46.165-.855.116-.936-.05-.083-.18-.132-.378-.23z"/>
            </svg>
        </div>
        <div>
            <div class="wa-header-title">{{ __('app.blast.manage_whatsapp_devices_title') }}</div>
            <div class="wa-header-sub">{{ __('app.blast.manage_whatsapp_devices_subtitle') }}</div>
        </div>
        <div class="wa-header-actions">
            <a href="{{ route('admin.blast.whatsapp') }}" class="wa-header-btn">
                <i class="fas fa-arrow-left"></i> {{ __('app.blast.back') }}
            </a>
        </div>
    </div>

    <div class="wa-card wa-provider-card" id="waProviderCard">
        <div class="wa-provider-row">
            <div>
                <div class="wa-provider-title">{{ __('app.blast.provider_mode_whatsapp') }}</div>
                <div class="wa-provider-note" id="waProviderNote">{{ __('app.blast.provider_note') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="wa-provider-badge gateway" id="waProviderBadge">{{ __('app.blast.gateway') }}</span>
                <button type="button" class="wa-btn" id="waProviderToggleBtn">{{ __('app.blast.activate_wablas') }}</button>
            </div>
        </div>
    </div>

    <div class="wa-card wa-device-card" id="waDeviceCard">
        <div class="wa-device-grid">
            <div>
                <div class="wa-device-list">
                    <div class="device-list-head">
                        <div class="device-list-title">{{ __('app.blast.device_list') }}</div>
                        <div class="device-list-actions">
                            <input type="text" class="device-input" id="newDeviceId" placeholder="{{ __('app.blast.device_id_placeholder') }}">
                            <button type="button" class="wa-btn" id="generateDeviceBtn">{{ __('app.blast.generate_id') }}</button>
                            <button type="button" class="wa-btn" id="addDeviceBtn">{{ __('app.blast.add') }}</button>
                        </div>
                    </div>
                    <div class="device-list-body" id="deviceList">
                        <div class="wa-device-sub">{{ __('app.blast.no_devices') }}</div>
                    </div>
                    <div class="device-rename-row">
                        <input type="text" class="device-input" id="renameDeviceInput" placeholder="{{ __('app.blast.device_name_placeholder') }}">
                        <button type="button" class="wa-btn" id="renameDeviceBtn">{{ __('app.blast.rename_device') }}</button>
                    </div>
                </div>
                <div class="wa-device-status-row">
                    <span class="wa-device-status-badge init" id="waStatusBadge">{{ __('app.blast.loading') }}</span>
                    <span class="wa-device-sub" id="waStatusSub">{{ __('app.blast.gateway_waiting') }}</span>
                </div>

                <div class="wa-device-meta">
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.device_id') }}</span>
                        <span class="meta-value" id="waDeviceId">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.connected_number') }}</span>
                        <span class="meta-value" id="waDevicePhone">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.connected_since') }}</span>
                        <span class="meta-value" id="waDeviceSince">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.gateway_base_url') }}</span>
                        <span class="meta-value">{{ $gatewayConfig['base_url'] ?? '-' }}</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.header_token') }}</span>
                        <span class="meta-value">{{ $gatewayConfig['api_key_header'] ?? 'X-API-KEY' }}</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">{{ __('app.blast.gateway_api_token') }}</span>
                        <span class="meta-value">{{ $gatewayConfig['api_key_display'] ?? '-' }}</span>
                    </div>
                </div>

                <div class="wa-device-actions">
                    <button type="button" class="wa-btn" id="waRefreshStatusBtn">{{ __('app.blast.refresh_status') }}</button>
                    <button type="button" class="wa-btn" id="waConnectDeviceBtn">{{ __('app.blast.connect_device') }}</button>
                    <button type="button" class="wa-btn" id="waActivateDeviceBtn">{{ __('app.blast.activate_device') }}</button>
                    <button type="button" class="wa-btn danger" id="waForceReconnectBtn">{{ __('app.blast.force_reconnect_device') }}</button>
                    <button type="button" class="wa-btn danger" id="waDisconnectDeviceBtn">{{ __('app.blast.disconnect_device') }}</button>
                    <button type="button" class="wa-btn danger" id="waDeleteDeviceBtn">{{ __('app.blast.delete_device') }}</button>
                    <button type="button" class="wa-btn danger" id="waResetDevicesBtn">{{ __('app.blast.reset_all_devices') }}</button>
                    <div class="wa-device-hint">{{ __('app.blast.connected_delivery_hint') }}</div>
                </div>
            </div>
            <div>
                <div class="wa-qr-box">
                    <div class="wa-qr-title">{{ __('app.blast.scan_whatsapp_qr') }}</div>
                    <img id="waQrImage" class="wa-qr-img" alt="QR WhatsApp">
                    <div class="wa-qr-placeholder" id="waQrPlaceholder">{{ __('app.blast.qr_waiting_placeholder') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gatewayStatusUrl = @json(route('admin.blast.whatsapp.gateway-status'));
        const gatewayDevicesUrl = @json(route('admin.blast.whatsapp.gateway-devices'));
        const gatewayDevicesCreateUrl = @json(route('admin.blast.whatsapp.gateway-devices.create'));
        const gatewayDeviceConnectUrl = @json(route('admin.blast.whatsapp.gateway-devices.connect', ['deviceId' => '__DEVICE__']));
        const gatewayDeviceActivateUrl = @json(route('admin.blast.whatsapp.gateway-devices.activate', ['deviceId' => '__DEVICE__']));
        const gatewayDeviceReconnectUrl = @json(route('admin.blast.whatsapp.gateway-devices.reconnect', ['deviceId' => '__DEVICE__']));
        const gatewayDeviceDisconnectUrl = @json(route('admin.blast.whatsapp.gateway-devices.disconnect', ['deviceId' => '__DEVICE__']));
        const gatewayDeviceRenameUrl = @json(route('admin.blast.whatsapp.gateway-devices.rename', ['deviceId' => '__DEVICE__']));
        const gatewayDeviceDeleteUrl = @json(route('admin.blast.whatsapp.gateway-devices.delete', ['deviceId' => '__DEVICE__']));
        const gatewayDevicesResetUrl = @json(route('admin.blast.whatsapp.gateway-devices.reset'));
        const providerStatusUrl = @json(route('admin.blast.whatsapp.provider-status'));
        const providerUpdateUrl = @json(route('admin.blast.whatsapp.provider-update'));

        const waDeviceCard = document.getElementById('waDeviceCard');
        const waRefreshStatusBtn = document.getElementById('waRefreshStatusBtn');
        const waForceReconnectBtn = document.getElementById('waForceReconnectBtn');
        const waConnectDeviceBtn = document.getElementById('waConnectDeviceBtn');
        const waActivateDeviceBtn = document.getElementById('waActivateDeviceBtn');
        const waDeleteDeviceBtn = document.getElementById('waDeleteDeviceBtn');
        const waDisconnectDeviceBtn = document.getElementById('waDisconnectDeviceBtn');
        const waResetDevicesBtn = document.getElementById('waResetDevicesBtn');
        const waStatusBadge = document.getElementById('waStatusBadge');
        const waStatusSub = document.getElementById('waStatusSub');
        const waDevicePhone = document.getElementById('waDevicePhone');
        const waDeviceSince = document.getElementById('waDeviceSince');
        const waDeviceId = document.getElementById('waDeviceId');
        const waQrImage = document.getElementById('waQrImage');
        const waQrPlaceholder = document.getElementById('waQrPlaceholder');
        const deviceList = document.getElementById('deviceList');
        const newDeviceIdInput = document.getElementById('newDeviceId');
        const generateDeviceBtn = document.getElementById('generateDeviceBtn');
        const addDeviceBtn = document.getElementById('addDeviceBtn');
        const renameDeviceInput = document.getElementById('renameDeviceInput');
        const renameDeviceBtn = document.getElementById('renameDeviceBtn');
        const waProviderBadge = document.getElementById('waProviderBadge');
        const waProviderNote = document.getElementById('waProviderNote');
        const waProviderToggleBtn = document.getElementById('waProviderToggleBtn');
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
        const blastText = {
            gateway: @json(__('app.blast.gateway')),
            wablas: 'Wablas',
            activateWablas: @json(__('app.blast.activate_wablas')),
            activateGateway: @json(__('app.blast.activate_gateway')),
            currentWablasNote: @json(__('app.blast.current_wablas_note')),
            currentGatewayNote: @json(__('app.blast.current_gateway_note')),
            providerUpdateFailed: @json(__('app.blast.provider_update_failed')),
            providerChangeFailed: @json(__('app.blast.provider_change_failed')),
            noDevices: @json(__('app.blast.no_devices')),
            gatewayUnreachable: @json(__('app.blast.gateway_unreachable')),
            gatewayProblem: @json(__('app.blast.gateway_problem')),
            activeSuffix: @json(__('app.blast.active_suffix')),
            connectedShort: @json(__('app.blast.connected_short')),
            notConnectedShort: @json(__('app.blast.not_connected_short')),
            statusConnected: @json(__('app.blast.status_connected')),
            statusQr: @json(__('app.blast.status_qr')),
            statusDisconnected: @json(__('app.blast.status_disconnected')),
            statusInit: @json(__('app.blast.status_init')),
            statusConnectedUpper: @json(__('app.blast.status_connected_upper')),
            statusQrUpper: @json(__('app.blast.status_qr_upper')),
            statusDisconnectedUpper: @json(__('app.blast.status_disconnected_upper')),
            statusInitUpper: @json(__('app.blast.status_init_upper')),
            statusConnectedSub: @json(__('app.blast.status_connected_sub')),
            statusQrSub: @json(__('app.blast.status_qr_sub')),
            statusDisconnectedSub: @json(__('app.blast.status_disconnected_sub')),
            statusInitSub: @json(__('app.blast.status_init_sub')),
            statusUnknown: @json(__('app.blast.status_unknown')),
            qrNotNeeded: @json(__('app.blast.qr_not_needed')),
            qrWaitingPlaceholder: @json(__('app.blast.qr_waiting_placeholder')),
            createDeviceFailed: @json(__('app.blast.create_device_failed')),
            createDeviceUniqueFailed: @json(__('app.blast.create_device_unique_failed')),
            connectDeviceFailed: @json(__('app.blast.connect_device_failed')),
            connectDeviceRetryFailed: @json(__('app.blast.connect_device_retry_failed')),
            activateDeviceFailed: @json(__('app.blast.activate_device_failed')),
            reconnectFailed: @json(__('app.blast.reconnect_failed')),
            reconnectRetryFailed: @json(__('app.blast.reconnect_retry_failed')),
            disconnectDeviceFailed: @json(__('app.blast.disconnect_device_failed')),
            deviceNameRequired: @json(__('app.blast.device_name_required')),
            renameDeviceFailed: @json(__('app.blast.rename_device_failed')),
            deleteDeviceConfirm: @json(__('app.blast.delete_device_confirm', ['device' => '__DEVICE__'])),
            deleteDeviceFailed: @json(__('app.blast.delete_device_failed')),
            resetDevicesConfirm: @json(__('app.blast.reset_devices_confirm')),
            resetDevicesFailed: @json(__('app.blast.reset_devices_failed')),
        };

        let devices = [];
        let selectedDeviceId = null;
        let activeDeviceId = null;
        let currentProvider = @json($providerState['current'] ?? 'gateway');

        function formatGatewayTime(value) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        }

        function normalizeGatewayPhone(user) {
            if (!user || !user.id) return '-';
            const raw = String(user.id);
            return raw.includes('@') ? raw.split('@')[0] : raw;
        }

        function buildDeviceUrl(templateUrl, deviceId) {
            return templateUrl.replace('__DEVICE__', encodeURIComponent(deviceId));
        }

        function generateDeviceId() {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const stamp = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
            const random = Math.floor(Math.random() * 900 + 100);
            return `device-${stamp}-${random}`;
        }

        function updateProviderUi(provider) {
            currentProvider = provider || 'gateway';
            if (!waProviderBadge || !waProviderToggleBtn || !waProviderNote) return;
            const isWablas = currentProvider === 'wablas';
            waProviderBadge.classList.toggle('gateway', !isWablas);
            waProviderBadge.classList.toggle('wablas', isWablas);
            waProviderBadge.textContent = isWablas ? blastText.wablas : blastText.gateway;
            waProviderToggleBtn.textContent = isWablas ? blastText.activateGateway : blastText.activateWablas;
            waProviderNote.textContent = isWablas
                ? blastText.currentWablasNote
                : blastText.currentGatewayNote;
        }

        updateProviderUi(currentProvider);

        async function fetchProviderStatus() {
            if (!providerStatusUrl) return;
            try {
                const response = await fetch(providerStatusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const payload = await response.json();
                if (payload?.success) {
                    updateProviderUi(payload?.data?.provider || currentProvider);
                }
            } catch (error) {
                // ignore
            }
        }

        async function toggleProvider() {
            if (!providerUpdateUrl) return;
            const nextProvider = currentProvider === 'wablas' ? 'gateway' : 'wablas';
            try {
                const response = await fetch(providerUpdateUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ provider: nextProvider })
                });
                if (!response.ok) throw new Error(blastText.providerUpdateFailed);
                const payload = await response.json();
                if (payload?.success) {
                    updateProviderUi(payload?.data?.provider || nextProvider);
                }
            } catch (error) {
                alert(blastText.providerChangeFailed);
            }
        }

        function renderDeviceList() {
            if (!deviceList) return;
            if (!devices.length) {
                deviceList.innerHTML = `<div class="wa-device-sub">${blastText.noDevices}</div>`;
                return;
            }

            deviceList.innerHTML = '';
            devices.forEach((device) => {
                const item = document.createElement('div');
                item.className = 'device-item' + (device.deviceId === selectedDeviceId ? ' active' : '');
                const label = device.label && String(device.label).trim() !== '' ? device.label : device.deviceId;
                item.innerHTML = `
                    <div>
                        <div class="device-item-title">${label}${device.isActive ? ` (${blastText.activeSuffix})` : ''}</div>
                        <div class="device-item-sub">${device.user?.id ? blastText.connectedShort : blastText.notConnectedShort}</div>
                    </div>
                    <div class="device-item-status ${device.status || 'disconnected'}">${({ connected: blastText.statusConnectedUpper, qr: blastText.statusQrUpper, disconnected: blastText.statusDisconnectedUpper, init: blastText.statusInitUpper }[device.status || 'disconnected'] || String(device.status || 'disconnected').toUpperCase())}</div>
                `;
                item.addEventListener('click', function() {
                    selectedDeviceId = device.deviceId;
                    if (renameDeviceInput) renameDeviceInput.value = label;
                    renderDeviceList();
                    updateGatewayUi(device);
                });
                deviceList.appendChild(item);
            });
        }

        function updateGatewayUi(data) {
            if (!waDeviceCard) return;
            const status = String(data?.status || 'disconnected').toLowerCase();
            const labelMap = {
                connected: blastText.statusConnected,
                qr: blastText.statusQr,
                disconnected: blastText.statusDisconnected,
                init: blastText.statusInit
            };
            const subMap = {
                connected: blastText.statusConnectedSub,
                qr: blastText.statusQrSub,
                disconnected: blastText.statusDisconnectedSub,
                init: blastText.statusInitSub
            };

            if (waStatusBadge) {
                waStatusBadge.classList.remove('connected', 'qr', 'disconnected', 'init');
                waStatusBadge.classList.add(labelMap[status] ? status : 'disconnected');
                waStatusBadge.textContent = labelMap[status] || blastText.statusDisconnected;
            }

            if (waStatusSub) {
                waStatusSub.textContent = subMap[status] || blastText.statusUnknown;
            }

            if (waDevicePhone) {
                waDevicePhone.textContent = normalizeGatewayPhone(data?.user);
            }

            if (waDeviceSince) {
                waDeviceSince.textContent = formatGatewayTime(data?.connectedAt);
            }

            if (waDeviceId) {
                waDeviceId.textContent = data?.deviceId || '-';
            }

            const qrData = data?.qrDataUrl || '';
            if (waQrImage && waQrPlaceholder) {
                if (status === 'qr' && qrData) {
                    waQrImage.src = qrData;
                    waQrImage.style.display = 'block';
                    waQrPlaceholder.style.display = 'none';
                } else {
                    waQrImage.style.display = 'none';
                    waQrPlaceholder.style.display = 'block';
                    waQrPlaceholder.textContent = status === 'connected'
                        ? blastText.qrNotNeeded
                        : blastText.qrWaitingPlaceholder;
                }
            }
        }

        function getSelectedDevice() {
            return devices.find((device) => device.deviceId === selectedDeviceId) || null;
        }

        async function fetchDevices() {
            if (!gatewayDevicesUrl) return;
            try {
                const response = await fetch(gatewayDevicesUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    throw new Error(blastText.gatewayUnreachable);
                }
                const payload = await response.json();
                if (payload?.success === false) {
                    throw new Error(payload?.message || blastText.gatewayProblem);
                }
                const data = payload?.data || {};
                devices = Array.isArray(data.devices) ? data.devices : [];
                activeDeviceId = data.activeDeviceId || devices.find(d => d.isActive)?.deviceId || null;
                if (!selectedDeviceId || !devices.some(d => d.deviceId === selectedDeviceId)) {
                    selectedDeviceId = activeDeviceId || (devices[0]?.deviceId || null);
                }
                renderDeviceList();
                const selected = getSelectedDevice();
                updateGatewayUi(selected || { status: 'disconnected' });
                if (renameDeviceInput && selected) {
                    const label = selected.label && String(selected.label).trim() !== '' ? selected.label : selected.deviceId;
                    renameDeviceInput.value = label;
                }
            } catch (error) {
                updateGatewayUi({ status: 'disconnected' });
                if (deviceList) deviceList.innerHTML = `<div class="wa-device-sub">${blastText.gatewayUnreachable}</div>`;
            }
        }

        async function createDevice() {
            let raw = newDeviceIdInput?.value || '';
            let deviceId = raw.trim().toLowerCase().replace(/[^a-z0-9_-]/g, '');
            if (!deviceId) {
                deviceId = generateDeviceId();
                if (newDeviceIdInput) newDeviceIdInput.value = deviceId;
            }
            try {
                const response = await fetch(gatewayDevicesCreateUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ device_id: deviceId })
                });
                if (!response.ok) throw new Error(blastText.createDeviceFailed);
                const payload = await response.json();
                if (!payload?.success) throw new Error(payload?.message || blastText.createDeviceFailed);
                newDeviceIdInput.value = '';
                selectedDeviceId = deviceId;
                await activateDevice(deviceId, true);
                await fetchDevices();
            } catch (error) {
                alert(blastText.createDeviceUniqueFailed);
            }
        }

        async function connectDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceConnectUrl, selected.deviceId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error(blastText.connectDeviceFailed);
                await fetchDevices();
            } catch (error) {
                alert(blastText.connectDeviceRetryFailed);
            }
        }

        async function activateDevice(forceDeviceId = null, skipDisconnect = false) {
            const selected = forceDeviceId
                ? devices.find((device) => device.deviceId === forceDeviceId)
                : getSelectedDevice();
            if (!selected) return;
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceActivateUrl, selected.deviceId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error(blastText.activateDeviceFailed);
                await fetchDevices();
            } catch (error) {
                alert(blastText.activateDeviceFailed);
            }
        }

        async function reconnectDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceReconnectUrl, selected.deviceId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error(blastText.reconnectFailed);
                await fetchDevices();
            } catch (error) {
                alert(blastText.reconnectRetryFailed);
            }
        }

        async function disconnectDevice(targetDeviceId = null) {
            const deviceId = targetDeviceId || getSelectedDevice()?.deviceId;
            if (!deviceId) return;
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceDisconnectUrl, deviceId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error(blastText.disconnectDeviceFailed);
                await fetchDevices();
            } catch (error) {
                if (!targetDeviceId) {
                    alert(blastText.disconnectDeviceFailed);
                }
            }
        }

        async function renameDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            const label = (renameDeviceInput?.value || '').trim();
            if (label === '') {
                alert(blastText.deviceNameRequired);
                return;
            }
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceRenameUrl, selected.deviceId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ label })
                });
                if (!response.ok) throw new Error(blastText.renameDeviceFailed);
                const payload = await response.json();
                if (!payload?.success) throw new Error(payload?.message || blastText.renameDeviceFailed);
                await fetchDevices();
            } catch (error) {
                alert(blastText.renameDeviceFailed);
            }
        }

        async function deleteDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            if (!confirm(blastText.deleteDeviceConfirm.replace('__DEVICE__', selected.deviceId))) return;
            try {
                const response = await fetch(buildDeviceUrl(gatewayDeviceDeleteUrl, selected.deviceId), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                if (!response.ok) throw new Error(blastText.deleteDeviceFailed);
                selectedDeviceId = null;
                await fetchDevices();
            } catch (error) {
                alert(blastText.deleteDeviceFailed);
            }
        }

            async function resetDevices() {
            if (!gatewayDevicesResetUrl) return;
            if (!confirm(blastText.resetDevicesConfirm)) return;
            try {
                const response = await fetch(gatewayDevicesResetUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });
                if (!response.ok) throw new Error(blastText.resetDevicesFailed);
                await fetchDevices();
            } catch (error) {
                alert(blastText.resetDevicesFailed);
            }
        }

        fetchDevices();
        fetchProviderStatus();

        if (waRefreshStatusBtn) {
            waRefreshStatusBtn.addEventListener('click', function() {
                fetchDevices();
            });
        }
        if (waForceReconnectBtn) {
            waForceReconnectBtn.addEventListener('click', function() {
                reconnectDevice();
            });
        }
        if (waConnectDeviceBtn) {
            waConnectDeviceBtn.addEventListener('click', function() {
                connectDevice();
            });
        }
        if (waActivateDeviceBtn) {
            waActivateDeviceBtn.addEventListener('click', function() {
                activateDevice();
            });
        }
        if (waDeleteDeviceBtn) {
            waDeleteDeviceBtn.addEventListener('click', function() {
                deleteDevice();
            });
        }
        if (waResetDevicesBtn) {
            waResetDevicesBtn.addEventListener('click', function() {
                resetDevices();
            });
        }
        if (waDisconnectDeviceBtn) {
            waDisconnectDeviceBtn.addEventListener('click', function() {
                disconnectDevice();
            });
        }
        if (renameDeviceBtn) {
            renameDeviceBtn.addEventListener('click', function() {
                renameDevice();
            });
        }
        if (addDeviceBtn) {
            addDeviceBtn.addEventListener('click', function() {
                createDevice();
            });
        }
        if (generateDeviceBtn) {
            generateDeviceBtn.addEventListener('click', function() {
                if (newDeviceIdInput) newDeviceIdInput.value = generateDeviceId();
            });
        }
        if (waProviderToggleBtn) {
            waProviderToggleBtn.addEventListener('click', function() {
                toggleProvider();
            });
        }

        setInterval(() => {
            if (document.visibilityState !== 'hidden') fetchDevices();
        }, 5000);
    });
</script>

@endsection

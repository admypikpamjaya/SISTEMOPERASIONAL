@extends('layouts.app')

@section('title', 'Manage Phone WhatsApp')

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
            <div class="wa-header-title">Manage Phone WhatsApp</div>
            <div class="wa-header-sub">Scan QR untuk koneksi gateway. Hanya super admin.</div>
        </div>
        <div class="wa-header-actions">
            <a href="{{ route('admin.blast.whatsapp') }}" class="wa-header-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="wa-card wa-provider-card" id="waProviderCard">
        <div class="wa-provider-row">
            <div>
                <div class="wa-provider-title">Mode Provider WhatsApp</div>
                <div class="wa-provider-note" id="waProviderNote">Menentukan jalur blasting WhatsApp yang aktif.</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="wa-provider-badge gateway" id="waProviderBadge">Gateway</span>
                <button type="button" class="wa-btn" id="waProviderToggleBtn">Aktifkan Wablas</button>
            </div>
        </div>
    </div>

    <div class="wa-card wa-device-card" id="waDeviceCard">
        <div class="wa-device-grid">
            <div>
                <div class="wa-device-list">
                    <div class="device-list-head">
                        <div class="device-list-title">Daftar Device</div>
                        <div class="device-list-actions">
                            <input type="text" class="device-input" id="newDeviceId" placeholder="device-id">
                            <button type="button" class="wa-btn" id="generateDeviceBtn">Generate</button>
                            <button type="button" class="wa-btn" id="addDeviceBtn">Tambah</button>
                        </div>
                    </div>
                    <div class="device-list-body" id="deviceList">
                        <div class="wa-device-sub">Belum ada device.</div>
                    </div>
                    <div class="device-rename-row">
                        <input type="text" class="device-input" id="renameDeviceInput" placeholder="Nama device">
                        <button type="button" class="wa-btn" id="renameDeviceBtn">Rename</button>
                    </div>
                </div>
                <div class="wa-device-status-row">
                    <span class="wa-device-status-badge init" id="waStatusBadge">Memuat...</span>
                    <span class="wa-device-sub" id="waStatusSub">Menunggu data gateway.</span>
                </div>

                <div class="wa-device-meta">
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Device ID</span>
                        <span class="meta-value" id="waDeviceId">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Nomor Terhubung</span>
                        <span class="meta-value" id="waDevicePhone">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Terhubung Sejak</span>
                        <span class="meta-value" id="waDeviceSince">-</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Gateway Base URL</span>
                        <span class="meta-value">{{ $gatewayConfig['base_url'] ?? '-' }}</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Header Token</span>
                        <span class="meta-value">{{ $gatewayConfig['api_key_header'] ?? 'X-API-KEY' }}</span>
                    </div>
                    <div class="wa-device-meta-row">
                        <span class="meta-label">Token API Gateway</span>
                        <span class="meta-value">{{ $gatewayConfig['api_key_display'] ?? '-' }}</span>
                    </div>
                </div>

                <div class="wa-device-actions">
                    <button type="button" class="wa-btn" id="waRefreshStatusBtn">Refresh Status</button>
                    <button type="button" class="wa-btn" id="waConnectDeviceBtn">Connect Device</button>
                    <button type="button" class="wa-btn" id="waActivateDeviceBtn">Aktifkan Device</button>
                    <button type="button" class="wa-btn danger" id="waForceReconnectBtn">Force Reconnect</button>
                    <button type="button" class="wa-btn danger" id="waDisconnectDeviceBtn">Putuskan Device</button>
                    <button type="button" class="wa-btn danger" id="waDeleteDeviceBtn">Hapus Device</button>
                    <div class="wa-device-hint">Jika status Connected, blasting langsung aktif.</div>
                </div>
            </div>
            <div>
                <div class="wa-qr-box">
                    <div class="wa-qr-title">Scan QR WhatsApp</div>
                    <img id="waQrImage" class="wa-qr-img" alt="QR WhatsApp">
                    <div class="wa-qr-placeholder" id="waQrPlaceholder">QR akan muncul di sini jika belum terhubung.</div>
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
        const providerStatusUrl = @json(route('admin.blast.whatsapp.provider-status'));
        const providerUpdateUrl = @json(route('admin.blast.whatsapp.provider-update'));

        const waDeviceCard = document.getElementById('waDeviceCard');
        const waRefreshStatusBtn = document.getElementById('waRefreshStatusBtn');
        const waForceReconnectBtn = document.getElementById('waForceReconnectBtn');
        const waConnectDeviceBtn = document.getElementById('waConnectDeviceBtn');
        const waActivateDeviceBtn = document.getElementById('waActivateDeviceBtn');
        const waDeleteDeviceBtn = document.getElementById('waDeleteDeviceBtn');
        const waDisconnectDeviceBtn = document.getElementById('waDisconnectDeviceBtn');
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
            waProviderBadge.textContent = isWablas ? 'Wablas' : 'Gateway';
            waProviderToggleBtn.textContent = isWablas ? 'Aktifkan Gateway' : 'Aktifkan Wablas';
            waProviderNote.textContent = isWablas
                ? 'Blasting saat ini menggunakan server Wablas.'
                : 'Blasting saat ini menggunakan gateway internal.';
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
                if (!response.ok) throw new Error('Gagal update provider.');
                const payload = await response.json();
                if (payload?.success) {
                    updateProviderUi(payload?.data?.provider || nextProvider);
                }
            } catch (error) {
                alert('Gagal mengubah provider. Coba lagi.');
            }
        }

        function renderDeviceList() {
            if (!deviceList) return;
            if (!devices.length) {
                deviceList.innerHTML = '<div class="wa-device-sub">Belum ada device.</div>';
                return;
            }

            deviceList.innerHTML = '';
            devices.forEach((device) => {
                const item = document.createElement('div');
                item.className = 'device-item' + (device.deviceId === selectedDeviceId ? ' active' : '');
                const label = device.label && String(device.label).trim() !== '' ? device.label : device.deviceId;
                item.innerHTML = `
                    <div>
                        <div class="device-item-title">${label}${device.isActive ? ' (aktif)' : ''}</div>
                        <div class="device-item-sub">${device.user?.id ? 'Connected' : 'Not Connected'}</div>
                    </div>
                    <div class="device-item-status ${device.status || 'disconnected'}">${(device.status || 'disconnected').toUpperCase()}</div>
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
                connected: 'Connected',
                qr: 'Scan QR',
                disconnected: 'Disconnected',
                init: 'Inisialisasi'
            };
            const subMap = {
                connected: 'WhatsApp siap digunakan.',
                qr: 'Silakan scan QR menggunakan WhatsApp.',
                disconnected: 'Tidak terhubung. Pastikan gateway berjalan.',
                init: 'Menunggu koneksi gateway.'
            };

            if (waStatusBadge) {
                waStatusBadge.classList.remove('connected', 'qr', 'disconnected', 'init');
                waStatusBadge.classList.add(labelMap[status] ? status : 'disconnected');
                waStatusBadge.textContent = labelMap[status] || 'Disconnected';
            }

            if (waStatusSub) {
                waStatusSub.textContent = subMap[status] || 'Status tidak diketahui.';
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
                        ? 'Sudah terhubung. QR tidak diperlukan.'
                        : 'QR akan muncul di sini jika belum terhubung.';
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
                    throw new Error('Gateway tidak dapat dihubungi.');
                }
                const payload = await response.json();
                if (payload?.success === false) {
                    throw new Error(payload?.message || 'Gateway error');
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
                if (deviceList) deviceList.innerHTML = '<div class="wa-device-sub">Gateway tidak dapat dihubungi.</div>';
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
                if (!response.ok) throw new Error('Gagal membuat device.');
                const payload = await response.json();
                if (!payload?.success) throw new Error(payload?.message || 'Gagal membuat device.');
                newDeviceIdInput.value = '';
                selectedDeviceId = deviceId;
                await activateDevice(deviceId, true);
                await fetchDevices();
            } catch (error) {
                alert('Gagal membuat device. Pastikan device ID unik.');
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
                if (!response.ok) throw new Error('Gagal connect device.');
                await fetchDevices();
            } catch (error) {
                alert('Connect device gagal. Coba lagi.');
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
                if (!response.ok) throw new Error('Gagal aktifkan device.');
                await fetchDevices();
            } catch (error) {
                alert('Aktifkan device gagal.');
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
                if (!response.ok) throw new Error('Gagal reconnect.');
                await fetchDevices();
            } catch (error) {
                alert('Reconnect gagal.');
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
                if (!response.ok) throw new Error('Gagal memutuskan device.');
                await fetchDevices();
            } catch (error) {
                if (!targetDeviceId) {
                    alert('Putuskan device gagal.');
                }
            }
        }

        async function renameDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            const label = (renameDeviceInput?.value || '').trim();
            if (label === '') {
                alert('Nama device tidak boleh kosong.');
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
                if (!response.ok) throw new Error('Gagal rename device.');
                const payload = await response.json();
                if (!payload?.success) throw new Error(payload?.message || 'Gagal rename device.');
                await fetchDevices();
            } catch (error) {
                alert('Rename device gagal.');
            }
        }

        async function deleteDevice() {
            const selected = getSelectedDevice();
            if (!selected) return;
            if (!confirm(`Hapus device ${selected.deviceId}?`)) return;
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
                if (!response.ok) throw new Error('Gagal hapus device.');
                selectedDeviceId = null;
                await fetchDevices();
            } catch (error) {
                alert('Hapus device gagal.');
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

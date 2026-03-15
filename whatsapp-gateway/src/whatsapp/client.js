const fs = require('fs');
const path = require('path');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const mime = require('mime-types');
const {
  makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  jidNormalizedUser
} = require('@whiskeysockets/baileys');

const env = require('../config/env');
const logger = require('../utils/logger');
const { normalizePhone, isValidPhone } = require('../utils/validator');

const devices = new Map();
let ioInstance = null;
const activeDevicePath = path.join(path.resolve(env.WA_AUTH_FOLDER), 'active_device.json');
let activeDeviceId = env.WA_DEFAULT_DEVICE || 'default';

function readActiveDevice() {
  try {
    if (!fs.existsSync(activeDevicePath)) return null;
    const raw = fs.readFileSync(activeDevicePath, 'utf-8');
    const parsed = JSON.parse(raw);
    const value = String(parsed?.deviceId || '').trim();
    return value !== '' ? value : null;
  } catch (err) {
    return null;
  }
}

function writeActiveDevice(deviceId) {
  try {
    const dir = path.dirname(activeDevicePath);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    fs.writeFileSync(
      activeDevicePath,
      JSON.stringify({ deviceId, updatedAt: new Date().toISOString() }, null, 2)
    );
  } catch (err) {
    logger.error(`[${deviceId}] Failed to persist active device: ${err.message}`);
  }
}

const storedActive = readActiveDevice();
if (storedActive) {
  activeDeviceId = storedActive;
}

function createDeviceState(id) {
  return {
    id,
    sock: null,
    connectionStatus: 'init',
    latestQr: null,
    latestQrDataUrl: null,
    connectedUser: null,
    connectedAt: null,
    lastQrAt: null,
    qrRefreshTimer: null,
    reconnectTimer: null,
    initPromise: null
  };
}

function getDeviceState(id) {
  const deviceId = id || activeDeviceId;
  if (!devices.has(deviceId)) {
    devices.set(deviceId, createDeviceState(deviceId));
  }
  return devices.get(deviceId);
}

function setActiveDevice(deviceId) {
  if (!deviceId) return activeDeviceId;
  activeDeviceId = deviceId;
  getDeviceState(deviceId);
  writeActiveDevice(deviceId);
  return activeDeviceId;
}

function getActiveDeviceId() {
  return activeDeviceId;
}

function emit(event, payload) {
  if (ioInstance) {
    ioInstance.emit(event, payload);
  }
}

function clearQrRefreshTimer(state) {
  if (state.qrRefreshTimer) {
    clearTimeout(state.qrRefreshTimer);
    state.qrRefreshTimer = null;
  }
}

function scheduleQrRefresh(state) {
  clearQrRefreshTimer(state);
  if (env.QR_TTL_MS <= 0) return;
  state.lastQrAt = Date.now();
  state.qrRefreshTimer = setTimeout(() => {
    if (state.connectionStatus === 'qr') {
      logger.warn(`[${state.id}] QR expired, regenerating...`);
      try {
        state.sock?.end(new Error('qr-expired'));
      } catch (err) {
        logger.error(`[${state.id}] Failed to refresh QR: ${err.message}`);
      }
    }
  }, env.QR_TTL_MS);
}

function resolveAuthFolder(deviceId) {
  const baseFolder = path.resolve(env.WA_AUTH_FOLDER);
  const defaultId = env.WA_DEFAULT_DEVICE || 'default';
  const defaultFolder = path.join(baseFolder, defaultId);
  const rootCreds = path.join(baseFolder, 'creds.json');

  if (deviceId === defaultId) {
    if (fs.existsSync(rootCreds) && !fs.existsSync(defaultFolder)) {
      return baseFolder;
    }
  }

  return path.join(baseFolder, deviceId);
}

function clearAuthFolder(deviceId) {
  const baseFolder = path.resolve(env.WA_AUTH_FOLDER);
  const folder = resolveAuthFolder(deviceId);
  try {
    if (path.resolve(folder) === baseFolder) {
      if (!fs.existsSync(baseFolder)) return;
      fs.readdirSync(baseFolder, { withFileTypes: true }).forEach((entry) => {
        if (!entry.isFile()) return;
        const filePath = path.join(baseFolder, entry.name);
        try {
          fs.unlinkSync(filePath);
        } catch (err) {
          logger.error(`[${deviceId}] Failed to remove session file ${entry.name}: ${err.message}`);
        }
      });
      return;
    }

    fs.rmSync(folder, { recursive: true, force: true });
  } catch (err) {
    logger.error(`[${deviceId}] Failed to clear session folder: ${err.message}`);
  }
}

function discoverDeviceIds() {
  const baseFolder = path.resolve(env.WA_AUTH_FOLDER);
  const ids = new Set();
  ids.add(activeDeviceId);
  devices.forEach((_, key) => ids.add(key));

  if (!fs.existsSync(baseFolder)) {
    return Array.from(ids);
  }

  const entries = fs.readdirSync(baseFolder, { withFileTypes: true });
  const hasRootCreds = entries.some(
    (entry) => entry.isFile() && entry.name === 'creds.json'
  );
  if (hasRootCreds) {
    ids.add(env.WA_DEFAULT_DEVICE || 'default');
  }
  entries.forEach((entry) => {
    if (entry.isDirectory()) {
      ids.add(entry.name);
    }
  });

  return Array.from(ids);
}

function buildStatus(state) {
  return {
    deviceId: state.id,
    status: state.connectionStatus,
    qr: state.latestQr,
    qrDataUrl: state.latestQrDataUrl,
    user: state.connectedUser,
    connectedAt: state.connectedAt,
    isActive: state.id === activeDeviceId
  };
}

async function initDevice(deviceId, { io } = {}) {
  const state = getDeviceState(deviceId);
  if (state.sock) return state.sock;
  if (state.initPromise) return state.initPromise;

  state.initPromise = (async () => {
    ioInstance = io || ioInstance;
    const authFolder = resolveAuthFolder(deviceId);
    fs.mkdirSync(authFolder, { recursive: true });

    const { state: authState, saveCreds } = await useMultiFileAuthState(authFolder);
    let version;
    try {
      const latest = await fetchLatestBaileysVersion();
      version = latest.version;
    } catch (err) {
      logger.warn(`[${deviceId}] Failed to fetch latest WA version, using default: ${err.message}`);
    }

    state.sock = makeWASocket({
      version,
      auth: authState,
      printQRInTerminal: env.WA_PRINT_QR,
      browser: ['WA Gateway', 'Chrome', '1.0.0'],
      keepAliveIntervalMs: 30000,
      connectTimeoutMs: 60000,
      defaultQueryTimeoutMs: 60000,
      markOnlineOnConnect: true,
      syncFullHistory: false
    });

    state.sock.ev.on('creds.update', saveCreds);

    state.sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect, qr } = update;

      if (qr) {
        state.connectionStatus = 'qr';
        state.latestQr = qr;
        state.latestQrDataUrl = await qrcode.toDataURL(qr);
        scheduleQrRefresh(state);
        if (env.WA_PRINT_QR) {
          qrcodeTerminal.generate(qr, { small: true });
        }
        emit('wa:qr', { deviceId: state.id, qr, qrDataUrl: state.latestQrDataUrl });
      }

      if (connection === 'open') {
        state.connectionStatus = 'connected';
        state.latestQr = null;
        state.latestQrDataUrl = null;
        clearQrRefreshTimer(state);
        state.connectedUser = state.sock?.user
          ? { id: state.sock.user.id, name: state.sock.user.name || '' }
          : null;
        state.connectedAt = new Date().toISOString();
        emit('wa:status', { deviceId: state.id, status: state.connectionStatus });
        logger.info(`[${state.id}] WhatsApp connected`);
      }

      if (connection === 'close') {
        const statusCode = lastDisconnect?.error?.output?.statusCode;
        const reason = lastDisconnect?.error?.message || lastDisconnect?.error?.toString();
        const isLoggedOut = statusCode === DisconnectReason.loggedOut;
        const shouldReconnect = isLoggedOut ? true : statusCode !== DisconnectReason.loggedOut;

        state.connectionStatus = 'disconnected';
        clearQrRefreshTimer(state);
        state.connectedUser = null;
        state.connectedAt = null;
        emit('wa:status', { deviceId: state.id, status: state.connectionStatus });
        logger.warn(
          `[${state.id}] WhatsApp disconnected. status=${statusCode ?? 'unknown'} reason=${reason || 'n/a'} Reconnect: ${shouldReconnect}`
        );

        if (isLoggedOut) {
          logger.warn(`[${state.id}] WhatsApp logged out. Clearing session to request new QR.`);
          clearAuthFolder(state.id);
        }

        state.sock = null;
        if (shouldReconnect) {
          if (state.reconnectTimer) return;
          state.reconnectTimer = setTimeout(() => {
            state.reconnectTimer = null;
            initDevice(state.id, { io: ioInstance }).catch((err) => {
              logger.error(`[${state.id}] Re-init failed: ${err.message}`);
            });
          }, env.RECONNECT_DELAY_MS);
        }
      }
    });

    return state.sock;
  })();

  try {
    return await state.initPromise;
  } finally {
    state.initPromise = null;
  }
}

async function initWhatsApp({ io } = {}) {
  return initDevice(activeDeviceId, { io });
}

async function ensureReady(deviceId = activeDeviceId, timeoutMs = 60000) {
  const state = getDeviceState(deviceId);
  if (!state.sock) {
    await initDevice(deviceId, { io: ioInstance });
  }

  const start = Date.now();
  while (state.connectionStatus !== 'connected') {
    if (Date.now() - start > timeoutMs) {
      throw new Error('WhatsApp not connected');
    }
    await new Promise((resolve) => setTimeout(resolve, 1000));
  }
}

function toJid(phone) {
  const clean = normalizePhone(phone);
  if (!isValidPhone(clean)) {
    throw new Error('Invalid phone number');
  }
  return jidNormalizedUser(`${clean}@s.whatsapp.net`);
}

async function sendText(phone, message, deviceId = activeDeviceId) {
  await ensureReady(deviceId);
  const jid = toJid(phone);
  const state = getDeviceState(deviceId);
  return state.sock.sendMessage(jid, { text: message });
}

async function sendFile(phone, filePath, caption, originalName, deviceId = activeDeviceId) {
  await ensureReady(deviceId);
  const jid = toJid(phone);
  const buffer = fs.readFileSync(filePath);
  const mimetype = mime.lookup(filePath) || 'application/octet-stream';
  const filename = originalName || path.basename(filePath);
  const state = getDeviceState(deviceId);

  if (mimetype.startsWith('image/')) {
    return state.sock.sendMessage(jid, { image: buffer, mimetype, caption });
  }

  if (mimetype.startsWith('video/')) {
    return state.sock.sendMessage(jid, { video: buffer, mimetype, caption });
  }

  if (mimetype.startsWith('audio/')) {
    return state.sock.sendMessage(jid, { audio: buffer, mimetype });
  }

  return state.sock.sendMessage(jid, { document: buffer, mimetype, fileName: filename, caption });
}

function getStatus(deviceId = activeDeviceId) {
  const state = getDeviceState(deviceId);
  return {
    ...buildStatus(state),
    activeDeviceId
  };
}

function listDevices() {
  const ids = discoverDeviceIds();
  return ids.map((id) => buildStatus(getDeviceState(id)));
}

async function forceReconnect(deviceId = activeDeviceId) {
  const state = getDeviceState(deviceId);
  if (state.sock) {
    try {
      state.sock.end(new Error('manual-reconnect'));
    } catch (err) {
      logger.error(`[${state.id}] Manual reconnect failed: ${err.message}`);
    }
  }
  state.sock = null;
  await initDevice(state.id, { io: ioInstance });
  return true;
}

async function disconnectDevice(deviceId) {
  const state = getDeviceState(deviceId);
  if (state.sock) {
    try {
      state.sock.end(new Error('manual-disconnect'));
    } catch (err) {
      logger.error(`[${state.id}] Manual disconnect failed: ${err.message}`);
    }
  }
  state.sock = null;
  state.connectionStatus = 'disconnected';
  state.latestQr = null;
  state.latestQrDataUrl = null;
  clearQrRefreshTimer(state);
  return true;
}

async function removeDevice(deviceId) {
  await disconnectDevice(deviceId);
  clearAuthFolder(deviceId);
  devices.delete(deviceId);
  if (activeDeviceId === deviceId) {
    activeDeviceId = env.WA_DEFAULT_DEVICE || 'default';
    writeActiveDevice(activeDeviceId);
  }
  return true;
}

module.exports = {
  initWhatsApp,
  initDevice,
  ensureReady,
  sendText,
  sendFile,
  getStatus,
  listDevices,
  forceReconnect,
  setActiveDevice,
  getActiveDeviceId,
  disconnectDevice,
  removeDevice
};

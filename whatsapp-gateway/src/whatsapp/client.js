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
  jidNormalizedUser,
  WAMessageStatus
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
    initPromise: null,
    messageStatuses: new Map(),
    pendingMessageAcks: new Map()
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

function hasAuthSession(deviceId) {
  const folder = resolveAuthFolder(deviceId);
  const credsPath = path.join(folder, 'creds.json');
  return fs.existsSync(credsPath);
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

function messageStatusName(status) {
  const names = {
    [WAMessageStatus.ERROR]: 'error',
    [WAMessageStatus.PENDING]: 'pending',
    [WAMessageStatus.SERVER_ACK]: 'server_ack',
    [WAMessageStatus.DELIVERY_ACK]: 'delivered',
    [WAMessageStatus.READ]: 'read',
    [WAMessageStatus.PLAYED]: 'played'
  };

  return names[status] || 'unknown';
}

function settlePendingMessageAck(state, messageId, status) {
  if (!messageId || !Number.isInteger(status)) return;

  state.messageStatuses.set(messageId, {
    status,
    updatedAt: Date.now()
  });

  if (state.messageStatuses.size > 500) {
    const oldestId = state.messageStatuses.keys().next().value;
    state.messageStatuses.delete(oldestId);
  }

  const pending = state.pendingMessageAcks.get(messageId);
  if (!pending) return;

  if (status === WAMessageStatus.ERROR) {
    clearTimeout(pending.timer);
    state.pendingMessageAcks.delete(messageId);
    pending.reject(new Error(`WhatsApp rejected message ${messageId}`));
    return;
  }

  if (status >= WAMessageStatus.SERVER_ACK) {
    clearTimeout(pending.timer);
    state.pendingMessageAcks.delete(messageId);
    pending.resolve(status);
  }
}

function rejectPendingMessageAcks(state, reason) {
  state.pendingMessageAcks.forEach((pending) => {
    clearTimeout(pending.timer);
    pending.reject(new Error(reason));
  });
  state.pendingMessageAcks.clear();
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

    state.sock.ev.on('messages.update', (updates) => {
      updates.forEach(({ key, update }) => {
        const status = update?.status;
        if (!key?.fromMe || !key?.id || !Number.isInteger(status)) return;

        logger.info(
          `[${state.id}] Outgoing message ${key.id} status=${messageStatusName(status)} (${status})`
        );
        settlePendingMessageAck(state, key.id, status);
      });
    });

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
        rejectPendingMessageAcks(
          state,
          `WhatsApp disconnected before server acknowledgement on device ${state.id}`
        );
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

async function initAllDevices({ io } = {}) {
  const ids = discoverDeviceIds();
  const withSession = ids.filter((id) => hasAuthSession(id));
  if (withSession.length === 0) {
    return initDevice(activeDeviceId, { io });
  }

  for (const id of withSession) {
    try {
      await initDevice(id, { io });
    } catch (err) {
      logger.error(`[${id}] Auto init failed: ${err.message}`);
    }
  }
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

async function resolveRecipientJid(state, phone) {
  const fallbackJid = toJid(phone);
  const clean = normalizePhone(phone);
  const results = await state.sock.onWhatsApp(clean);
  const recipient = Array.isArray(results)
    ? results.find((item) => item?.exists)
    : null;

  if (!recipient) {
    throw new Error(`WhatsApp number ${clean} is not registered`);
  }

  const jid = jidNormalizedUser(recipient.jid || fallbackJid);
  logger.info(
    `[${state.id}] Recipient ${clean} resolved to ${jid}${recipient.lid ? ` (lid=${recipient.lid})` : ''}`
  );

  return jid;
}

function waitForServerAck(state, result) {
  const messageId = result?.key?.id;
  if (!messageId) {
    return Promise.reject(new Error('WhatsApp did not return a message ID'));
  }

  const initialStatus = Number.isInteger(result?.status)
    ? result.status
    : state.messageStatuses.get(messageId)?.status;

  if (initialStatus === WAMessageStatus.ERROR) {
    return Promise.reject(new Error(`WhatsApp rejected message ${messageId}`));
  }

  if (Number.isInteger(initialStatus) && initialStatus >= WAMessageStatus.SERVER_ACK) {
    return Promise.resolve(initialStatus);
  }

  return new Promise((resolve, reject) => {
    const cachedStatus = state.messageStatuses.get(messageId)?.status;
    if (Number.isInteger(cachedStatus) && cachedStatus >= WAMessageStatus.SERVER_ACK) {
      resolve(cachedStatus);
      return;
    }

    const timer = setTimeout(() => {
      state.pendingMessageAcks.delete(messageId);
      reject(new Error(
        `WhatsApp server acknowledgement timeout after ${env.WA_ACK_TIMEOUT_MS}ms for message ${messageId} on device ${state.id}`
      ));
    }, env.WA_ACK_TIMEOUT_MS);

    state.pendingMessageAcks.set(messageId, { resolve, reject, timer });
  });
}

async function sendConfirmedMessage(state, deviceId, jid, content) {
  const result = await sendWithTimeout(
    deviceId,
    () => state.sock.sendMessage(jid, content)
  );
  const status = await waitForServerAck(state, result);

  return {
    ...result,
    gatewayDeliveryStatus: messageStatusName(status),
    gatewayMessageStatus: status,
    recipientJid: jid
  };
}

async function sendWithTimeout(deviceId, operation) {
  let timeout;
  const timeoutPromise = new Promise((_, reject) => {
    timeout = setTimeout(() => {
      reject(new Error(
        `WhatsApp send timeout after ${env.WA_SEND_TIMEOUT_MS}ms on device ${deviceId}`
      ));
    }, env.WA_SEND_TIMEOUT_MS);
  });

  try {
    return await Promise.race([operation(), timeoutPromise]);
  } catch (err) {
    if (String(err?.message || '').includes('WhatsApp send timeout')) {
      logger.warn(`[${deviceId}] Send timed out. Reconnecting device.`);
      forceReconnect(deviceId).catch((reconnectError) => {
        logger.error(`[${deviceId}] Reconnect after send timeout failed: ${reconnectError.message}`);
      });
    }

    throw err;
  } finally {
    clearTimeout(timeout);
  }
}

async function sendText(phone, message, deviceId = activeDeviceId) {
  await ensureReady(deviceId);
  const state = getDeviceState(deviceId);
  const jid = await resolveRecipientJid(state, phone);
  return sendConfirmedMessage(state, deviceId, jid, { text: message });
}

async function sendFile(phone, filePath, caption, originalName, deviceId = activeDeviceId) {
  await ensureReady(deviceId);
  const state = getDeviceState(deviceId);
  const jid = await resolveRecipientJid(state, phone);
  const buffer = fs.readFileSync(filePath);
  const mimetype = mime.lookup(filePath) || 'application/octet-stream';
  const filename = originalName || path.basename(filePath);

  if (mimetype.startsWith('image/')) {
    return sendConfirmedMessage(
      state,
      deviceId,
      jid,
      { image: buffer, mimetype, caption }
    );
  }

  if (mimetype.startsWith('video/')) {
    return sendConfirmedMessage(
      state,
      deviceId,
      jid,
      { video: buffer, mimetype, caption }
    );
  }

  if (mimetype.startsWith('audio/')) {
    return sendConfirmedMessage(
      state,
      deviceId,
      jid,
      { audio: buffer, mimetype }
    );
  }

  return sendConfirmedMessage(
    state,
    deviceId,
    jid,
    { document: buffer, mimetype, fileName: filename, caption }
  );
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

async function resetAllDevices() {
  const ids = discoverDeviceIds();
  for (const id of ids) {
    await removeDevice(id);
  }
  devices.clear();
  activeDeviceId = env.WA_DEFAULT_DEVICE || 'default';
  writeActiveDevice(activeDeviceId);
  return true;
}

module.exports = {
  initWhatsApp,
  initAllDevices,
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
  removeDevice,
  resetAllDevices
};

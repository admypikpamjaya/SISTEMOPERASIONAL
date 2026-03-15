const { queue } = require('../queue/queue');
const { ok, fail } = require('../utils/response');
const {
  normalizePhone,
  isValidPhone,
  parseNumbers,
  parseCustomList
} = require('../utils/validator');
const {
  getStatus,
  listDevices,
  forceReconnect,
  initDevice,
  setActiveDevice,
  getActiveDeviceId,
  removeDevice,
  disconnectDevice
} = require('../whatsapp/client');

function sanitizeDeviceId(raw) {
  const value = String(raw || '').trim();
  if (!value) return null;
  const normalized = value.toLowerCase().replace(/[^a-z0-9_-]/g, '');
  return normalized || null;
}

function sanitizeNumbers(numbers) {
  return numbers
    .map((value) => normalizePhone(value))
    .filter((value) => isValidPhone(value));
}

async function sendMessage(req, res, next) {
  try {
    const phone = normalizePhone(req.body.phone);
    const message = String(req.body.message || '').trim();
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!isValidPhone(phone) || !message) {
      return fail(res, 'Invalid phone or message');
    }

    const job = await queue.add('send', {
      type: 'text',
      payload: { phone, message, deviceId }
    });

    return ok(res, 'Message queued', { jobId: job.id });
  } catch (err) {
    return next(err);
  }
}

async function sendFile(req, res, next) {
  try {
    const phone = normalizePhone(req.body.phone);
    const caption = String(req.body.caption || '').trim();
    const file = req.file;
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!isValidPhone(phone)) {
      return fail(res, 'Invalid phone');
    }

    if (!file) {
      return fail(res, 'File is required');
    }

    const job = await queue.add('send', {
      type: 'file',
      payload: {
        phone,
        filePath: file.path,
        caption,
        originalName: file.originalname,
        deviceId
      }
    });

    return ok(res, 'File queued', { jobId: job.id });
  } catch (err) {
    return next(err);
  }
}

async function blast(req, res, next) {
  try {
    const numbers = sanitizeNumbers(parseNumbers(req.body.numbers));
    const message = String(req.body.message || '').trim();
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!numbers.length || !message) {
      return fail(res, 'Invalid numbers or message');
    }

    const jobs = numbers.map((phone) => ({
      name: 'send',
      data: {
        type: 'text',
        payload: { phone, message, deviceId }
      }
    }));

    const created = await queue.addBulk(jobs);
    return ok(res, 'Blast queued', { count: created.length, jobIds: created.map((j) => j.id) });
  } catch (err) {
    return next(err);
  }
}

async function blastCustom(req, res, next) {
  try {
    const items = Array.isArray(req.body) ? req.body : parseCustomList(req.body);
    const deviceId = sanitizeDeviceId(req.body.deviceId);
    const sanitized = items
      .map((item) => ({
        phone: normalizePhone(item.phone),
        message: String(item.message || '').trim()
      }))
      .filter((item) => isValidPhone(item.phone) && item.message);

    if (!sanitized.length) {
      return fail(res, 'Invalid payload');
    }

    const jobs = sanitized.map((item) => ({
      name: 'send',
      data: {
        type: 'text',
        payload: { phone: item.phone, message: item.message, deviceId }
      }
    }));

    const created = await queue.addBulk(jobs);
    return ok(res, 'Blast queued', { count: created.length, jobIds: created.map((j) => j.id) });
  } catch (err) {
    return next(err);
  }
}

async function blastFile(req, res, next) {
  try {
    const file = req.file;
    const numbers = sanitizeNumbers(parseNumbers(req.body.numbers || req.body['numbers[]']));
    const caption = String(req.body.caption || '').trim();
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!file) {
      return fail(res, 'File is required');
    }

    if (!numbers.length) {
      return fail(res, 'Invalid numbers');
    }

    const jobs = numbers.map((phone) => ({
      name: 'send',
      data: {
        type: 'file',
        payload: {
          phone,
          filePath: file.path,
          caption,
          originalName: file.originalname,
          deviceId
        }
      }
    }));

    const created = await queue.addBulk(jobs);
    return ok(res, 'Blast file queued', { count: created.length, jobIds: created.map((j) => j.id) });
  } catch (err) {
    return next(err);
  }
}

async function sendTemplate(req, res, next) {
  try {
    const phone = normalizePhone(req.body.phone);
    const template = String(req.body.template || '').trim();
    const variables = req.body.variables || {};
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!isValidPhone(phone) || !template) {
      return fail(res, 'Invalid phone or template');
    }

    const job = await queue.add('send', {
      type: 'template',
      payload: { phone, template, variables, deviceId }
    });

    return ok(res, 'Template queued', { jobId: job.id });
  } catch (err) {
    return next(err);
  }
}

function status(req, res) {
  const deviceId = sanitizeDeviceId(req.query.deviceId || '');
  return ok(res, 'Status', getStatus(deviceId || undefined));
}

async function reconnect(req, res, next) {
  try {
    await forceReconnect();
    return ok(res, 'Reconnecting', {});
  } catch (err) {
    return next(err);
  }
}

function devices(req, res) {
  return ok(res, 'Devices', {
    activeDeviceId: getActiveDeviceId(),
    devices: listDevices()
  });
}

async function createDevice(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.body.deviceId);
    if (!deviceId) {
      return fail(res, 'deviceId is required');
    }
    const existing = listDevices().find((item) => item.deviceId === deviceId);
    if (existing) {
      return fail(res, 'Device ID sudah digunakan. Gunakan ID lain.');
    }
    await initDevice(deviceId);
    return ok(res, 'Device created', getStatus(deviceId));
  } catch (err) {
    return next(err);
  }
}

async function connectDevice(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.params.deviceId);
    if (!deviceId) {
      return fail(res, 'Invalid deviceId');
    }
    await initDevice(deviceId);
    return ok(res, 'Device connected', getStatus(deviceId));
  } catch (err) {
    return next(err);
  }
}

async function activateDevice(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.params.deviceId);
    if (!deviceId) {
      return fail(res, 'Invalid deviceId');
    }
    setActiveDevice(deviceId);
    await initDevice(deviceId);
    return ok(res, 'Device activated', getStatus(deviceId));
  } catch (err) {
    return next(err);
  }
}

async function reconnectDevice(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.params.deviceId);
    if (!deviceId) {
      return fail(res, 'Invalid deviceId');
    }
    await forceReconnect(deviceId);
    return ok(res, 'Reconnecting', getStatus(deviceId));
  } catch (err) {
    return next(err);
  }
}

async function deleteDevice(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.params.deviceId);
    if (!deviceId) {
      return fail(res, 'Invalid deviceId');
    }
    await removeDevice(deviceId);
    return ok(res, 'Device removed', { deviceId });
  } catch (err) {
    return next(err);
  }
}

async function disconnect(req, res, next) {
  try {
    const deviceId = sanitizeDeviceId(req.params.deviceId);
    if (!deviceId) {
      return fail(res, 'Invalid deviceId');
    }
    await disconnectDevice(deviceId);
    return ok(res, 'Device disconnected', { deviceId });
  } catch (err) {
    return next(err);
  }
}

module.exports = {
  sendMessage,
  sendFile,
  blast,
  blastCustom,
  blastFile,
  sendTemplate,
  status,
  reconnect,
  devices,
  createDevice,
  connectDevice,
  activateDevice,
  reconnectDevice,
  deleteDevice,
  disconnect
};

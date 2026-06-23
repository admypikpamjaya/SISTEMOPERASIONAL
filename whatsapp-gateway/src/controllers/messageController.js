const { queue } = require('../queue/queue');
const fs = require('fs/promises');
const env = require('../config/env');
const { processJob } = require('../services/messageService');
const { sleep } = require('../utils/sleep');
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
  disconnectDevice,
  resetAllDevices
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

async function processDirectJob(jobData) {
  const result = await processJob(jobData);

  if (env.MESSAGE_DELAY_MS > 0) {
    await sleep(env.MESSAGE_DELAY_MS);
  }

  return result;
}

async function sendMessage(req, res, next) {
  try {
    const phone = normalizePhone(req.body.phone);
    const message = String(req.body.message || '').trim();
    const deviceId = sanitizeDeviceId(req.body.deviceId);

    if (!isValidPhone(phone) || !message) {
      return fail(res, 'Invalid phone or message');
    }

    const result = await processDirectJob({
      type: 'text',
      payload: { phone, message, deviceId }
    });

    return ok(res, 'Message sent', {
      messageId: result?.key?.id || null,
      deliveryStatus: result?.gatewayDeliveryStatus || 'server_ack',
      messageStatus: result?.gatewayMessageStatus ?? null,
      recipientJid: result?.recipientJid || null,
      deviceId
    });
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

    try {
      const result = await processDirectJob({
        type: 'file',
        payload: {
          phone,
          filePath: file.path,
          caption,
          originalName: file.originalname,
          deviceId
        }
      });

      return ok(res, 'File sent', {
        messageId: result?.key?.id || null,
        deliveryStatus: result?.gatewayDeliveryStatus || 'server_ack',
        messageStatus: result?.gatewayMessageStatus ?? null,
        recipientJid: result?.recipientJid || null,
        deviceId
      });
    } finally {
      await fs.unlink(file.path).catch(() => {});
    }
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

async function serializeJob(jobId) {
  const normalizedJobId = String(jobId || '').trim();
  if (!normalizedJobId) {
    return {
      jobId: normalizedJobId,
      state: 'unknown',
      exists: false
    };
  }

  const job = await queue.getJob(normalizedJobId);
  if (!job) {
    return {
      jobId: normalizedJobId,
      state: 'unknown',
      exists: false
    };
  }

  const state = await job.getState();
  const result = job.returnvalue && typeof job.returnvalue === 'object'
    ? job.returnvalue
    : {};

  return {
    jobId: String(job.id),
    exists: true,
    state,
    messageId: result?.key?.id || result?.messageId || null,
    failedReason: job.failedReason || null,
    attemptsMade: job.attemptsMade || 0,
    timestamp: job.timestamp || null,
    processedOn: job.processedOn || null,
    finishedOn: job.finishedOn || null,
    deviceId: job.data?.payload?.deviceId || null,
    phone: job.data?.payload?.phone || null
  };
}

async function jobStatus(req, res, next) {
  try {
    const job = await serializeJob(req.params.jobId);
    return ok(res, 'Job status', { job });
  } catch (err) {
    return next(err);
  }
}

async function jobsStatus(req, res, next) {
  try {
    const jobIds = Array.isArray(req.body.jobIds)
      ? req.body.jobIds.slice(0, 200)
      : [];

    if (!jobIds.length) {
      return fail(res, 'jobIds is required');
    }

    const jobs = await Promise.all(jobIds.map((jobId) => serializeJob(jobId)));
    return ok(res, 'Jobs status', { jobs });
  } catch (err) {
    return next(err);
  }
}

async function queueStatus(req, res, next) {
  try {
    const counts = await queue.getJobCounts(
      'waiting',
      'active',
      'completed',
      'failed',
      'delayed',
      'paused'
    );

    return ok(res, 'Queue status', {
      deliveryMode: 'direct',
      workerEnabled: env.RUN_WORKER,
      queueName: env.QUEUE_NAME,
      counts
    });
  } catch (err) {
    return next(err);
  }
}

async function clearQueue(req, res, next) {
  try {
    const before = await queue.getJobCounts(
      'waiting',
      'active',
      'completed',
      'failed',
      'delayed',
      'paused'
    );

    await queue.pause();

    try {
      await queue.drain(true);
      await queue.clean(0, 10000, 'completed');
      await queue.clean(0, 10000, 'failed');
    } finally {
      await queue.resume();
    }

    const after = await queue.getJobCounts(
      'waiting',
      'active',
      'completed',
      'failed',
      'delayed',
      'paused'
    );

    return ok(res, 'Queue cleared', {
      deliveryMode: 'direct',
      queueName: env.QUEUE_NAME,
      before,
      after,
      activeRetained: Number(after.active || 0)
    });
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

async function resetDevices(req, res, next) {
  try {
    await resetAllDevices();
    return ok(res, 'Devices reset', {
      activeDeviceId: getActiveDeviceId(),
      devices: listDevices()
    });
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
  jobStatus,
  jobsStatus,
  queueStatus,
  clearQueue,
  status,
  reconnect,
  devices,
  createDevice,
  connectDevice,
  activateDevice,
  reconnectDevice,
  deleteDevice,
  disconnect,
  resetDevices
};

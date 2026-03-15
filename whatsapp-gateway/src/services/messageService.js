const { sendText, sendFile } = require('../whatsapp/client');
const { getTemplate, renderTemplate } = require('./templateService');

async function processJob(jobData) {
  const { type, payload } = jobData;

  if (type === 'text') {
    return sendText(payload.phone, payload.message, payload.deviceId);
  }

  if (type === 'file') {
    return sendFile(
      payload.phone,
      payload.filePath,
      payload.caption,
      payload.originalName,
      payload.deviceId
    );
  }

  if (type === 'template') {
    const template = getTemplate(payload.template);
    if (!template) {
      throw new Error(`Template not found: ${payload.template}`);
    }
    const message = renderTemplate(template, payload.variables || {});
    return sendText(payload.phone, message, payload.deviceId);
  }

  throw new Error(`Unknown job type: ${type}`);
}

module.exports = { processJob };

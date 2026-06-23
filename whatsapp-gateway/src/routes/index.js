const router = require('express').Router();
const controller = require('../controllers/messageController');
const { upload } = require('../utils/upload');
const env = require('../config/env');
const packageInfo = require('../../package.json');

router.get('/health', (req, res) => {
  return res.json({
    success: true,
    message: 'OK',
    data: {
      version: packageInfo.version,
      deliveryMode: 'direct',
      workerEnabled: env.RUN_WORKER,
      queueName: env.QUEUE_NAME,
      gitCommit: process.env.GIT_COMMIT || null
    }
  });
});

router.get('/status', controller.status);
router.post('/reconnect', controller.reconnect);
router.get('/devices', controller.devices);
router.post('/devices', controller.createDevice);
router.post('/devices/:deviceId/connect', controller.connectDevice);
router.post('/devices/:deviceId/activate', controller.activateDevice);
router.post('/devices/:deviceId/reconnect', controller.reconnectDevice);
router.post('/devices/:deviceId/disconnect', controller.disconnect);
router.delete('/devices/:deviceId', controller.deleteDevice);
router.post('/devices/reset', controller.resetDevices);

router.post('/send-message', controller.sendMessage);
router.post('/send-file', upload.single('file'), controller.sendFile);
router.post('/blast', controller.blast);
router.post('/blast-custom', controller.blastCustom);
router.post('/blast-file', upload.single('file'), controller.blastFile);
router.post('/send-template', controller.sendTemplate);
router.get('/jobs/:jobId', controller.jobStatus);
router.post('/jobs/status', controller.jobsStatus);
router.get('/queue/status', controller.queueStatus);
router.post('/queue/clear', controller.clearQueue);

module.exports = router;

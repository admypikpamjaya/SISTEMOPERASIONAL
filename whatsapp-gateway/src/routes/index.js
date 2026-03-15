const router = require('express').Router();
const controller = require('../controllers/messageController');
const { upload } = require('../utils/upload');

router.get('/health', (req, res) => {
  return res.json({ success: true, message: 'OK', data: {} });
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

router.post('/send-message', controller.sendMessage);
router.post('/send-file', upload.single('file'), controller.sendFile);
router.post('/blast', controller.blast);
router.post('/blast-custom', controller.blastCustom);
router.post('/blast-file', upload.single('file'), controller.blastFile);
router.post('/send-template', controller.sendTemplate);

module.exports = router;

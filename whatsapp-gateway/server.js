const http = require('http');
const { Server } = require('socket.io');
const env = require('./src/config/env');
const logger = require('./src/utils/logger');
const app = require('./src/app');
const { initAllDevices } = require('./src/whatsapp/client');
const { startWorker } = require('./src/queue/worker');

const server = http.createServer(app);
let io;

if (env.SOCKET_ENABLED) {
  io = new Server(server, {
    cors: { origin: '*' }
  });

  io.on('connection', (socket) => {
    logger.info(`Socket connected: ${socket.id}`);
  });
}

if (env.RUN_WORKER) {
  initAllDevices({ io }).catch((err) => {
    logger.error(`WhatsApp init failed: ${err.message}`);
  });

  startWorker().catch((err) => {
    logger.error(`Worker failed: ${err.message}`);
  });
} else {
  logger.info('RUN_WORKER=false. API will run without WhatsApp connection.');
}

server.listen(env.PORT, () => {
  logger.info(`Server running on ${env.BASE_URL}`);
});

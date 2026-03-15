const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.join(process.cwd(), '.env') });

const env = {
  NODE_ENV: process.env.NODE_ENV || 'development',
  PORT: parseInt(process.env.PORT || '3000', 10),
  BASE_URL: process.env.BASE_URL || 'http://localhost:3000',
  REDIS_URL: process.env.REDIS_URL || 'redis://127.0.0.1:6379',
  QUEUE_NAME: process.env.QUEUE_NAME || 'wa-messages',
  MESSAGE_DELAY_MS: parseInt(process.env.MESSAGE_DELAY_MS || '1500', 10),
  QUEUE_ATTEMPTS: parseInt(process.env.QUEUE_ATTEMPTS || '3', 10),
  QUEUE_BACKOFF_MS: parseInt(process.env.QUEUE_BACKOFF_MS || '5000', 10),
  WA_AUTH_FOLDER: process.env.WA_AUTH_FOLDER || path.join(process.cwd(), 'sessions'),
  WA_PRINT_QR: process.env.WA_PRINT_QR !== 'false',
  WA_DEFAULT_DEVICE: process.env.WA_DEFAULT_DEVICE || 'default',
  SOCKET_ENABLED: process.env.SOCKET_ENABLED === 'true',
  RUN_WORKER: process.env.RUN_WORKER !== 'false',
  API_KEY: process.env.API_KEY || '',
  API_KEY_HEADER: (process.env.API_KEY_HEADER || 'x-api-key').toLowerCase(),
  QR_TTL_MS: parseInt(process.env.QR_TTL_MS || '35000', 10),
  RECONNECT_DELAY_MS: parseInt(process.env.RECONNECT_DELAY_MS || '5000', 10),
  RATE_LIMIT_WINDOW_MS: parseInt(process.env.RATE_LIMIT_WINDOW_MS || '60000', 10),
  RATE_LIMIT_MAX: parseInt(process.env.RATE_LIMIT_MAX || '120', 10),
  UPLOAD_DIR: process.env.UPLOAD_DIR || path.join(process.cwd(), 'uploads'),
  MAX_FILE_SIZE_MB: parseInt(process.env.MAX_FILE_SIZE_MB || '10', 10)
};

module.exports = env;

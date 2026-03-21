const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.join(process.cwd(), '.env') });

const DEFAULT_QR_TTL_MS = 300000;
const MIN_QR_TTL_MS = 180000;

function parseInteger(value, fallback) {
  const parsed = parseInt(value, 10);
  return Number.isFinite(parsed) ? parsed : fallback;
}

const env = {
  NODE_ENV: process.env.NODE_ENV || 'development',
  PORT: parseInteger(process.env.PORT, 3000),
  BASE_URL: process.env.BASE_URL || 'http://localhost:3000',
  REDIS_URL: process.env.REDIS_URL || 'redis://127.0.0.1:6379',
  QUEUE_NAME: process.env.QUEUE_NAME || 'wa-messages',
  MESSAGE_DELAY_MS: parseInteger(process.env.MESSAGE_DELAY_MS, 1500),
  QUEUE_ATTEMPTS: parseInteger(process.env.QUEUE_ATTEMPTS, 3),
  QUEUE_BACKOFF_MS: parseInteger(process.env.QUEUE_BACKOFF_MS, 5000),
  WA_AUTH_FOLDER: process.env.WA_AUTH_FOLDER || path.join(process.cwd(), 'sessions'),
  WA_PRINT_QR: process.env.WA_PRINT_QR !== 'false',
  WA_DEFAULT_DEVICE: process.env.WA_DEFAULT_DEVICE || 'default',
  SOCKET_ENABLED: process.env.SOCKET_ENABLED === 'true',
  RUN_WORKER: process.env.RUN_WORKER !== 'false',
  API_KEY: process.env.API_KEY || '',
  API_KEY_HEADER: (process.env.API_KEY_HEADER || 'x-api-key').toLowerCase(),
  QR_TTL_MS: Math.max(MIN_QR_TTL_MS, parseInteger(process.env.QR_TTL_MS, DEFAULT_QR_TTL_MS)),
  RECONNECT_DELAY_MS: parseInteger(process.env.RECONNECT_DELAY_MS, 5000),
  RATE_LIMIT_WINDOW_MS: parseInteger(process.env.RATE_LIMIT_WINDOW_MS, 60000),
  RATE_LIMIT_MAX: parseInteger(process.env.RATE_LIMIT_MAX, 120),
  UPLOAD_DIR: process.env.UPLOAD_DIR || path.join(process.cwd(), 'uploads'),
  MAX_FILE_SIZE_MB: parseInteger(process.env.MAX_FILE_SIZE_MB, 10)
};

module.exports = env;

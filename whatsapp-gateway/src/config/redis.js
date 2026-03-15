const IORedis = require('ioredis');
const env = require('./env');
const logger = require('../utils/logger');

const connection = new IORedis(env.REDIS_URL, {
  maxRetriesPerRequest: null
});

connection.on('error', (err) => {
  logger.error(`Redis error: ${err.message}`);
});

module.exports = { connection };

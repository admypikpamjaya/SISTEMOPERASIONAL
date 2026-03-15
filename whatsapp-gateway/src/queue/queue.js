const { Queue } = require('bullmq');
const env = require('../config/env');
const { connection } = require('../config/redis');

const queue = new Queue(env.QUEUE_NAME, {
  connection,
  defaultJobOptions: {
    attempts: env.QUEUE_ATTEMPTS,
    backoff: {
      type: 'exponential',
      delay: env.QUEUE_BACKOFF_MS
    },
    removeOnComplete: 1000,
    removeOnFail: 1000
  }
});

module.exports = {
  queue,
  queueName: env.QUEUE_NAME,
  connection
};

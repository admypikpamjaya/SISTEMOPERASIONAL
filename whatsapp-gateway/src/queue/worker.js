const { Worker } = require('bullmq');
const env = require('../config/env');
const logger = require('../utils/logger');
const { connection, queueName } = require('./queue');
const { processJob } = require('../services/messageService');
const { sleep } = require('../utils/sleep');
const { initWhatsApp } = require('../whatsapp/client');

let worker;

async function startWorker() {
  if (worker) return worker;

  await initWhatsApp();

  worker = new Worker(
    queueName,
    async (job) => {
      logger.info(`Processing job ${job.id} (${job.data.type})`);
      await processJob(job.data);

      if (env.MESSAGE_DELAY_MS > 0) {
        await sleep(env.MESSAGE_DELAY_MS);
      }
    },
    {
      connection,
      concurrency: 1
    }
  );

  worker.on('completed', (job) => {
    logger.info(`Job ${job.id} completed`);
  });

  worker.on('failed', (job, err) => {
    logger.error(`Job ${job?.id} failed: ${err.message}`);
  });

  logger.info('Queue worker started');
  return worker;
}

if (require.main === module) {
  startWorker().catch((err) => {
    logger.error(`Worker failed to start: ${err.message}`);
    process.exit(1);
  });
}

module.exports = { startWorker };

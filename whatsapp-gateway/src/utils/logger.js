const path = require('path');
const { createLogger, format, transports } = require('winston');
const env = require('../config/env');

const logFormat = format.printf(({ level, message, timestamp, stack }) => {
  return `${timestamp} [${level}]: ${stack || message}`;
});

const logger = createLogger({
  level: env.NODE_ENV === 'production' ? 'info' : 'debug',
  format: format.combine(format.timestamp(), format.errors({ stack: true }), logFormat),
  transports: [
    new transports.Console(),
    new transports.File({ filename: path.join(process.cwd(), 'logs', 'error.log'), level: 'error' }),
    new transports.File({ filename: path.join(process.cwd(), 'logs', 'combined.log') })
  ]
});

module.exports = logger;

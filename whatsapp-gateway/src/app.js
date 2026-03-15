const express = require('express');
const cors = require('cors');
const rateLimit = require('express-rate-limit');
const env = require('./config/env');
const routes = require('./routes');
const logger = require('./utils/logger');
const { notFound, errorHandler } = require('./utils/errorHandler');

const app = express();

app.use(cors());
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

const limiter = rateLimit({
  windowMs: env.RATE_LIMIT_WINDOW_MS,
  max: env.RATE_LIMIT_MAX,
  standardHeaders: true,
  legacyHeaders: false
});

app.use(limiter);

if (env.API_KEY) {
  app.use((req, res, next) => {
    if (req.path === '/health') {
      return next();
    }

    const provided = req.headers[env.API_KEY_HEADER];
    if (!provided || provided !== env.API_KEY) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized',
        data: {}
      });
    }

    return next();
  });
}

app.use((req, res, next) => {
  const start = Date.now();
  res.on('finish', () => {
    const duration = Date.now() - start;
    logger.info(`${req.method} ${req.originalUrl} ${res.statusCode} ${duration}ms`);
  });
  next();
});

app.use('/', routes);

app.use(notFound);
app.use(errorHandler);

module.exports = app;

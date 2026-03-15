const logger = require('./logger');

function notFound(req, res) {
  return res.status(404).json({
    success: false,
    message: 'Route not found',
    data: {}
  });
}

function errorHandler(err, req, res, next) {
  logger.error(err.stack || err.message || 'Unknown error');
  const status = err.status || 500;
  return res.status(status).json({
    success: false,
    message: err.message || 'Internal server error',
    data: {}
  });
}

module.exports = { notFound, errorHandler };

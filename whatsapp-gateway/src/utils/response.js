function ok(res, message, data = {}) {
  return res.json({
    success: true,
    message,
    data
  });
}

function fail(res, message, status = 400, data = {}) {
  return res.status(status).json({
    success: false,
    message,
    data
  });
}

module.exports = { ok, fail };

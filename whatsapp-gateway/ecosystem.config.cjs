module.exports = {
  apps: [
    {
      name: 'wa-gateway',
      script: 'server.js',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '300M',
      time: true,
      merge_logs: true,
      env: {
        NODE_ENV: 'production',
        PORT: 3000,
        RUN_WORKER: 'true',
        WA_PRINT_QR: 'true'
      }
    }
  ]
};

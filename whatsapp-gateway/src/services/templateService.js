const fs = require('fs');
const path = require('path');

const templatesPath = path.join(__dirname, '..', 'config', 'templates.json');
let cache = null;

function loadTemplates() {
  if (!cache) {
    const raw = fs.readFileSync(templatesPath, 'utf8');
    cache = JSON.parse(raw);
  }
  return cache;
}

function getTemplate(name) {
  const templates = loadTemplates();
  return templates[name];
}

function renderTemplate(template, variables = {}) {
  return template.replace(/{{\s*([\w.]+)\s*}}/g, (match, key) => {
    const value = key.split('.').reduce((acc, part) => (acc ? acc[part] : undefined), variables);
    return value === undefined || value === null ? '' : String(value);
  });
}

module.exports = {
  getTemplate,
  renderTemplate
};

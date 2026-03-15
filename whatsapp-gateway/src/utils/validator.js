function normalizePhone(value) {
  return String(value || '').replace(/\D/g, '');
}

function isValidPhone(phone) {
  return phone.length >= 8 && phone.length <= 15;
}

function parseNumbers(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (!trimmed) return [];
    if (trimmed.startsWith('[')) {
      try {
        const parsed = JSON.parse(trimmed);
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return [];
      }
    }
    if (trimmed.includes(',')) {
      return trimmed.split(',').map((item) => item.trim()).filter(Boolean);
    }
    return [trimmed];
  }
  return [];
}

function parseCustomList(value) {
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
}

module.exports = {
  normalizePhone,
  isValidPhone,
  parseNumbers,
  parseCustomList
};

let rows = [];
let selectedFile = null;
const requiredColumns = ['first_name', 'last_name', 'email', 'university_id'];
const csvInput = document.querySelector('#csv');
const dropzone = document.querySelector('#dropzone');
const filePrompt = document.querySelector('#filePrompt');
const fileSummary = document.querySelector('#fileSummary');
const fileName = document.querySelector('#fileName');
const fileSize = document.querySelector('#fileSize');
const removeFileButton = document.querySelector('#removeFile');
const uploadError = document.querySelector('#uploadError');
const validateButton = document.querySelector('#validate');
const backButton = document.querySelector('#back');
const confirmButton = document.querySelector('#confirm');
const importAgainButton = document.querySelector('#again');
const previewBody = document.querySelector('#previewBody');
const validationSummary = document.querySelector('#summary');
const completeCopy = document.querySelector('#completeCopy');

document.querySelectorAll('[data-select]').forEach(select => {
  const trigger = select.querySelector('.select-trigger');
  const menu = select.querySelector('.select-menu');
  trigger.addEventListener('click', () => {
    menu.hidden = !menu.hidden;
    trigger.setAttribute('aria-expanded', String(!menu.hidden));
  });
  menu.querySelectorAll('button').forEach(option => option.addEventListener('click', () => {
    select.querySelector('input').value = option.dataset.value;
    trigger.querySelector('span').textContent = option.textContent;
    menu.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');
  }));
});

function showError(message) {
  uploadError.textContent = message;
  uploadError.hidden = false;
}
function acceptFile(file) {
  uploadError.hidden = true;
  if (!file || !file.name.toLowerCase().endsWith('.csv')) {
    showError('Choose a CSV file.');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    showError('The CSV must be smaller than 5 MB.');
    return;
  }
  selectedFile = file;
  fileName.textContent = file.name;
  fileSize.textContent = `${Math.max(1, Math.round(file.size / 1024))} KB`;
  filePrompt.hidden = true;
  fileSummary.hidden = false;
  validateButton.disabled = false;
}
function removeFile() {
  selectedFile = null;
  csvInput.value = '';
  filePrompt.hidden = false;
  fileSummary.hidden = true;
  validateButton.disabled = true;
  uploadError.hidden = true;
}
function showStep(number) {
  document.querySelectorAll('[data-step]').forEach(step => { step.hidden = Number(step.dataset.step) !== number; });
  document.querySelectorAll('[data-indicator]').forEach(indicator => {
    const value = Number(indicator.dataset.indicator);
    indicator.classList.toggle('active', value === number);
    indicator.classList.toggle('done', value < number);
  });
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
function parseCsvLine(line) {
  const values = [];
  let value = '';
  let quoted = false;
  for (let index = 0; index < line.length; index += 1) {
    const character = line[index];
    if (character === '"' && line[index + 1] === '"' && quoted) { value += '"'; index += 1; }
    else if (character === '"') quoted = !quoted;
    else if (character === ',' && !quoted) { values.push(value.trim()); value = ''; }
    else value += character;
  }
  values.push(value.trim());
  return values;
}

csvInput.addEventListener('change', () => acceptFile(csvInput.files[0]));
removeFileButton.addEventListener('click', removeFile);
['dragenter', 'dragover'].forEach(eventName => dropzone.addEventListener(eventName, event => {
  event.preventDefault();
  dropzone.classList.add('drag');
}));
['dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, event => {
  event.preventDefault();
  dropzone.classList.remove('drag');
}));
dropzone.addEventListener('drop', event => acceptFile(event.dataTransfer.files[0]));

validateButton.addEventListener('click', () => {
  if (!selectedFile) { showError('Choose a CSV file first.'); return; }
  const reader = new FileReader();
  reader.addEventListener('load', () => {
    const lines = String(reader.result).trim().split(/\r?\n/);
    const headers = parseCsvLine(lines.shift()).map(header => header.toLowerCase());
    const missing = requiredColumns.filter(column => !headers.includes(column));
    if (missing.length) { showError(`Missing required columns: ${missing.join(', ')}.`); return; }
    rows = lines.filter(Boolean).map(line => {
      const values = parseCsvLine(line);
      return Object.fromEntries(headers.map((header, index) => [header, values[index] || '']));
    });
    if (!rows.length) { showError('The CSV contains no user rows.'); return; }
    previewBody.innerHTML = rows.slice(0, 8).map((row, index) => {
      const validEmail = /^[^\s@]+@university\.edu$/i.test(row.email);
      return `<tr><td>${index + 2}</td><td>${row.first_name} ${row.last_name}</td><td>${row.email}</td><td><span class="badge ${validEmail ? 'success' : 'danger'}">${validEmail ? 'Ready' : 'Invalid university email'}</span></td></tr>`;
    }).join('');
    validationSummary.textContent = `${rows.length} row${rows.length === 1 ? '' : 's'} found. Review before confirming.`;
    showStep(2);
  });
  reader.readAsText(selectedFile);
});

backButton.addEventListener('click', () => showStep(1));
confirmButton.addEventListener('click', () => {
  completeCopy.textContent = `${rows.length} account${rows.length === 1 ? '' : 's'} processed successfully.`;
  showStep(3);
});
importAgainButton.addEventListener('click', () => {
  removeFile();
  rows = [];
  previewBody.innerHTML = '';
  showStep(1);
});

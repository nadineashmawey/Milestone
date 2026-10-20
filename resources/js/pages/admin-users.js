const users = [
  { initials: 'AH', name: 'Aya Hassan', email: 'aya.hassan@university.edu', role: 'Student', status: 'Active', active: 'Today' },
  { initials: 'PL', name: 'Professor Lee', email: 'lee@university.edu', role: 'Professor', status: 'Active', active: 'Today' },
  { initials: 'SA', name: 'Sara Ahmed', email: 'sara.ahmed@university.edu', role: 'TA', status: 'Active', active: 'Today' },
  { initials: 'DL', name: 'Dr. Lina Haddad', email: 'lina.haddad@university.edu', role: 'Student', status: 'Pending role', active: 'Never' },
  { initials: 'NK', name: 'Nadia Karim', email: 'nadia.karim@university.edu', role: 'Student', status: 'Deactivated', active: 'Sep 28' },
];
const body = document.querySelector('#usersBody');
const toast = document.querySelector('#toast');
const toastMessage = document.querySelector('#toastMessage');
let filter = 'all';

function visibleUsers() {
  return users.map((user, index) => ({ user, index })).filter(({ user }) => filter === 'all' || user.role.toLowerCase() === filter);
}
function showToast(message) {
  toastMessage.textContent = message;
  toast.hidden = false;
  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(() => { toast.hidden = true; }, 3000);
}
function selectedIndexes() {
  return [...document.querySelectorAll('.user-check:checked')].map(box => Number(box.value));
}
function updateSelection() {
  const count = selectedIndexes().length;
  document.querySelector('#selectionCount').textContent = `${count} selected`;
  document.querySelector('#assignProfessor').disabled = count === 0;
  document.querySelector('#assignTa').disabled = count === 0;
}
function draw() {
  body.innerHTML = visibleUsers().map(({ user, index }) => `<tr data-user-index="${index}">
    <td><input type="checkbox" class="user-check" value="${index}" aria-label="Select ${user.name}"></td>
    <td><div class="person"><span class="avatar">${user.initials}</span><div><strong>${user.name}</strong><span>${user.email}</span></div></div></td>
    <td><span class="badge info">${user.role}</span></td>
    <td><span class="badge ${user.status === 'Active' ? 'success' : 'warning'}">${user.status}</span></td>
    <td>${user.active}</td>
    <td><button type="button" class="row-action" data-toggle-status="${index}">${user.status === 'Deactivated' ? 'Activate' : 'Deactivate'}</button></td>
  </tr>`).join('');
  document.querySelectorAll('.user-check').forEach(box => box.addEventListener('change', updateSelection));
  document.querySelectorAll('[data-toggle-status]').forEach(button => button.addEventListener('click', () => toggleStatus(Number(button.dataset.toggleStatus))));
  updateSelection();
}
function assignRole(role) {
  const selected = selectedIndexes();
  if (!selected.length) return;
  selected.forEach(index => {
    users[index].role = role;
    if (users[index].status === 'Pending role') users[index].status = 'Active';
  });
  draw();
  showToast(`${selected.length} user${selected.length === 1 ? '' : 's'} assigned the ${role} role.`);
}
function toggleStatus(index) {
  const user = users[index];
  const activating = user.status === 'Deactivated';
  user.status = activating ? 'Active' : 'Deactivated';
  if (activating) user.active = 'Today';
  draw();
  showToast(`${user.name} was ${activating ? 'activated' : 'deactivated'}.`);
}

document.querySelector('#assignProfessor').addEventListener('click', () => assignRole('Professor'));
document.querySelector('#assignTa').addEventListener('click', () => assignRole('TA'));
document.querySelector('#closeToast').addEventListener('click', () => { toast.hidden = true; });
document.querySelectorAll('[data-tab]').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('[data-tab]').forEach(tab => tab.setAttribute('aria-selected', String(tab === button)));
  document.querySelectorAll('[data-panel]').forEach(panel => { panel.hidden = panel.dataset.panel !== button.dataset.tab; });
}));
document.querySelectorAll('[data-select]').forEach(select => {
  const trigger = select.querySelector('.select-trigger');
  const menu = select.querySelector('.select-menu');
  trigger.addEventListener('click', () => {
    menu.hidden = !menu.hidden;
    trigger.setAttribute('aria-expanded', String(!menu.hidden));
  });
  menu.querySelectorAll('button').forEach(option => option.addEventListener('click', () => {
    trigger.querySelector('span').textContent = option.textContent;
    menu.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');
    menu.querySelectorAll('button').forEach(item => item.setAttribute('aria-selected', String(item === option)));
    const input = select.querySelector('input');
    if (input) input.value = option.dataset.value;
    else { filter = option.dataset.value; draw(); }
  }));
});
const drawer = document.querySelector('#drawer');
const overlay = document.querySelector('#overlay');
function toggleDrawer(open) {
  drawer.classList.toggle('open', open);
  drawer.setAttribute('aria-hidden', String(!open));
  overlay.hidden = !open;
}
document.querySelector('#addUser').addEventListener('click', () => toggleDrawer(true));
document.querySelector('#closeDrawer').addEventListener('click', () => toggleDrawer(false));
document.querySelector('#cancelDrawer').addEventListener('click', () => toggleDrawer(false));
overlay.addEventListener('click', () => toggleDrawer(false));

const reviewDrawer = document.querySelector('#reviewDrawer');
const reviewOverlay = document.querySelector('#reviewOverlay');
function toggleReview(open) {
  reviewDrawer.classList.toggle('open', open);
  reviewDrawer.setAttribute('aria-hidden', String(!open));
  reviewOverlay.hidden = !open;
}
function resolvePending(approved) {
  if (approved) {
    const user = users.find(item => item.email === 'lina.haddad@university.edu');
    user.role = 'Professor';
    user.status = 'Active';
    user.active = 'Today';
  }
  document.querySelector('#pendingCount').textContent = '0';
  document.querySelector('#pendingSummary').textContent = 'No accounts awaiting approval';
  document.querySelector('#pendingTable').hidden = true;
  document.querySelector('#pendingEmpty').hidden = false;
  toggleReview(false);
  draw();
  showToast(approved ? 'Dr. Lina Haddad was approved as Professor.' : 'Dr. Lina Haddad’s role request was rejected.');
}
document.querySelector('#reviewPending').addEventListener('click', () => toggleReview(true));
document.querySelector('#closeReview').addEventListener('click', () => toggleReview(false));
reviewOverlay.addEventListener('click', () => toggleReview(false));
document.querySelector('#approvePending').addEventListener('click', () => resolvePending(true));
document.querySelector('#rejectPending').addEventListener('click', () => resolvePending(false));

const dataRequestDrawer = document.querySelector('#dataRequestDrawer');
const dataRequestOverlay = document.querySelector('#dataRequestOverlay');
const requestRow = document.querySelector('[data-request-row]');
function toggleDataRequest(open) {
  dataRequestDrawer.classList.toggle('open', open);
  dataRequestDrawer.setAttribute('aria-hidden', String(!open));
  dataRequestOverlay.hidden = !open;
}
function resolveDataRequest(status) {
  requestRow.dataset.status = status.toLowerCase();
  const badge = requestRow.querySelector('[data-request-badge]');
  badge.textContent = status;
  badge.className = `badge ${status === 'Approved' ? 'success' : 'danger'}`;
  const action = requestRow.querySelector('#reviewDataRequest');
  action.textContent = 'Reviewed';
  action.disabled = true;
  document.querySelector('[data-tab="requests"] span').textContent = '0';
  toggleDataRequest(false);
  showToast(`Aya Hassan’s data request was ${status.toLowerCase()}.`);
}
document.querySelector('#reviewDataRequest').addEventListener('click', () => toggleDataRequest(true));
document.querySelector('#closeDataRequest').addEventListener('click', () => toggleDataRequest(false));
dataRequestOverlay.addEventListener('click', () => toggleDataRequest(false));
document.querySelector('#approveDataRequest').addEventListener('click', () => resolveDataRequest('Approved'));
document.querySelector('#rejectDataRequest').addEventListener('click', () => resolveDataRequest('Rejected'));

const requestStatusSelect = document.querySelector('#requestStatusSelect');
const requestStatusTrigger = requestStatusSelect.querySelector('.select-trigger');
const requestStatusMenu = requestStatusSelect.querySelector('.select-menu');
requestStatusTrigger.addEventListener('click', () => {
  requestStatusMenu.hidden = !requestStatusMenu.hidden;
  requestStatusTrigger.setAttribute('aria-expanded', String(!requestStatusMenu.hidden));
});
requestStatusMenu.querySelectorAll('button').forEach(option => option.addEventListener('click', () => {
  const status = option.dataset.requestStatus;
  requestStatusTrigger.querySelector('span').textContent = option.textContent;
  requestStatusMenu.hidden = true;
  requestStatusTrigger.setAttribute('aria-expanded', 'false');
  requestStatusMenu.querySelectorAll('button').forEach(item => item.setAttribute('aria-selected', String(item === option)));
  const visible = status === 'all' || requestRow.dataset.status === status;
  requestRow.hidden = !visible;
  document.querySelector('#requestsEmpty').hidden = visible;
}));

document.querySelector('#userForm').addEventListener('submit', event => {
  event.preventDefault();
  const rules = {
    first: value => value.trim() ? '' : 'First name is required.',
    last: value => value.trim() ? '' : 'Last name is required.',
    email: value => /^[^\s@]+@university\.edu$/i.test(value) ? '' : 'Enter a valid university email ending in @university.edu.',
  };
  let valid = true;
  Object.entries(rules).forEach(([id, validate]) => {
    const input = document.querySelector(`#${id}`);
    const error = document.querySelector(`[data-error="${id}"]`);
    const message = validate(input.value);
    error.textContent = message;
    error.hidden = !message;
    input.classList.toggle('invalid', Boolean(message));
    if (message) valid = false;
  });
  if (!valid) return;
  toggleDrawer(false);
  document.querySelector('#userForm').reset();
  showToast('User added successfully.');
});
draw();

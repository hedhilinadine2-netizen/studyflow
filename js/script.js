/**
 * StudyFlow — script.js (v2 — all upgrades applied)
 */

const API_URL = 'api/tasks.php';
let isSubmitting = false;

/* ═══════════════════════════════════════
   TOAST SYSTEM
═══════════════════════════════════════ */
function showToast(message, type = 'info', duration = 4000, actions = []) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position:fixed; bottom:24px; right:24px;
            display:flex; flex-direction:column; gap:10px;
            z-index:9999; max-width:360px;
        `;
        document.body.appendChild(container);
    }

    const colors = {
        info:    { bg:'#1e40af', border:'#3b82f6', icon:'ℹ️' },
        warning: { bg:'#92400e', border:'#f59e0b', icon:'⚠️' },
        success: { bg:'#065f46', border:'#10b981', icon:'✅' },
        error:   { bg:'#7f1d1d', border:'#ef4444', icon:'❌' },
    };
    const s = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
        background:${s.bg}; border-left:4px solid ${s.border};
        color:#fff; padding:12px 16px; border-radius:8px;
        font-family:'Segoe UI',sans-serif; font-size:14px; line-height:1.5;
        box-shadow:0 4px 20px rgba(0,0,0,0.35);
        opacity:0; transform:translateX(40px);
        transition:opacity 0.3s ease,transform 0.3s ease;
        display:flex; align-items:flex-start; gap:10px;
    `;

    let actionHTML = '';
    if (actions.length) {
        actionHTML = `<div style="margin-top:8px;display:flex;gap:8px;">` +
            actions.map(a => `<button data-action="${a.label}" style="
                background:rgba(255,255,255,0.2);border:none;color:#fff;
                padding:4px 10px;border-radius:5px;cursor:pointer;font-size:12px;
            ">${a.label}</button>`).join('') + `</div>`;
    }

    toast.innerHTML = `
        <span style="font-size:18px">${s.icon}</span>
        <div style="flex:1"><span>${message}</span>${actionHTML}</div>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }));

    const dismiss = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        setTimeout(() => toast.remove(), 320);
    };

    actions.forEach(a => {
        toast.querySelector(`[data-action="${a.label}"]`)
            ?.addEventListener('click', () => { a.fn(); dismiss(); });
    });

    const timer = setTimeout(dismiss, duration);
    toast.addEventListener('click', (e) => {
        if (!e.target.dataset.action) { clearTimeout(timer); dismiss(); }
    });

    return dismiss;
}

/* ═══════════════════════════════════════
   SKELETON LOADER
═══════════════════════════════════════ */
function showSkeleton() {
    const list = document.getElementById('task-list');
    if (!list) return;
    if (!document.getElementById('pulse-style')) {
        const st = document.createElement('style');
        st.id = 'pulse-style';
        st.textContent = `@keyframes pulse{0%,100%{opacity:1}50%{opacity:.45}}`;
        document.head.appendChild(st);
    }
    list.innerHTML = [1,2,3].map(() => `
        <li style="display:flex;justify-content:space-between;align-items:center;
            padding:14px;border-radius:8px;margin-bottom:8px;
            background:#f3f4f6;animation:pulse 1.4s ease-in-out infinite;">
            <div>
                <div style="height:14px;width:190px;background:#e5e7eb;border-radius:4px;margin-bottom:7px;"></div>
                <div style="height:11px;width:110px;background:#e5e7eb;border-radius:4px;"></div>
            </div>
            <div style="height:28px;width:110px;background:#e5e7eb;border-radius:6px;"></div>
        </li>`).join('');
}

/* ═══════════════════════════════════════
   STATS BAR
═══════════════════════════════════════ */
function renderStats(tasks) {
    let bar = document.getElementById('task-stats');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'task-stats';
        bar.style.cssText = `display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;`;
        const list = document.getElementById('task-list');
        list?.parentNode?.insertBefore(bar, list);
    }

    const total  = tasks.length;
    const done   = tasks.filter(t => t.status === 'done').length;
    const urgent = tasks.filter(t => {
        if (t.status === 'done') return false;
        const diff = new Date(t.due_date) - new Date();
        return diff >= 0 && diff <= 2 * 86400000;
    }).length;

    const stat = (label, value, color) => `
        <div style="background:#f9fafb;border:0.5px solid #e5e7eb;border-radius:8px;
            padding:8px 16px;text-align:center;flex:1;min-width:70px;">
            <div style="font-size:22px;font-weight:500;color:${color}">${value}</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">${label}</div>
        </div>`;

    bar.innerHTML =
        stat('Total',  total,  '#374151') +
        stat('Done',   done,   '#059669') +
        stat('Urgent', urgent, urgent > 0 ? '#dc2626' : '#374151');
}

/* ═══════════════════════════════════════
   DEADLINE CHECK — once per session per task
═══════════════════════════════════════ */
function checkDeadlines(tasks) {
    const fired = JSON.parse(sessionStorage.getItem('sf_alerted') || '[]');
    const now   = new Date();

    tasks.forEach((task, i) => {
        if (task.status === 'done') return;
        if (fired.includes(String(task.id))) return;

        const due  = new Date(task.due_date);
        const diff = due - now;
        if (diff < 0 || diff > 2 * 86400000) return;

        const hours = Math.floor(diff / 3600000);
        const label = hours < 1  ? 'Due in less than 1 hour!'
                    : hours < 24 ? `Due in ${hours} hour${hours !== 1 ? 's' : ''}!`
                    : hours < 48 ? 'Due tomorrow!'
                    :              'Due in 2 days!';

        setTimeout(() => {
            showToast(`${label} — <strong>${task.title}</strong>`, 'warning', 7000);
        }, i * 700);

        fired.push(String(task.id));
    });

    sessionStorage.setItem('sf_alerted', JSON.stringify(fired));
}

/* ═══════════════════════════════════════
   API
═══════════════════════════════════════ */
async function apiFetch(url, method = 'GET', body = null) {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) options.body = JSON.stringify(body);
    const res  = await fetch(url, options);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
    return data;
}

/* ═══════════════════════════════════════
   RENDER
═══════════════════════════════════════ */
function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderTasks(tasks) {
    renderStats(tasks);
    const list = document.getElementById('task-list');
    if (!list) return;

    if (tasks.length === 0) {
        list.innerHTML = `<li style="text-align:center;padding:40px 20px;color:#9ca3af;font-size:14px;">
            No tasks yet. Add one below!</li>`;
        return;
    }

    const allDone = tasks.every(t => t.status === 'done');
    if (allDone) {
        list.innerHTML = `<li style="text-align:center;padding:40px 20px;">
            <div style="font-size:40px;margin-bottom:10px">🎉</div>
            <strong style="font-size:16px">All tasks done!</strong><br>
            <span style="color:#9ca3af;font-size:13px">You crushed it. Time to relax.</span>
        </li>`;
        return;
    }

    list.innerHTML = tasks.map(task => {
        const due      = new Date(task.due_date);
        const dateStr  = due.toLocaleDateString('fr-TN', { day:'2-digit', month:'short', year:'numeric' });
        const diff     = due - new Date();
        const isUrgent = task.status !== 'done' && diff >= 0 && diff <= 2 * 86400000;

        return `
        <li class="task-item" data-id="${task.id}" style="
            display:flex;justify-content:space-between;align-items:center;
            padding:12px 14px;border-radius:8px;margin-bottom:8px;
            background:#f9fafb;
            border:0.5px solid #e5e7eb;
            ${isUrgent ? 'border-left:3px solid #f59e0b;' : ''}
            transition:background 0.4s;
        ">
            <div>
                <div style="font-size:14px;font-weight:500;color:#111;
                    ${task.status==='done' ? 'text-decoration:line-through;opacity:0.45;' : ''}">
                    ${escapeHtml(task.title)}
                </div>
                <div style="font-size:12px;color:#9ca3af;margin-top:3px">
                    📅 ${dateStr}
                    ${isUrgent ? ' — <span style="color:#d97706;font-weight:500">Urgent</span>' : ''}
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                <select class="status-select" data-id="${task.id}" style="
                    font-size:12px;padding:4px 8px;border-radius:6px;
                    border:0.5px solid #d1d5db;background:#fff;cursor:pointer;">
                    <option value="pending"     ${task.status==='pending'     ?'selected':''}>Pending</option>
                    <option value="in_progress" ${task.status==='in_progress' ?'selected':''}>In Progress</option>
                    <option value="done"        ${task.status==='done'        ?'selected':''}>Done ✅</option>
                </select>
                <button class="btn-edit" data-id="${task.id}"
                    data-title="${escapeHtml(task.title)}" data-date="${task.due_date}"
                    style="background:none;border:none;cursor:pointer;font-size:15px;padding:4px;">✏️</button>
                <button class="btn-delete" data-id="${task.id}" data-title="${escapeHtml(task.title)}"
                    style="background:none;border:none;cursor:pointer;font-size:15px;padding:4px;">🗑️</button>
            </div>
        </li>`;
    }).join('');

    attachListeners();
}

/* ═══════════════════════════════════════
   EDIT MODAL
═══════════════════════════════════════ */
function openEditModal(id, currentTitle, currentDate) {
    document.getElementById('sf-modal')?.remove();

    const overlay = document.createElement('div');
    overlay.id = 'sf-modal';
    overlay.style.cssText = `
        position:fixed;inset:0;background:rgba(0,0,0,0.45);
        display:flex;align-items:center;justify-content:center;z-index:10000;
    `;
    overlay.innerHTML = `
        <div style="background:#fff;border-radius:12px;padding:24px;
            width:90%;max-width:380px;font-family:'Segoe UI',sans-serif;">
            <div style="font-size:16px;font-weight:500;margin-bottom:18px;color:#111">Edit task</div>
            <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:4px">Title</label>
            <input id="modal-title" value="${currentTitle}" style="
                width:100%;box-sizing:border-box;padding:9px 12px;
                border:1px solid #d1d5db;border-radius:7px;font-size:14px;margin-bottom:14px;"/>
            <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:4px">Due date</label>
            <input id="modal-date" type="date" value="${currentDate}" style="
                width:100%;box-sizing:border-box;padding:9px 12px;
                border:1px solid #d1d5db;border-radius:7px;font-size:14px;margin-bottom:20px;"/>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button id="modal-cancel" style="
                    padding:8px 16px;border:1px solid #d1d5db;border-radius:7px;
                    background:#fff;cursor:pointer;font-size:13px;color:#374151;">Cancel</button>
                <button id="modal-save" style="
                    padding:8px 16px;border:none;border-radius:7px;
                    background:#1d4ed8;color:#fff;cursor:pointer;font-size:13px;font-weight:500;">Save changes</button>
            </div>
        </div>`;

    document.body.appendChild(overlay);
    document.getElementById('modal-title').focus();

    const close = () => overlay.remove();
    document.getElementById('modal-cancel').addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });

    document.getElementById('modal-save').addEventListener('click', async () => {
        const title   = document.getElementById('modal-title').value.trim();
        const dueDate = document.getElementById('modal-date').value.trim();
        if (!title || !dueDate) { showToast('Fill in all fields.', 'warning'); return; }
        try {
            await apiFetch(`${API_URL}?id=${id}`, 'PUT', { title, due_date: dueDate });
            showToast('Task updated!', 'success');
            close();
            loadTasks();
        } catch (err) {
            showToast('Could not update: ' + err.message, 'error');
        }
    });
}

/* ═══════════════════════════════════════
   UNDO DELETE
═══════════════════════════════════════ */
function deleteTaskWithUndo(id, title) {
    let cancelled = false;
    showToast(`"${title}" deleted.`, 'info', 5000,
        [{ label: 'Undo', fn: () => { cancelled = true; loadTasks(); } }]);

    document.querySelector(`.task-item[data-id="${id}"]`)?.remove();

    setTimeout(async () => {
        if (cancelled) return;
        try {
            await apiFetch(`${API_URL}?id=${id}`, 'DELETE');
        } catch (err) {
            showToast('Delete failed: ' + err.message, 'error');
            loadTasks();
        }
    }, 5000);
}

/* ═══════════════════════════════════════
   STATUS FLASH
═══════════════════════════════════════ */
function flashRow(id) {
    const li = document.querySelector(`.task-item[data-id="${id}"]`);
    if (!li) return;
    li.style.background = '#d1fae5';
    setTimeout(() => { li.style.background = ''; }, 900);
}

/* ═══════════════════════════════════════
   LOAD
═══════════════════════════════════════ */
async function loadTasks() {
    showSkeleton();
    try {
        const data = await apiFetch(API_URL);
        renderTasks(data.tasks);
        checkDeadlines(data.tasks);
    } catch (err) {
        showToast('Failed to load tasks: ' + err.message, 'error');
    }
}

/* ═══════════════════════════════════════
   ADD
═══════════════════════════════════════ */
async function addTask(title, due_date) {
    const data = await apiFetch(API_URL, 'POST', { title, due_date, status: 'pending' });
    showToast(`"${data.task.title}" added!`, 'success');
    loadTasks();
}

/* ═══════════════════════════════════════
   UPDATE STATUS
═══════════════════════════════════════ */
async function updateStatus(id, status) {
    try {
        await apiFetch(`${API_URL}?id=${id}`, 'PUT', { status });
        flashRow(id);
        showToast('Status saved!', 'success', 2000);
        loadTasks();
    } catch (err) {
        showToast('Could not update status: ' + err.message, 'error');
    }
}

/* ═══════════════════════════════════════
   LISTENERS
═══════════════════════════════════════ */
function attachListeners() {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () =>
            deleteTaskWithUndo(btn.dataset.id, btn.dataset.title));
    });
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () =>
            openEditModal(btn.dataset.id, btn.dataset.title, btn.dataset.date));
    });
    document.querySelectorAll('.status-select').forEach(sel => {
        sel.addEventListener('change', () => updateStatus(sel.dataset.id, sel.value));
    });
}

/* ═══════════════════════════════════════
   FORM
═══════════════════════════════════════ */
function initAddForm() {
    const form = document.getElementById('form-add-task');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isSubmitting) return;
        const btn     = form.querySelector('button[type="submit"]');
        const title   = form.querySelector('[name="title"]').value.trim();
        const dueDate = form.querySelector('[name="due_date"]').value.trim();
        if (!title || !dueDate) { showToast('Fill in title and due date.', 'warning'); return; }
        isSubmitting = true;
        if (btn) { btn.disabled = true; btn.textContent = 'Adding...'; }
        try {
            await addTask(title, dueDate);
            form.reset();
        } catch (err) {
            showToast('Could not add task: ' + err.message, 'error');
        } finally {
            isSubmitting = false;
            if (btn) { btn.disabled = false; btn.textContent = 'Add Task'; }
        }
    });
}

/* ═══════════════════════════════════════
   INIT
═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadTasks();
    initAddForm();
});
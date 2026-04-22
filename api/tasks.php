<?php
require_once 'includes/config.php';
requireLogin();

$tasks = getUserTasks($_SESSION['user_id']);

// Filter tasks by status
$current_tasks = [];
$past_tasks = [];
$overdue_tasks = [];
$today = date('Y-m-d');

foreach ($tasks as $task) {
    if ($task['status'] == 'done') {
        $past_tasks[] = $task;
    } elseif ($task['due_date'] < $today) {
        $overdue_tasks[] = $task;
    } else {
        $current_tasks[] = $task;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Tasks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f7f8fc;
            color: #1a1a2e;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: white;
            border-right: 1px solid #e8ecf2;
            padding: 24px 0;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px 24px;
            border-bottom: 1px solid #e8ecf2;
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .brand-name {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            padding: 0 12px;
        }

        .s-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            color: #5a5a7a;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .s-link:hover, .s-link.active {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            color: #667eea;
        }

        .s-ico {
            font-size: 1.2rem;
            width: 28px;
        }

        .sidebar-bottom {
            position: absolute;
            bottom: 24px;
            left: 0;
            right: 0;
            padding: 0 20px;
        }

        .premium-btn {
            display: block;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }

        .add-new-btn {
            display: block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            color: #ef4444;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 10px;
            border-radius: 10px;
            background: #fef2f2;
        }

        /* ========== MAIN ========== */
        .main {
            margin-left: 260px;
            padding: 24px 32px;
        }

        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .time-date {
            font-size: 0.85rem;
            color: #8b8aa8;
        }

        .time {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1a2e;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
        }

        .user-email {
            font-size: 0.75rem;
            color: #8b8aa8;
        }

        /* Tasks Header */
        .tasks-header {
            margin-bottom: 24px;
        }

        .tasks-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid #e8ecf2;
            padding-bottom: 12px;
        }

        .filter-tab {
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .filter-tab.active {
            background: #667eea;
            color: white;
        }

        .filter-tab:not(.active):hover {
            background: #f0f2f9;
        }

        /* Subject Selector */
        .subject-selector {
            background: white;
            border: 1px solid #e8ecf2;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .subject-selector label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #5a5a7a;
        }

        .subject-selector select {
            padding: 8px 12px;
            border: 1px solid #e8ecf2;
            border-radius: 8px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
        }

        .add-task-btn {
            margin-left: auto;
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }

        /* Tasks List */
        .tasks-list {
            background: white;
            border-radius: 16px;
            border: 1px solid #e8ecf2;
            overflow: hidden;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f9;
            transition: background 0.2s;
        }

        .task-item:hover {
            background: #fafbfd;
        }

        .task-check {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .task-check.completed {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .task-info {
            flex: 1;
        }

        .task-title {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .task-title.completed {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .task-meta {
            display: flex;
            gap: 16px;
            font-size: 0.7rem;
            color: #8b8aa8;
        }

        .task-subject {
            background: #e0e7ff;
            color: #4f46e5;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.65rem;
        }

        .task-actions {
            display: flex;
            gap: 8px;
        }

        .task-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            padding: 4px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .task-actions button:hover {
            background: #f0f2f9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #8b8aa8;
        }

        .empty-ico {
            font-size: 48px;
            margin-bottom: 12px;
        }

        /* Add Task Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 28px;
            width: 90%;
            max-width: 450px;
        }

        .modal-content h3 {
            margin-bottom: 20px;
        }

        .modal-content input, .modal-content select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e8ecf2;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-save {
            background: #667eea;
            color: white;
            border: none;
        }

        .btn-cancel {
            background: #f0f2f9;
            border: none;
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"></div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link"><span class="s-ico"></span> Dashboard</a>
        <a href="calendar.php" class="s-link"><span class="s-ico"></span> Calendar</a>
        <a href="activities.php" class="s-link"><span class="s-ico"></span> Activities</a>
        <a href="tasks.php" class="s-link active"><span class="s-ico"></span> Tasks</a>
        <a href="exams.php" class="s-link"><span class="s-ico"></span> Exams</a>
        <a href="#" class="s-link"><span class="s-ico">⏱</span> Focus Timer</a>
    </nav>
    <div class="sidebar-bottom">
        <a href="#" class="premium-btn"> Try Premium Free</a>
        <a href="#" class="add-new-btn" onclick="openAddTaskModal()">+ Add New</a>
        <a href="logout.php" class="logout-link"> Déconnexion</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main">
    <header class="topbar">
        <div class="time-date">
            <div class="time" id="clock">--:--</div>
            <div id="date">--</div>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_nom']); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
        </div>
    </header>

    <div class="tasks-header">
        <h1> Tasks</h1>
        <div class="filter-tabs">
            <div class="filter-tab active" onclick="filterTasks('current')">Current</div>
            <div class="filter-tab" onclick="filterTasks('past')">Past</div>
            <div class="filter-tab" onclick="filterTasks('overdue')">Overdue</div>
        </div>
    </div>

    <div class="subject-selector">
        <label> Select Subject:</label>
        <select id="subjectFilter">
            <option value="all">All Subjects</option>
            <option value="math">Mathematics</option>
            <option value="php">PHP</option>
            <option value="web">Web Development</option>
            <option value="database">Database</option>
        </select>
        <button class="add-task-btn" onclick="openAddTaskModal()">+ Add Task</button>
    </div>

    <div class="tasks-list" id="tasksList">
        <!-- Tasks will be loaded here -->
    </div>
</main>

<!-- Add Task Modal -->
<div class="modal" id="addTaskModal">
    <div class="modal-content">
        <h3> Add New Task</h3>
        <input type="text" id="taskTitle" placeholder="Task title">
        <input type="date" id="taskDate">
        <select id="taskSubject">
            <option value="Math">Mathematics</option>
            <option value="PHP">PHP</option>
            <option value="Web">Web Development</option>
            <option value="Database">Database</option>
        </select>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeAddTaskModal()">Cancel</button>
            <button class="btn-save" onclick="saveTask()">Save</button>
        </div>
    </div>
</div>

<script>
    let tasks = <?php echo json_encode($tasks); ?>;
    let currentFilter = 'current';
    let currentSubject = 'all';

    function formatDate(date) {
        const d = new Date(date);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function filterTasks(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        renderTasks();
    }

    function renderTasks() {
        let filteredTasks = [];
        
        if (currentFilter === 'current') {
            filteredTasks = tasks.filter(t => t.status !== 'done' && t.due_date >= new Date().toISOString().split('T')[0]);
        } else if (currentFilter === 'past') {
            filteredTasks = tasks.filter(t => t.status === 'done');
        } else if (currentFilter === 'overdue') {
            filteredTasks = tasks.filter(t => t.status !== 'done' && t.due_date < new Date().toISOString().split('T')[0]);
        }
        
        if (currentSubject !== 'all') {
            // filter by subject if needed
        }
        
        const container = document.getElementById('tasksList');
        
        if (filteredTasks.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-ico"></div>
                    <p>No tasks found</p>
                    <button class="add-task-btn" onclick="openAddTaskModal()">+ Add a task</button>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredTasks.map(task => `
            <div class="task-item" data-id="${task.id}">
                <div class="task-check ${task.status === 'done' ? 'completed' : ''}" onclick="toggleTaskStatus(${task.id})">
                    ${task.status === 'done' ? '' : ''}
                </div>
                <div class="task-info">
                    <div class="task-title ${task.status === 'done' ? 'completed' : ''}">${escapeHtml(task.title)}</div>
                    <div class="task-meta">
                        <span class="task-subject"> ${task.subject || 'Task'}</span>
                        <span> ${formatDate(task.due_date)}</span>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="editTask(${task.id})"></button>
                    <button onclick="deleteTask(${task.id})"></button>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function openAddTaskModal() {
        document.getElementById('addTaskModal').style.display = 'flex';
    }

    function closeAddTaskModal() {
        document.getElementById('addTaskModal').style.display = 'none';
        document.getElementById('taskTitle').value = '';
        document.getElementById('taskDate').value = '';
    }

    function saveTask() {
        const title = document.getElementById('taskTitle').value;
        const due_date = document.getElementById('taskDate').value;
        const subject = document.getElementById('taskSubject').value;
        
        if (!title || !due_date) {
            alert('Please fill all fields');
            return;
        }
        
        fetch('api/tasks.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, due_date, status: 'pending', subject })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error adding task');
            }
        });
        
        closeAddTaskModal();
    }

    function toggleTaskStatus(id) {
        const task = tasks.find(t => t.id == id);
        const newStatus = task.status === 'done' ? 'pending' : 'done';
        
        fetch(`api/tasks.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(() => location.reload());
    }

    function deleteTask(id) {
        if (confirm('Delete this task?')) {
            fetch(`api/tasks.php?id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => location.reload());
        }
    }

    function editTask(id) {
        // Implement edit functionality
        alert('Edit feature coming soon');
    }

    // Clock
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = hours + ':' + minutes;
        
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('date').textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()];
    }
    
    updateClock();
    setInterval(updateClock, 60000);
    renderTasks();
</script>

</body>
</html>
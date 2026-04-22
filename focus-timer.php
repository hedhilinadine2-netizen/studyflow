<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Focus Timer</title>
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

        .dark-mode-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            color: #5a5a7a;
            text-decoration: none;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        /* ========== MAIN ========== */
        .main {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 48px;
            border-bottom: 1px solid #e8ecf2;
            background: white;
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

        /* Timer Content */
        .timer-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 48px;
        }

        .timer-card {
            max-width: 500px;
            width: 100%;
            text-align: center;
            background: white;
            border-radius: 32px;
            border: 1px solid #e8ecf2;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .timer-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #667eea;
            margin-bottom: 16px;
        }

        .timer-display {
            font-size: 5rem;
            font-weight: 700;
            font-family: 'Monaco', 'Courier New', monospace;
            color: #1a1a2e;
            letter-spacing: 4px;
            margin-bottom: 32px;
        }

        .timer-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 40px;
        }

        .timer-btn {
            padding: 10px 24px;
            border-radius: 40px;
            border: 1px solid #e8ecf2;
            background: white;
            color: #1a1a2e;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .timer-btn:hover {
            background: #f0f2f9;
        }

        .btn-start {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .motivation-text {
            margin-bottom: 32px;
        }

        .motivation-text h2 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a2e;
        }

        .motivation-text p {
            color: #8b8aa8;
            font-size: 0.85rem;
        }

        /* Checklist Section */
        .checklist-section {
            background: #f7f8fc;
            border-radius: 20px;
            padding: 20px;
            margin-top: 32px;
            text-align: left;
        }

        .checklist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8ecf2;
        }

        .checklist-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a2e;
        }

        .add-list-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
        }

        .checklist-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
        }

        .checklist-check.completed {
            background: #10b981;
            border-color: #10b981;
        }

        .checklist-text {
            flex: 1;
            font-size: 0.85rem;
            color: #1a1a2e;
        }

        .checklist-text.completed {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .delete-item {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 14px;
        }

        .checklist-input {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e8ecf2;
        }

        .checklist-input input {
            flex: 1;
            background: white;
            border: 1px solid #e8ecf2;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.8rem;
        }

        .checklist-input input:focus {
            outline: none;
            border-color: #667eea;
        }

        .checklist-input button {
            background: #667eea;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            color: white;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .main {
                margin-left: 0;
            }
            .timer-display {
                font-size: 3.5rem;
            }
            .topbar {
                padding: 20px 24px;
            }
            .timer-content {
                padding: 24px;
            }
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">SF</div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link"><span class="s-ico"></span> Dashboard</a>
        <a href="calendar.php" class="s-link"><span class="s-ico"></span> Calendar</a>
        <a href="tasks.php" class="s-link"><span class="s-ico"></span> Tasks</a>
        <a href="exams.php" class="s-link"><span class="s-ico"></span> Exams</a>
        <a href="classes.php" class="s-link"><span class="s-ico"></span> Classes</a>
        <a href="vacations.php" class="s-link"><span class="s-ico"></span> Vacations</a>
        <a href="focus-timer.php" class="s-link active"><span class="s-ico"></span> Focus Timer</a>
        <div class="sidebar-bottom">
        
        <a href="logout.php" class="logout-link"> Déconnexion</a>
    </div>
    </nav>
   <div class="sidebar-bottom">
        
        <a href="logout.php" class="logout-link"> Déconnexion</a>
    </div>
</aside>

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

    <div class="timer-content">
        <div class="timer-card">
            <div class="timer-label">FOCUS</div>
            <div class="timer-display" id="timerDisplay">25:00</div>

            <div class="timer-buttons">
                <button class="timer-btn btn-start" onclick="startTimer()">Start</button>
                <button class="timer-btn" onclick="pauseTimer()">Pause</button>
                <button class="timer-btn" onclick="resetTimer()">Reset</button>
            </div>

            <div class="motivation-text">
                <h2>You've got this. </h2>
                <p>Focused work makes you up to five times more productive.</p>
            </div>

            <div class="checklist-section">
                <div class="checklist-header">
                    <h3> Checklist</h3>
                    <button class="add-list-btn" onclick="showAddInput()">+ Add List</button>
                </div>
                <div id="checklistItems"></div>
                <div id="addInputContainer" style="display: none;" class="checklist-input">
                    <input type="text" id="newTaskInput" placeholder="Write a task...">
                    <button onclick="addTask()">Add</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let timeLeft = 25 * 60;
    let timerInterval = null;
    let isRunning = false;

    let tasks = JSON.parse(localStorage.getItem('focus_tasks') || '[]');

    function updateDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timerDisplay').textContent = 
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    function startTimer() {
        if (isRunning) return;
        isRunning = true;
        timerInterval = setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
                updateDisplay();
            } else {
                clearInterval(timerInterval);
                isRunning = false;
                alert(' Time is up! Great job!');
                timeLeft = 25 * 60;
                updateDisplay();
            }
        }, 1000);
    }

    function pauseTimer() {
        clearInterval(timerInterval);
        isRunning = false;
    }

    function resetTimer() {
        pauseTimer();
        timeLeft = 25 * 60;
        updateDisplay();
    }

    function renderChecklist() {
        const container = document.getElementById('checklistItems');
        if (tasks.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:20px; color:#9ca3af;">No tasks yet. Add one!</div>';
            return;
        }
        
        container.innerHTML = tasks.map((task, index) => `
            <div class="checklist-item">
                <div class="checklist-check ${task.completed ? 'completed' : ''}" onclick="toggleTask(${index})">
                    ${task.completed ? '' : ''}
                </div>
                <div class="checklist-text ${task.completed ? 'completed' : ''}">${escapeHtml(task.text)}</div>
                <button class="delete-item" onclick="deleteTask(${index})"></button>
            </div>
        `).join('');
    }

    function showAddInput() {
        document.getElementById('addInputContainer').style.display = 'flex';
    }

    function addTask() {
        const input = document.getElementById('newTaskInput');
        const text = input.value.trim();
        if (text) {
            tasks.push({ text: text, completed: false });
            localStorage.setItem('focus_tasks', JSON.stringify(tasks));
            renderChecklist();
            input.value = '';
            document.getElementById('addInputContainer').style.display = 'none';
        }
    }

    function toggleTask(index) {
        tasks[index].completed = !tasks[index].completed;
        localStorage.setItem('focus_tasks', JSON.stringify(tasks));
        renderChecklist();
    }

    function deleteTask(index) {
        tasks.splice(index, 1);
        localStorage.setItem('focus_tasks', JSON.stringify(tasks));
        renderChecklist();
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

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
    updateDisplay();
    renderChecklist();
</script>

</body>
</html>
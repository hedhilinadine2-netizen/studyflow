<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user_name = $_SESSION['user_nom'] ?? 'Utilisateur';
$user_email = $_SESSION['user_email'] ?? '';
$tasks = getUserTasks($_SESSION['user_id']);

// Statistiques
$pending = 0;
$done = 0;
$overdue = 0;
$today = date('Y-m-d');

foreach ($tasks as $task) {
    if ($task['status'] == 'done') {
        $done++;
    } else {
        $pending++;
        if ($task['due_date'] < $today) {
            $overdue++;
        }
    }
}

// Tâches à faire aujourd'hui
$tasks_today = 0;
foreach ($tasks as $task) {
    if ($task['due_date'] == $today && $task['status'] != 'done') {
        $tasks_today++;
    }
}

// Tâches récentes (5 dernières)
$recent_tasks = array_slice(array_reverse($tasks), 0, 5);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Dashboard</title>
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
            overflow: hidden;
        }

        .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
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

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 32px;
            color: white;
        }

        .welcome-banner h1 {
            font-size: 1.6rem;
            margin-bottom: 6px;
        }

        .welcome-banner p {
            opacity: 0.85;
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e8ecf2;
        }

        .stat-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8b8aa8;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .stat-sub {
            font-size: 0.7rem;
            color: #8b8aa8;
            margin-top: 4px;
        }

        .stat-number.pending { color: #f59e0b; }
        .stat-number.overdue { color: #ef4444; }
        .stat-number.done { color: #10b981; }
        .stat-number.streak { color: #667eea; }

        /* Two Columns */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
        }

        /* Tasks Card */
        .card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e8ecf2;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e8ecf2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-header a {
            font-size: 0.75rem;
            color: #667eea;
            text-decoration: none;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid #f0f2f9;
        }

        .task-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            background: white;
        }

        .task-check.done {
            background: #10b981;
            border-color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .task-info {
            flex: 1;
        }

        .task-title {
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .task-title.done {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .task-meta {
            font-size: 0.7rem;
            color: #8b8aa8;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #8b8aa8;
        }

        /* Calendar Mini */
        .calendar-mini {
            padding: 16px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .calendar-week {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            margin-bottom: 8px;
        }

        .calendar-week span {
            font-size: 0.7rem;
            color: #8b8aa8;
            font-weight: 500;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .cal-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .cal-day.today {
            background: #667eea;
            color: white;
            font-weight: 600;
        }

        .cal-day:hover:not(.today) {
            background: #f0f2f9;
        }

        /* Next Exam */
        .next-exam {
            padding: 16px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">SF</div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link active">
            <span class="s-ico"></span> Dashboard
        </a>
        <a href="calendar.php" class="s-link">
            <span class="s-ico"></span> Calendar
        </a>
        <a href="tasks.php" class="s-link">
            <span class="s-ico"></span> Tasks
        </a>
        <a href="exams.php" class="s-link">
            <span class="s-ico"></span> Exams
        </a>
        <a href="classes.php" class="s-link">
            <span class="s-ico"></span> Classes
        </a>
        <a href="vacations.php" class="s-link">
            <span class="s-ico"></span> Vacations
        </a>
        <a href="focus-timer.php" class="s-link">
            <span class="s-ico"></span> Focus Timer
        </a>
        <div class="sidebar-bottom">
        
        <a href="logout.php" class="logout-link"> Déconnexion</a>
    </div>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="main">
    <!-- Topbar -->
    <header class="topbar">
        <div class="time-date">
            <div class="time" id="clock">--:--</div>
            <div id="date">--</div>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
        </div>
    </header>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($user_name); ?> </h1>
        <p><?php echo $tasks_today; ?> task<?php echo $tasks_today > 1 ? 's' : ''; ?> due today. Stay focused! </p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"> PENDING TASKS</div>
            <div class="stat-number pending"><?php echo $pending; ?></div>
            <div class="stat-sub">Last 7 days</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"> OVERDUE TASKS</div>
            <div class="stat-number overdue"><?php echo $overdue; ?></div>
            <div class="stat-sub">Last 7 days</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">TASKS COMPLETED</div>
            <div class="stat-number done"><?php echo $done; ?></div>
            <div class="stat-sub">Last 7 days</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"> YOUR STREAK</div>
            <div class="stat-number streak">0</div>
            <div class="stat-sub">Last 7 days</div>
        </div>
    </div>

    <!-- Two Columns -->
    <div class="two-columns">
        <!-- Recent Tasks -->
        <div class="card">
            <div class="card-header">
                <h3> Recent Tasks</h3>
                <a href="tasks.php">View all </a>
            </div>
            <?php if (empty($recent_tasks)): ?>
                <div class="empty-state">
                     No tasks yet<br>
                    <a href="#" style="color:#667eea; font-size:0.8rem;">+ Add a task</a>
                </div>
            <?php else: ?>
                <?php foreach ($recent_tasks as $task): ?>
                <div class="task-item">
                    <div class="task-check <?php echo $task['status'] == 'done' ? 'done' : ''; ?>">
                        <?php echo $task['status'] == 'done' ? '' : ''; ?>
                    </div>
                    <div class="task-info">
                        <div class="task-title <?php echo $task['status'] == 'done' ? 'done' : ''; ?>">
                            <?php echo htmlspecialchars($task['title']); ?>
                        </div>
                        <div class="task-meta">
                            <?php echo date('d M Y', strtotime($task['due_date'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Side -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Mini Calendar -->
            <div class="card">
                <div class="card-header">
                    <h3>Calendar</h3>
                    <a href="calendar.php">View </a>
                </div>
                <div class="calendar-mini">
                    <div class="calendar-header">
                        <span id="calendar-month"></span>
                    </div>
                    <div class="calendar-week">
                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                    </div>
                    <div class="calendar-days" id="calendar-days"></div>
                </div>
            </div>

            <!-- Next Exam -->
            <div class="card">
                <div class="card-header">
                    <h3> Next Exam</h3>
                    <a href="exams.php">Add </a>
                </div>
                <div class="next-exam">
                    <span style="color: #8b8aa8;">No exams planned yet.</span>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
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

    // Calendar
    function buildCalendar() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('calendar-month').textContent = months[month] + ' ' + year;
        
        const firstDay = new Date(year, month, 1).getDay();
        const start = firstDay === 0 ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const container = document.getElementById('calendar-days');
        container.innerHTML = '';
        
        for (let i = 0; i < start; i++) {
            const empty = document.createElement('div');
            empty.style.opacity = '0';
            container.appendChild(empty);
        }
        
        for (let d = 1; d <= daysInMonth; d++) {
            const day = document.createElement('div');
            day.textContent = d;
            day.className = 'cal-day';
            if (d === now.getDate()) {
                day.classList.add('today');
            }
            container.appendChild(day);
        }
    }
    buildCalendar();
</script>

</body>
</html>
<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Calendar</title>
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

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .calendar-container {
            background: white;
            border-radius: 20px;
            border: 1px solid #e8ecf2;
            padding: 24px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .month-nav {
            display: flex;
            gap: 16px;
        }

        .month-nav button {
            background: #f0f2f9;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }

        .month-title {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            padding: 12px 0;
            background: #fafbfd;
            border-radius: 12px;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px;
            border-radius: 12px;
            cursor: pointer;
            background: #fafbfd;
        }

        .day.today {
            background: #667eea;
            color: white;
        }

        .day-number {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .task-dot {
            width: 6px;
            height: 6px;
            background: #667eea;
            border-radius: 50%;
            margin-top: 4px;
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

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">SF</div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link"><span class="s-ico"></span> Dashboard</a>
        <a href="calendar.php" class="s-link active"><span class="s-ico"></span> Calendar</a>
        <a href="tasks.php" class="s-link"><span class="s-ico"></span> Tasks</a>
        <a href="exams.php" class="s-link"><span class="s-ico"></span> Exams</a>
        <a href="classes.php" class="s-link"><span class="s-ico"></span> Classes</a>
        <a href="vacations.php" class="s-link"><span class="s-ico"></span> Vacations</a>
        <a href="focus-timer.php" class="s-link"><span class="s-ico"></span> Focus Timer</a>
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

    <h1 class="page-title"> Calendar</h1>

    <div class="calendar-container">
        <div class="calendar-header">
            <div class="month-nav">
                <button onclick="changeMonth(-1)"> Prev</button>
                <button onclick="goToday()">Today</button>
                <button onclick="changeMonth(1)">Next </button>
            </div>
            <div class="month-title" id="monthTitle">March 2026</div>
        </div>
        <div class="weekdays">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>
        <div class="days" id="calendarDays"></div>
    </div>
</main>

<script>
    let currentDate = new Date();

    function formatDate(date) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[date.getMonth()] + ' ' + date.getFullYear();
    }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        document.getElementById('monthTitle').textContent = formatDate(currentDate);
        
        const firstDay = new Date(year, month, 1).getDay();
        let start = firstDay === 0 ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const container = document.getElementById('calendarDays');
        container.innerHTML = '';
        
        for (let i = 0; i < start; i++) {
            const empty = document.createElement('div');
            empty.className = 'day';
            empty.style.background = 'transparent';
            container.appendChild(empty);
        }
        
        const today = new Date();
        for (let d = 1; d <= daysInMonth; d++) {
            const day = document.createElement('div');
            day.className = 'day';
            if (d === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                day.classList.add('today');
            }
            day.innerHTML = `<div class="day-number">${d}</div>`;
            container.appendChild(day);
        }
    }

    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar();
    }

    function goToday() {
        currentDate = new Date();
        renderCalendar();
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
    renderCalendar();
</script>

</body>
</html>
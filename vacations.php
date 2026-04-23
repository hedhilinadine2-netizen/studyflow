<?php
require_once 'includes/config.php';
requireLogin();

// Ensure vacations table has status column
try {
    $pdo->exec("ALTER TABLE vacations ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'upcoming'");
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE vacations ADD COLUMN status VARCHAR(50) DEFAULT 'upcoming'");
    } catch (Exception $e2) {
        // Column might already exist
    }
}

// Fetch vacations
$stmt = $pdo->prepare("SELECT * FROM vacations WHERE user_id = ? ORDER BY start_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$vacations = $stmt->fetchAll();

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Vacations</title>
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

        .filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .filter-tab {
            padding: 10px 24px;
            border-radius: 30px;
            background: white;
            border: 1px solid #e8ecf2;
            cursor: pointer;
            font-weight: 500;
        }

        .filter-tab.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

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

        .tasks-list {
            background: white;
            border-radius: 20px;
            border: 1px solid #e8ecf2;
            overflow: hidden;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 16px;
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
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
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
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .task-title.completed {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .task-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: #8b8aa8;
        }

        .task-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge {
            background: #e0e7ff;
            color: #4f46e5;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-overdue {
            background: #fef2f2;
            color: #ef4444;
        }

        .task-actions {
            display: flex;
            gap: 8px;
        }

        .task-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 8px;
            border-radius: 8px;
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
        <a href="calendar.php" class="s-link"><span class="s-ico"></span> Calendar</a>
        <a href="tasks.php" class="s-link"><span class="s-ico"></span> Tasks</a>
        <a href="exams.php" class="s-link"><span class="s-ico"></span> Exams</a>
        <a href="classes.php" class="s-link"><span class="s-ico"></span> Classes</a>
        <a href="vacations.php" class="s-link active"><span class="s-ico"></span> Vacations</a>
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

    <h1 class="page-title"> Vacations</h1>

    <div class="filter-tabs">
        <div class="filter-tab active" onclick="filterVacations(event, 'current')">Current</div>
        <div class="filter-tab" onclick="filterVacations(event, 'completed')">Completed</div>
    </div>

    <div class="subject-selector">
        <a href="add-vacation.php" style="margin-left: auto; background:#667eea; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-size:0.85rem;">+ Add Vacation</a>
    </div>

    <div class="tasks-list" id="vacationsList">
    </div>
</main>

<script>
    let allVacations = <?php echo json_encode($vacations); ?>;
    let currentFilter = 'current';
    let today = '<?php echo $today; ?>';

    function formatDate(date) {
        const d = new Date(date);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function getVacationStatus(vacation) {
        if (vacation.end_date < today) return 'completed';
        if (vacation.start_date <= today && vacation.end_date >= today) return 'ongoing';
        return 'upcoming';
    }

    function filterVacations(evt, filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        evt.target.classList.add('active');
        renderVacations();
    }

    function renderVacations() {
        let filteredVacations = currentFilter === 'current' ? 
            allVacations.filter(v => getVacationStatus(v) !== 'completed') : 
            allVacations.filter(v => getVacationStatus(v) === 'completed');
        
        const container = document.getElementById('vacationsList');
        
        if (filteredVacations.length === 0) {
            let message = currentFilter === 'current' ? 'No current vacations found' : 'No completed vacations found';
            
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-ico"></div>
                    <p>${message}</p>
                    <a href="add-vacation.php" style="color:#667eea; text-decoration:none;">+ Add a vacation</a>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredVacations.map(vacation => {
            let badgeClass = '';
            let badgeText = '';
            const status = getVacationStatus(vacation);
            
            if (status === 'upcoming') {
                badgeClass = 'badge';
                badgeText = 'Upcoming';
            } else if (status === 'ongoing') {
                badgeClass = 'badge';
                badgeText = 'Ongoing';
            } else {
                badgeClass = 'badge';
                badgeText = 'Completed';
            }
            
            return `
            <div class="task-item" data-id="${vacation.id}">
                <div class="task-check ${status === 'completed' ? 'completed' : ''}" onclick="toggleStatus(${vacation.id}, '${status}')">
                </div>
                <div class="task-info">
                    <div class="task-title ${status === 'completed' ? 'completed' : ''}">${escapeHtml(vacation.title)}</div>
                    <div class="task-meta">
                        <span> ${formatDate(vacation.start_date)} - ${formatDate(vacation.end_date)}</span>
                        ${vacation.destination ? `<span> ${escapeHtml(vacation.destination)}</span>` : ''}
                        <span class="${badgeClass}">${badgeText}</span>
                    </div>
                    ${vacation.description ? `<div style="font-size:0.7rem; color:#8b8aa8; margin-top:6px;">${escapeHtml(vacation.description)}</div>` : ''}
                </div>
                <div class="task-actions">
                    <button onclick="editVacation(${vacation.id})" title="Edit">✏️</button>
                    <button onclick="deleteVacation(${vacation.id})" title="Delete">🗑️</button>
                </div>
            </div>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function toggleStatus(id, currentStatus) {
        const newStatus = currentStatus === 'completed' ? 'upcoming' : 'completed';
        window.location.href = 'api/update-vacation-status.php?id=' + id + '&status=' + newStatus;
    }

    function deleteVacation(id) {
        if (confirm('Delete this vacation?')) {
            window.location.href = 'api/delete-vacation.php?id=' + id;
        }
    }

    function editVacation(id) {
        window.location.href = 'edit-vacation.php?id=' + id;
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
    renderVacations();
</script>

</body>
</html>
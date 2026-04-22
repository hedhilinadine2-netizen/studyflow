<?php
require_once 'includes/config.php';
requireLogin();

// Create vacations table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS vacations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    destination VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Fetch vacations
$stmt = $pdo->prepare("SELECT * FROM vacations WHERE user_id = ? ORDER BY start_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$vacations = $stmt->fetchAll();

// Filter vacations
$upcoming_vacations = [];
$ongoing_vacations = [];
$completed_vacations = [];
$today = date('Y-m-d');

foreach ($vacations as $vacation) {
    if ($vacation['end_date'] < $today) {
        $completed_vacations[] = $vacation;
    } elseif ($vacation['start_date'] <= $today && $vacation['end_date'] >= $today) {
        $ongoing_vacations[] = $vacation;
    } else {
        $upcoming_vacations[] = $vacation;
    }
}
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

        .vacations-list {
            background: white;
            border-radius: 20px;
            border: 1px solid #e8ecf2;
            overflow: hidden;
        }

        .vacation-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f9;
            transition: background 0.2s;
        }

        .vacation-item:hover {
            background: #fafbfd;
        }

        .vacation-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .vacation-info {
            flex: 1;
        }

        .vacation-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .vacation-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: #8b8aa8;
        }

        .vacation-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-upcoming {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .badge-ongoing {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-completed {
            background: #ecfdf5;
            color: #10b981;
        }

        .vacation-actions {
            display: flex;
            gap: 8px;
        }

        .vacation-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .vacation-actions button:hover {
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
        <div class="brand-icon">📚</div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link"><span class="s-ico">🏠</span> Dashboard</a>
        <a href="calendar.php" class="s-link"><span class="s-ico">📅</span> Calendar</a>
        <a href="tasks.php" class="s-link"><span class="s-ico">✅</span> Tasks</a>
        <a href="exams.php" class="s-link"><span class="s-ico">📖</span> Exams</a>
        <a href="classes.php" class="s-link"><span class="s-ico">📚</span> Classes</a>
        <a href="vacations.php" class="s-link active"><span class="s-ico">🌴</span> Vacations</a>
        <a href="focus-timer.php" class="s-link"><span class="s-ico">⏱️</span> Focus Timer</a>
    </nav>
   <div class="sidebar-bottom">
        
        <a href="logout.php" class="logout-link">🚪 Déconnexion</a>
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

    <h1 class="page-title">🌴 Vacations</h1>

    <div class="filter-tabs">
        <div class="filter-tab active" onclick="filterVacations('upcoming')">Upcoming</div>
        <div class="filter-tab" onclick="filterVacations('ongoing')">Ongoing</div>
        <div class="filter-tab" onclick="filterVacations('completed')">Completed</div>
    </div>

    <div class="vacations-list" id="vacationsList">
        <!-- Vacations will be loaded here -->
    </div>
</main>

<script>
    let allVacations = <?php echo json_encode($vacations); ?>;
    let currentFilter = 'upcoming';
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

    function filterVacations(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        renderVacations();
    }

    function renderVacations() {
        let filteredVacations = allVacations.filter(v => getVacationStatus(v) === currentFilter);
        
        const container = document.getElementById('vacationsList');
        
        if (filteredVacations.length === 0) {
            let message = '';
            if (currentFilter === 'upcoming') message = 'No upcoming vacations planned';
            else if (currentFilter === 'ongoing') message = 'No ongoing vacations';
            else message = 'No completed vacations';
            
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-ico">🌴</div>
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
                badgeClass = 'badge-upcoming';
                badgeText = 'Upcoming';
            } else if (status === 'ongoing') {
                badgeClass = 'badge-ongoing';
                badgeText = '🟢 Ongoing';
            } else {
                badgeClass = 'badge-completed';
                badgeText = 'Completed ✓';
            }
            
            return `
            <div class="vacation-item" data-id="${vacation.id}">
                <div class="vacation-icon">🌴</div>
                <div class="vacation-info">
                    <div class="vacation-title">${escapeHtml(vacation.title)}</div>
                    <div class="vacation-meta">
                        <span>📅 ${formatDate(vacation.start_date)} → ${formatDate(vacation.end_date)}</span>
                        ${vacation.destination ? `<span>📍 ${escapeHtml(vacation.destination)}</span>` : ''}
                        <span class="badge ${badgeClass}">${badgeText}</span>
                    </div>
                    ${vacation.description ? `<div style="font-size:0.7rem; color:#8b8aa8; margin-top:6px;">${escapeHtml(vacation.description)}</div>` : ''}
                </div>
                <div class="vacation-actions">
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

    function deleteVacation(id) {
        if (confirm('Delete this vacation?')) {
            fetch(`api/delete-vacation.php?id=${id}`, { method: 'DELETE' })
            .then(() => location.reload());
        }
    }

    function editVacation(id) {
        window.location.href = `edit-vacation.php?id=${id}`;
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
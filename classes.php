<?php
require_once 'includes/config.php';
requireLogin();

// Create classes table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    teacher VARCHAR(255),
    room VARCHAR(100),
    day_of_week VARCHAR(20),
    start_time TIME,
    end_time TIME,
    color VARCHAR(20) DEFAULT '#667eea',
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Fetch classes
$stmt = $pdo->prepare("SELECT * FROM classes WHERE user_id = ? ORDER BY class_name ASC");
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

// Filter classes
$active_classes = [];
$completed_classes = [];

foreach ($classes as $class) {
    if ($class['status'] == 'active') {
        $active_classes[] = $class;
    } else {
        $completed_classes[] = $class;
    }
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Classes</title>
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

        .classes-list {
            background: white;
            border-radius: 20px;
            border: 1px solid #e8ecf2;
            overflow: hidden;
        }

        .class-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f9;
            transition: background 0.2s;
        }

        .class-item:hover {
            background: #fafbfd;
        }

        .class-color {
            width: 12px;
            height: 50px;
            border-radius: 6px;
        }

        .class-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .class-info {
            flex: 1;
        }

        .class-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .class-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: #8b8aa8;
        }

        .class-meta span {
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

        .badge-completed {
            background: #ecfdf5;
            color: #10b981;
        }

        .class-actions {
            display: flex;
            gap: 8px;
        }

        .class-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .class-actions button:hover {
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
        <a href="classes.php" class="s-link active"><span class="s-ico"></span> Classes</a>
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

    <h1 class="page-title"> Classes</h1>

    <div class="filter-tabs">
        <div class="filter-tab active" onclick="filterClasses('active')">Active</div>
        <div class="filter-tab" onclick="filterClasses('completed')">Completed</div>
    </div>

    <div class="subject-selector">
        <label> Filter by Subject:</label>
        <select id="subjectFilter" onchange="filterClasses(currentFilter)">
            <option value="all">All Subjects</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Physics">Physics</option>
            <option value="PHP">PHP</option>
            <option value="Web Development">Web Development</option>
            <option value="Database">Database</option>
            <option value="English">English</option>
            <option value="Computer Science">Computer Science</option>
        </select>
        <a href="add-class.php" style="margin-left: auto; background:#667eea; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-size:0.85rem;">+ Add Class</a>
    </div>

    <div class="classes-list" id="classesList">
        <!-- Classes will be loaded here -->
    </div>
</main>

<script>
    let allClasses = <?php echo json_encode($classes); ?>;
    let currentFilter = 'active';
    let currentSubject = 'all';

    function formatTime(time) {
        if (!time) return '';
        const t = time.split(':');
        return t[0] + ':' + t[1];
    }

    function filterClasses(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        renderClasses();
    }

    function renderClasses() {
        let filteredClasses = currentFilter === 'active' ? 
            allClasses.filter(c => c.status !== 'completed') : 
            allClasses.filter(c => c.status === 'completed');
        
        if (currentSubject !== 'all') {
            filteredClasses = filteredClasses.filter(c => c.subject === currentSubject);
        }
        
        const container = document.getElementById('classesList');
        
        if (filteredClasses.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-ico"></div>
                    <p>No classes found</p>
                    <a href="add-class.php" style="color:#667eea; text-decoration:none;">+ Add a class</a>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredClasses.map(classItem => `
            <div class="class-item" data-id="${classItem.id}">
                <div class="class-color" style="background: ${classItem.color || '#667eea'};"></div>
                <div class="class-icon"></div>
                <div class="class-info">
                    <div class="class-title">${escapeHtml(classItem.class_name)}</div>
                    <div class="class-meta">
                        <span> ${escapeHtml(classItem.subject)}</span>
                        ${classItem.teacher ? `<span>‍ ${escapeHtml(classItem.teacher)}</span>` : ''}
                        ${classItem.room ? `<span> ${escapeHtml(classItem.room)}</span>` : ''}
                        ${classItem.day_of_week ? `<span> ${escapeHtml(classItem.day_of_week)}</span>` : ''}
                        ${classItem.start_time ? `<span>⏰ ${formatTime(classItem.start_time)}${classItem.end_time ? ' - ' + formatTime(classItem.end_time) : ''}</span>` : ''}
                        <span class="badge ${classItem.status === 'completed' ? 'badge-completed' : ''}">${classItem.status === 'active' ? 'Active' : 'Completed'}</span>
                    </div>
                </div>
                <div class="class-actions">
                    <button onclick="editClass(${classItem.id})" title="Edit"></button>
                    <button onclick="deleteClass(${classItem.id})" title="Delete"></button>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function deleteClass(id) {
        if (confirm('Delete this class?')) {
            fetch(`api/delete-class.php?id=${id}`, { method: 'DELETE' })
            .then(() => location.reload());
        }
    }

    function editClass(id) {
        window.location.href = `edit-class.php?id=${id}`;
    }

    // Update subject filter
    document.getElementById('subjectFilter').addEventListener('change', function() {
        currentSubject = this.value;
        renderClasses();
    });

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
    renderClasses();
</script>

</body>
</html>
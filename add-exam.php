<?php
require_once 'includes/config.php';
requireLogin();

$error = '';

// Create the exams table with all required columns
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS exams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        exam_name VARCHAR(255) NOT NULL,
        exam_type VARCHAR(100) DEFAULT 'Exam',
        subject VARCHAR(255) NOT NULL,
        mode VARCHAR(100) DEFAULT 'In Person',
        seat VARCHAR(50),
        room VARCHAR(100),
        exam_date DATE NOT NULL,
        exam_time TIME,
        duration INT,
        status VARCHAR(50) DEFAULT 'current',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {
    // Table might already exist
}

// Add status column if it doesn't exist
try {
    $pdo->exec("ALTER TABLE exams ADD COLUMN status VARCHAR(50) DEFAULT 'current'");
} catch (Exception $e) {
    // Column might already exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = isset($_POST['exam_name']) ? trim($_POST['exam_name']) : '';
    $exam_type = isset($_POST['exam_type']) ? trim($_POST['exam_type']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $mode = isset($_POST['mode']) ? trim($_POST['mode']) : '';
    $seat = isset($_POST['seat']) ? trim($_POST['seat']) : '';
    $room = isset($_POST['room']) ? trim($_POST['room']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $duration = isset($_POST['duration']) ? trim($_POST['duration']) : '';
    
    $exam_type = $exam_type ?: 'Exam';
    $mode = $mode ?: 'In Person';
    
    if (empty($exam_name) || empty($subject) || empty($date)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            // Insert with explicit column list matching values
            $sql = "INSERT INTO exams (user_id, exam_name, exam_type, subject, mode, seat, room, exam_date, exam_time, duration, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'],
                $exam_name,
                $exam_type,
                $subject,
                $mode,
                $seat,
                $room,
                $date,
                $time,
                $duration,
                'current'
            ]);
            header("Location: exams.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error adding exam: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - Add Exam</title>
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

        .s-link:hover {
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

        /* Form Container */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            border: 1px solid #e8ecf2;
            padding: 32px;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #8b8aa8;
            font-size: 0.85rem;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e8ecf2;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a2e;
        }

        .form-group label .required {
            color: #ef4444;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e8ecf2;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #ecfdf5;
            color: #10b981;
            border: 1px solid #d1fae5;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e8ecf2;
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #f0f2f9;
            color: #5a5a7a;
            padding: 12px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .main {
                margin-left: 0;
            }
            .form-row, .form-row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"></div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link"><span class="s-ico"></span> Dashboard</a>
        <a href="calendar.php" class="s-link"><span class="s-ico"></span> Calendar</a>
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

    <div class="form-container">
        <h1 class="form-title"> Add New Exam</h1>
        <div class="form-subtitle">Fill in the details below to schedule your exam</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Exam Name <span class="required">*</span></label>
                    <input type="text" name="exam_name" placeholder="e.g., Final Exam PHP" required>
                </div>
                <div class="form-group">
                    <label>Exam Type</label>
                    <select name="exam_type">
                        <option value="Exam"> Exam</option>
                        <option value="Quiz"> Quiz</option>
                        <option value="Test"> Test</option>
                        <option value="Final"> Final Exam</option>
                        <option value="Midterm"> Midterm</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Subject <span class="required">*</span></label>
                    <select name="subject" required>
                        <option value="">Select subject</option>
                        <option value="Mathematics"> Mathematics</option>
                        <option value="Physics"> Physics</option>
                        <option value="PHP"> PHP</option>
                        <option value="Web Development"> Web Development</option>
                        <option value="Database"> Database</option>
                        <option value="English"> English</option>
                        <option value="Computer Science"> Computer Science</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mode</label>
                    <select name="mode">
                        <option value="In Person"> In Person</option>
                        <option value="Online"> Online</option>
                        <option value="Hybrid"> Hybrid</option>
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Seat #</label>
                    <input type="text" name="seat" placeholder="e.g., A12">
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" placeholder="e.g., Room 101">
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class">
                        <option value="">Select class</option>
                        <option value="L1">L1 - First Year</option>
                        <option value="L2">L2 - Second Year</option>
                        <option value="L3">L3 - Third Year</option>
                        <option value="M1">M1 - Master 1</option>
                        <option value="M2">M2 - Master 2</option>
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="time">
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration" placeholder="e.g., 120">
                </div>
            </div>

            <div class="form-actions">
                <a href="exams.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Exam</button>
            </div>
        </form>
    </div>
</main>

<script>
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

    // Set default date to next week
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    document.querySelector('input[name="date"]').value = nextWeek.toISOString().split('T')[0];
</script>

</body>
</html>
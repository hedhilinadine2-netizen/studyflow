<?php
require_once 'includes/config.php';
requireLogin();

$error = '';
$vacation = null;

$vacation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vacation_id) {
    header("Location: vacations.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vacations WHERE id = ? AND user_id = ?");
$stmt->execute([$vacation_id, $_SESSION['user_id']]);
$vacation = $stmt->fetch();

if (!$vacation) {
    header("Location: vacations.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $destination = isset($_POST['destination']) ? trim($_POST['destination']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    if (empty($title) || empty($start_date) || empty($end_date)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE vacations SET title = ?, destination = ?, start_date = ?, end_date = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $destination, $start_date, $end_date, $description, $vacation_id]);
            header("Location: vacations.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error updating vacation: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vacation - StudyFlow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f7f8fc; color: #1a1a2e; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background: white; border-right: 1px solid #e8ecf2; padding: 24px 0; z-index: 100; }
        .sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 0 20px 24px; border-bottom: 1px solid #e8ecf2; margin-bottom: 20px; }
        .brand-icon { width: 38px; height: 38px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; }
        .brand-name { font-size: 1.3rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-menu { padding: 0 12px; }
        .s-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 12px; color: #5a5a7a; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; margin-bottom: 4px; }
        .s-link:hover, .s-link.active { background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); color: #667eea; }
        .sidebar-bottom { position: absolute; bottom: 24px; left: 0; right: 0; padding: 0 20px; }
        .logout-link { display: flex; align-items: center; gap: 8px; justify-content: center; color: #ef4444; text-decoration: none; font-size: 0.85rem; padding: 10px; border-radius: 10px; background: #fef2f2; }
        .main { margin-left: 260px; padding: 24px 32px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .time-date { font-size: 0.85rem; color: #8b8aa8; }
        .time { font-size: 1.2rem; font-weight: 600; }
        .user-info { text-align: right; }
        .user-name { font-weight: 600; }
        .user-email { font-size: 0.75rem; color: #8b8aa8; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; border-radius: 24px; border: 1px solid #e8ecf2; padding: 32px; }
        .form-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .form-subtitle { color: #8b8aa8; font-size: 0.85rem; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e8ecf2; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; }
        .form-group label .required { color: #ef4444; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 16px; border: 1px solid #e8ecf2; border-radius: 12px; font-size: 0.9rem; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; }
        .alert-error { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e8ecf2; }
        .btn-save { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 28px; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: #f0f2f9; color: #5a5a7a; padding: 12px 28px; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">SF</div>
        <div class="brand-name">StudyFlow</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="s-link">Dashboard</a>
        <a href="calendar.php" class="s-link">Calendar</a>
        <a href="tasks.php" class="s-link">Tasks</a>
        <a href="exams.php" class="s-link">Exams</a>
        <a href="classes.php" class="s-link">Classes</a>
        <a href="vacations.php" class="s-link active">Vacations</a>
    </nav>
    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-link">Déconnexion</a>
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
        <h1 class="form-title">Edit Vacation</h1>
        <div class="form-subtitle">Update your vacation details</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Vacation Title <span class="required">*</span></label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($vacation['title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Destination</label>
                <input type="text" name="destination" value="<?php echo htmlspecialchars($vacation['destination'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Start Date <span class="required">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($vacation['start_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label>End Date <span class="required">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($vacation['end_date']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($vacation['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="vacations.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Changes</button>
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
</script>

</body>
</html>
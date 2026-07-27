<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// --------------------------------------------------------
// Stats
// --------------------------------------------------------
$total = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$today = $conn->query("SELECT COUNT(*) AS c FROM students WHERE DATE(registered_at) = CURDATE()")->fetch_assoc()['c'];
$thisWeek = $conn->query("SELECT COUNT(*) AS c FROM students WHERE YEARWEEK(registered_at, 1) = YEARWEEK(CURDATE(), 1)")->fetch_assoc()['c'];
$courseCountResult = $conn->query("SELECT COUNT(DISTINCT course) AS c FROM students");
$courseCount = $courseCountResult->fetch_assoc()['c'];

// --------------------------------------------------------
// Search / filter
// --------------------------------------------------------
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT id, full_name, email, phone, gender, course, registered_at
         FROM students
         WHERE full_name LIKE ? OR email LIKE ? OR course LIKE ?
         ORDER BY registered_at DESC"
    );
    $like = "%$search%";
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $conn->query(
        "SELECT id, full_name, email, phone, gender, course, registered_at
         FROM students ORDER BY registered_at DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Student Registration Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="seal-row">
            <div class="seal">SR</div>
            <span>Student Registration</span>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="../index.php">View Public Form</a>
        </nav>
        <div class="logout">
            <a href="logout.php">Log out (<?= htmlspecialchars($_SESSION['admin_username']) ?>)</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <div>
                <div class="kicker">Admin Panel</div>
                <h1>Registered Students</h1>
            </div>
            <div class="date"><?= date('l, d F Y') ?></div>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="label">Total Registered</div>
                <div class="value"><?= (int)$total ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Registered Today</div>
                <div class="value"><?= (int)$today ?></div>
            </div>
            <div class="stat-card">
                <div class="label">This Week</div>
                <div class="value"><?= (int)$thisWeek ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Courses Represented</div>
                <div class="value"><?= (int)$courseCount ?></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-head">
                <h3>All Students</h3>
                <form method="GET" class="search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#5b6172" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="#5b6172" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="text" name="search" placeholder="Search name, email, course…" value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>

            <?php if ($students->num_rows === 0): ?>
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 21c0-4 4-6 8-6s8 2 8 6" stroke="#5b6172" stroke-width="1.6"/></svg>
                    <p><?= $search !== '' ? 'No students match your search.' : 'No students have registered yet.' ?></p>
                </div>
            <?php else: ?>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="avatar-chip">
                                        <div class="circle"><?= strtoupper(substr($row['full_name'], 0, 1)) ?></div>
                                        <div>
                                            <div style="font-weight:600;"><?= htmlspecialchars($row['full_name']) ?></div>
                                            <div style="color:var(--ink-soft); font-size:12px;">STU-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($row['email']) ?></div>
                                    <div style="color:var(--ink-soft); font-size:12px;"><?= htmlspecialchars($row['phone']) ?></div>
                                </td>
                                <td><span class="badge gender-<?= htmlspecialchars($row['gender']) ?>"><?= htmlspecialchars($row['gender']) ?></span></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($row['registered_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>

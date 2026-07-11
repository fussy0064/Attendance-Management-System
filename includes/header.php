<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Attendance Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">📋 Attendance Management System</div>
    <?php if (Auth::check()): ?>
    <nav class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="mark_attendance.php">Mark Attendance</a>
        <a href="view_reports.php">Reports</a>
        <?php if (Auth::role() === 'admin'): ?>
            <a href="manage_persons.php">Students/Employees</a>
            <a href="manage_users.php">Users</a>
        <?php endif; ?>
        <span class="whoami">👤 <?= htmlspecialchars(Auth::username()) ?> (<?= htmlspecialchars(Auth::role()) ?>)</span>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
    <?php endif; ?>
</header>
<main class="container">

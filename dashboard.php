<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/repositories/PersonRepository.php';
require_once __DIR__ . '/repositories/AttendanceRepository.php';
Auth::requireLogin();

// Rebuild the logged-in user object so we can call the polymorphic
// getAllowedActions() method to decide what this dashboard shows.
require_once __DIR__ . '/repositories/UserRepository.php';
$userRepo = new UserRepository();
$currentUser = $userRepo->findByUsername(Auth::username());
$actions = $currentUser->getAllowedActions();

$personRepo = new PersonRepository();
$attendanceRepo = new AttendanceRepository();

$totalPersons = count($personRepo->all());
$todayRecords = $attendanceRepo->getByDate(date('Y-m-d'));
$presentToday = count(array_filter($todayRecords, fn($r) => $r['status'] === 'Present'));
$absentToday  = count(array_filter($todayRecords, fn($r) => $r['status'] === 'Absent'));
$leaveToday   = count(array_filter($todayRecords, fn($r) => $r['status'] === 'Leave'));

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Welcome, <?= htmlspecialchars(Auth::username()) ?> !</h1>
<p class="badge">Role: <?= htmlspecialchars(Auth::role()) ?></p>

<div class="card">
    <h2>Today's Overview (<?= date('d M Y') ?>)</h2>
    <table>
        <tr>
            <th>Total Students/Employees</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Leave</th>
        </tr>
        <tr>
            <td><?= $totalPersons ?></td>
            <td class="status-present"><?= $presentToday ?></td>
            <td class="status-absent"><?= $absentToday ?></td>
            <td class="status-leave"><?= $leaveToday ?></td>
        </tr>
    </table>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <p>
        <a class="btn" href="mark_attendance.php">Mark Attendance</a>
        <a class="btn btn-secondary" href="view_reports.php">View Reports</a>
        <?php if (in_array('manage_persons', $actions)): ?>
            <a class="btn btn-secondary" href="manage_persons.php">Manage Students/Employees</a>
        <?php endif; ?>
        <?php if (in_array('manage_users', $actions)): ?>
            <a class="btn btn-secondary" href="manage_users.php">Manage Users</a>
        <?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

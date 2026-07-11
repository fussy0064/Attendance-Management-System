<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/repositories/PersonRepository.php';
require_once __DIR__ . '/repositories/AttendanceRepository.php';
Auth::requireLogin();

$personRepo = new PersonRepository();
$attendanceRepo = new AttendanceRepository();
$success = '';

$date = $_GET['date'] ?? date('Y-m-d');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['attendance_date'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? [];

    foreach ($statuses as $personId => $status) {
        if (in_array($status, ['Present', 'Absent', 'Leave'], true)) {
            $attendanceRepo->markAttendance((int) $personId, $date, $status, Auth::userId());
        }
    }
    $success = 'Attendance saved for ' . htmlspecialchars($date) . '.';
}

$persons = $personRepo->all();
$existing = $attendanceRepo->getExistingStatusesForDate($date);

$pageTitle = 'Mark Attendance';
require __DIR__ . '/includes/header.php';
?>
<h1>Mark Attendance</h1>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="card">
    <form method="GET" action="mark_attendance.php" class="filters">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        </div>
        <button type="submit" class="btn btn-secondary">Load Date</button>
    </form>
</div>

<div class="card">
    <form method="POST" action="mark_attendance.php">
        <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($date) ?>">
        <table>
            <tr><th>Name</th><th>ID Number</th><th>Course/Dept</th><th>Status</th></tr>
            <?php foreach ($persons as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p->getFullName()) ?></td>
                <td><?= htmlspecialchars($p->getIdNumber()) ?></td>
                <td><?= htmlspecialchars($p->getCategoryLabel()) ?></td>
                <td>
                    <?php $current = $existing[$p->getId()] ?? ''; ?>
                    <select name="status[<?= $p->getId() ?>]">
                        <option value="">-- Select --</option>
                        <option value="Present" <?= $current === 'Present' ? 'selected' : '' ?>>Present</option>
                        <option value="Absent" <?= $current === 'Absent' ? 'selected' : '' ?>>Absent</option>
                        <option value="Leave" <?= $current === 'Leave' ? 'selected' : '' ?>>Leave</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($persons)): ?>
            <tr><td colspan="4">No students/employees found. Add some first.</td></tr>
            <?php endif; ?>
        </table>
        <?php if (!empty($persons)): ?>
            <br><button type="submit" class="btn">Save Attendance</button>
        <?php endif; ?>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

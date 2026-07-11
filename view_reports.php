<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/repositories/AttendanceRepository.php';
Auth::requireLogin();

$attendanceRepo = new AttendanceRepository();

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$nameFilter = trim($_GET['name'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$records = $attendanceRepo->getByDateRange($from, $to);

if ($nameFilter !== '') {
    $needle = mb_strtolower($nameFilter);
    $records = array_values(array_filter($records, fn($r) => str_contains(mb_strtolower($r['full_name']), $needle)));
}
if ($statusFilter !== '') {
    $records = array_values(array_filter($records, fn($r) => $r['status'] === $statusFilter));
}

$pageTitle = 'Attendance Reports';
require __DIR__ . '/includes/header.php';
?>
<h1>Attendance Reports</h1>

<div class="card">
    <form method="GET" action="view_reports.php" class="filters">
        <div class="form-group">
            <label>From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
        </div>
        <div class="form-group">
            <label>To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
        </div>
        <div class="form-group">
            <label>Name contains</label>
            <input type="text" name="name" value="<?= htmlspecialchars($nameFilter) ?>" placeholder="e.g. John">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="Present" <?= $statusFilter === 'Present' ? 'selected' : '' ?>>Present</option>
                <option value="Absent" <?= $statusFilter === 'Absent' ? 'selected' : '' ?>>Absent</option>
                <option value="Leave" <?= $statusFilter === 'Leave' ? 'selected' : '' ?>>Leave</option>
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        <a class="btn btn-secondary" href="export_csv.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&name=<?= urlencode($nameFilter) ?>&status=<?= urlencode($statusFilter) ?>">Export CSV</a>
    </form>
</div>

<div class="card">
    <h2>Results (<?= count($records) ?> records)</h2>
    <table>
        <tr><th>Date</th><th>Name</th><th>ID Number</th><th>Type</th><th>Course/Dept</th><th>Status</th><th>Marked By</th></tr>
        <?php foreach ($records as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['attendance_date']) ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['id_number']) ?></td>
            <td><?= ucfirst($r['person_type']) ?></td>
            <td><?= htmlspecialchars($r['category']) ?></td>
            <td class="status-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></td>
            <td><?= htmlspecialchars($r['marked_by_username']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($records)): ?>
        <tr><td colspan="7">No records match your filters.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_report_' . $from . '_to_' . $to . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Full Name', 'ID Number', 'Type', 'Course/Department', 'Status', 'Marked By']);

foreach ($records as $r) {
    fputcsv($out, [
        $r['attendance_date'],
        $r['full_name'],
        $r['id_number'],
        ucfirst($r['person_type']),
        $r['category'],
        $r['status'],
        $r['marked_by_username'],
    ]);
}

fclose($out);
exit;

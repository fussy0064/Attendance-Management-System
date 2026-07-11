<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Encryption.php';

/**
 * AttendanceRepository
 * Handles marking and querying attendance records.
 * Even the status value ("Present"/"Absent"/"Leave") is encrypted at
 * rest, per the assignment's "all row data encrypted" requirement.
 */
class AttendanceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Marks (inserts or updates) attendance for one person on one date.
     */
    public function markAttendance(int $personId, string $date, string $status, int $markedBy): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attendance (person_id, attendance_date, status_enc, marked_by)
             VALUES (:person_id, :date, :status, :marked_by)
             ON DUPLICATE KEY UPDATE status_enc = :status2, marked_by = :marked_by2'
        );
        $encStatus = Encryption::encrypt($status);
        $stmt->execute([
            'person_id'  => $personId,
            'date'       => $date,
            'status'     => $encStatus,
            'marked_by'  => $markedBy,
            'status2'    => $encStatus,
            'marked_by2' => $markedBy,
        ]);
    }

    private function decorateRow(array $row): array
    {
        $row['full_name'] = Encryption::decrypt($row['full_name_enc']);
        $row['id_number'] = Encryption::decrypt($row['id_number_enc']);
        $row['category']  = Encryption::decrypt($row['category_enc']);
        $row['status']    = Encryption::decrypt($row['status_enc']);
        return $row;
    }

    private function baseQuery(): string
    {
        return 'SELECT a.id, a.attendance_date, a.status_enc, a.person_id,
                        p.person_type, p.full_name_enc, p.id_number_enc, p.category_enc,
                        u.username AS marked_by_username
                 FROM attendance a
                 JOIN persons p ON p.id = a.person_id
                 JOIN users u ON u.id = a.marked_by';
    }

    public function getByDate(string $date): array
    {
        $stmt = $this->db->prepare($this->baseQuery() . ' WHERE a.attendance_date = :date ORDER BY p.id ASC');
        $stmt->execute(['date' => $date]);
        return array_map(fn($r) => $this->decorateRow($r), $stmt->fetchAll());
    }

    public function getByDateRange(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            $this->baseQuery() . ' WHERE a.attendance_date BETWEEN :from AND :to ORDER BY a.attendance_date DESC, p.id ASC'
        );
        $stmt->execute(['from' => $from, 'to' => $to]);
        return array_map(fn($r) => $this->decorateRow($r), $stmt->fetchAll());
    }

    public function getExistingStatusesForDate(string $date): array
    {
        // person_id => decrypted status, useful to pre-fill the marking form
        $rows = $this->getByDate($date);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['person_id']] = $row['status'];
        }
        return $map;
    }
}

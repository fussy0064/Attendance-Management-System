<?php
require_once __DIR__ . '/User.php';

class Teacher extends User
{
    public function getRole(): string { return 'teacher'; }

    public function getAllowedActions(): array
    {
        return ['mark_attendance', 'view_reports'];
    }
}

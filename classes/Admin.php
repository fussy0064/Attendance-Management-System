<?php
require_once __DIR__ . '/User.php';

class Admin extends User
{
    public function getRole(): string { return 'admin'; }

    public function getAllowedActions(): array
    {
        return ['manage_users', 'manage_persons', 'mark_attendance', 'view_reports'];
    }
}

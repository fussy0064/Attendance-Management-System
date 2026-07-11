<?php
require_once __DIR__ . '/Person.php';

class Employee extends Person
{
    private string $department;

    public function __construct(string $fullName, string $idNumber, string $email, string $phone, string $department, ?int $id = null)
    {
        parent::__construct($fullName, $idNumber, $email, $phone, $id);
        $this->department = $department;
    }

    public function getType(): string { return 'employee'; }
    public function getCategoryLabel(): string { return $this->department; }
    public function getDepartment(): string { return $this->department; }
    public function setDepartment(string $v): void { $this->department = $v; }
}

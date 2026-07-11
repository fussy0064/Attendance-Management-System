<?php
require_once __DIR__ . '/Person.php';

class Student extends Person
{
    private string $course;

    public function __construct(string $fullName, string $idNumber, string $email, string $phone, string $course, ?int $id = null)
    {
        parent::__construct($fullName, $idNumber, $email, $phone, $id);
        $this->course = $course;
    }

    public function getType(): string { return 'student'; }
    public function getCategoryLabel(): string { return $this->course; }
    public function getCourse(): string { return $this->course; }
    public function setCourse(string $v): void { $this->course = $v; }
}

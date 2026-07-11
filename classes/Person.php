<?php

/**
 * Person (abstract)
 * Base class for anyone whose attendance can be tracked: Student or Employee.
 * Demonstrates abstraction (cannot be instantiated directly) and
 * encapsulation (private properties only reachable via getters/setters).
 */
abstract class Person
{
    private ?int $id;
    private string $fullName;
    private string $idNumber;
    private string $email;
    private string $phone;

    public function __construct(string $fullName, string $idNumber, string $email, string $phone, ?int $id = null)
    {
        $this->id       = $id;
        $this->fullName = $fullName;
        $this->idNumber = $idNumber;
        $this->email    = $email;
        $this->phone    = $phone;
    }

    // Every subclass must define what "type" it is and its category label
    // (course for a Student, department for an Employee) -> polymorphism.
    abstract public function getType(): string;
    abstract public function getCategoryLabel(): string;

    public function getId(): ?int { return $this->id; }
    public function getFullName(): string { return $this->fullName; }
    public function getIdNumber(): string { return $this->idNumber; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }

    public function setFullName(string $v): void { $this->fullName = $v; }
    public function setIdNumber(string $v): void { $this->idNumber = $v; }
    public function setEmail(string $v): void { $this->email = $v; }
    public function setPhone(string $v): void { $this->phone = $v; }
}

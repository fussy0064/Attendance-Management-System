<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Encryption.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/Employee.php';

/**
 * PersonRepository
 * All sensitive columns (name, id number, email, phone, course/department)
 * are encrypted before INSERT/UPDATE and decrypted after SELECT.
 */
class PersonRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function hydrate(array $row): Student|Employee
    {
        $fullName = Encryption::decrypt($row['full_name_enc']);
        $idNumber = Encryption::decrypt($row['id_number_enc']);
        $email    = Encryption::decrypt($row['email_enc']);
        $phone    = Encryption::decrypt($row['phone_enc']);
        $category = Encryption::decrypt($row['category_enc']);
        $id       = (int) $row['id'];

        return $row['person_type'] === 'student'
            ? new Student($fullName, $idNumber, $email, $phone, $category, $id)
            : new Employee($fullName, $idNumber, $email, $phone, $category, $id);
    }

    public function create(Student|Employee $person): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO persons (person_type, full_name_enc, id_number_enc, email_enc, phone_enc, category_enc)
             VALUES (:type, :full_name, :id_number, :email, :phone, :category)'
        );
        $stmt->execute([
            'type'      => $person->getType(),
            'full_name' => Encryption::encrypt($person->getFullName()),
            'id_number' => Encryption::encrypt($person->getIdNumber()),
            'email'     => Encryption::encrypt($person->getEmail()),
            'phone'     => Encryption::encrypt($person->getPhone()),
            'category'  => Encryption::encrypt($person->getCategoryLabel()),
        ]);
    }

    public function update(Student|Employee $person): void
    {
        $stmt = $this->db->prepare(
            'UPDATE persons SET full_name_enc = :full_name, id_number_enc = :id_number,
             email_enc = :email, phone_enc = :phone, category_enc = :category
             WHERE id = :id'
        );
        $stmt->execute([
            'full_name' => Encryption::encrypt($person->getFullName()),
            'id_number' => Encryption::encrypt($person->getIdNumber()),
            'email'     => Encryption::encrypt($person->getEmail()),
            'phone'     => Encryption::encrypt($person->getPhone()),
            'category'  => Encryption::encrypt($person->getCategoryLabel()),
            'id'        => $person->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM persons WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function findById(int $id): Student|Employee|null
    {
        $stmt = $this->db->prepare('SELECT * FROM persons WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM persons ORDER BY id DESC');
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll());
    }

    /**
     * Search by name/ID number. Because these fields are encrypted at
     * rest, they can't be matched with a SQL WHERE/LIKE. Instead we
     * decrypt every row (typical dataset for a class/department is
     * small) and filter in PHP.
     */
    public function search(string $term): array
    {
        $term = mb_strtolower(trim($term));
        if ($term === '') {
            return $this->all();
        }

        return array_values(array_filter($this->all(), function ($person) use ($term) {
            return str_contains(mb_strtolower($person->getFullName()), $term)
                || str_contains(mb_strtolower($person->getIdNumber()), $term);
        }));
    }

    /**
     * Checks if an ID number is already used by another person.
     * Because id_number is encrypted at rest, this can't be a SQL
     * WHERE check, so we decrypt and compare in PHP (same approach
     * as search()). $excludeId lets an edit ignore its own record.
     */
    public function idNumberExists(string $idNumber, ?int $excludeId = null): bool
    {
        $idNumber = trim($idNumber);
        foreach ($this->all() as $person) {
            if ($excludeId !== null && $person->getId() === $excludeId) {
                continue;
            }
            if (strcasecmp($person->getIdNumber(), $idNumber) === 0) {
                return true;
            }
        }
        return false;
    }
}

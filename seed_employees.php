<?php
// Run once from project root: php seed_employees.php
// Adds 4 sample Employees using the app's own classes,
// so data is encrypted exactly like data added via the UI.

require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Encryption.php';
require_once __DIR__ . '/classes/Person.php';
require_once __DIR__ . '/classes/Employee.php';
require_once __DIR__ . '/repositories/PersonRepository.php';

$employees = [
    ['John Mushi',    'EMP-1001', 'john.mushi@example.com',    '0712345001', 'Finance'],
    ['Grace Kileo',   'EMP-1002', 'grace.kileo@example.com',   '0712345002', 'Human Resources'],
    ['Peter Mnyika',  'EMP-1003', 'peter.mnyika@example.com',  '0712345003', 'IT Department'],
    ['Amina Juma',    'EMP-1004', 'amina.juma@example.com',    '0712345004', 'Administration'],
];

$repo = new PersonRepository();

foreach ($employees as [$name, $idNumber, $email, $phone, $department]) {
    $repo->create(new Employee($name, $idNumber, $email, $phone, $department));
    echo "Added employee: $name ($department)\n";
}

echo "Done. " . count($employees) . " employees seeded.\n";

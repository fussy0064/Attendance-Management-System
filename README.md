# Attendance Management System

A web-based Attendance Management System built with **plain PHP (OOP) and MySQL** —
no frameworks — for the CBE BIT2 "Internet and Web Development" individual assignment.

It lets Admins and Teachers register students/employees, mark daily attendance,
search and filter records, generate/export reports, and manage system users,
with sensitive data encrypted at rest.

## Features

- User registration & login (Admin / Teacher roles) with session-based auth
- Add / edit / delete students and employees
- Mark daily attendance (Present / Absent / Leave)
- Search & filter attendance records by date range, name, and status
- Export filtered attendance reports to CSV
- AES-256-CBC encryption of all sensitive row data before it is stored
- Role-based access control (Admins manage users/persons; Teachers mark attendance and view reports)

## Tech Stack

- **Backend:** PHP 8+ (OOP, PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, vanilla JavaScript
- **No frameworks** (no Laravel/CodeIgniter/etc.) per assignment requirements

## Object-Oriented Design

| Concept | Where it's demonstrated |
|---|---|
| **Abstraction** | `Person` and `User` are abstract classes; they cannot be instantiated directly and define abstract methods subclasses must implement. |
| **Inheritance** | `Student` and `Employee` extend `Person`. `Admin` and `Teacher` extend `User`. |
| **Encapsulation** | All entity properties are `private`, only reachable through getters/setters. The `Database` connection is also encapsulated behind a singleton. |
| **Polymorphism** | `getType()`/`getCategoryLabel()` behave differently per subclass of `Person`. `getAllowedActions()` behaves differently per subclass of `User`, driving what the dashboard/nav shows without any `if ($role === ...)` branching at the call site. `UserFactory` returns the correct subtype based on DB data. |
| **Constructors** | Every class defines an explicit `__construct()`, including `parent::__construct()` calls in subclasses. |

## Database Design (3NF)

Three tables: `users`, `persons`, `attendance`.

- Every table has a single-column primary key (`id`).
- No repeating groups; `attendance` does not duplicate a person's name/department —
  it stores only `person_id` as a foreign key back to `persons`.
- No transitive dependencies: non-key columns depend only on each table's primary key.

See [`sql/schema.sql`](sql/schema.sql) for the full DDL.

## Encryption Approach & Key Management

Per the assignment's requirement that "all row data stored in database tables shall
be encrypted before being persisted," every sensitive column (`*_enc` suffix) is
encrypted with **AES-256-CBC** via `classes/Encryption.php` before `INSERT`/`UPDATE`,
and decrypted immediately after `SELECT` inside the repository layer — application
code above the repositories only ever sees plaintext.

- **Key derivation:** `Config::ENCRYPTION_KEY` (a configured secret) is stretched to
  a 256-bit key using `hash('sha256', ..., true)`.
- **IV handling:** a fresh random IV is generated for every value encrypted, so the
  same plaintext never produces the same ciphertext twice. The IV is prepended to
  the ciphertext and base64-encoded as a single stored string; the IV itself isn't
  secret, only the key is.
- **Passwords** are hashed with PHP's `password_hash()` (bcrypt), not encrypted —
  hashing is one-way and correct for credentials, whereas encryption (reversible)
  is used for data that must be displayed back to the user (names, emails, etc.).
- **Key storage:** in this submission the key lives in `config/Config.php` for
  simplicity. In a real deployment it should be moved to an environment variable
  (e.g. read via `getenv('ENCRYPTION_KEY')`) and excluded from version control.
- **Trade-off documented:** because names/ID numbers are encrypted, they cannot be
  searched with SQL `LIKE`. Search-by-name filters decrypt rows in PHP after a
  narrower SQL fetch (by date range) — acceptable for typical class/department
  dataset sizes; a larger-scale system would use a separate searchable hash/index.

## Setup Instructions

1. Create the database:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Update `config/Config.php` with your DB credentials and a strong `ENCRYPTION_KEY`.
3. Seed a default admin account:
   ```bash
   php seed.php
   ```
4. Serve the app (development):
   ```bash
   php -S localhost:8000
   ```
   Or deploy to Apache/Nginx + PHP-FPM on AWS EC2.
5. Log in at `/index.php` with the seeded admin credentials, then create your own
   admin/teacher accounts from **Manage Users** and remove the default one.

## Project Structure

```
config/            Config.php, Database.php (PDO singleton)
classes/           Encryption, Person/Student/Employee, User/Admin/Teacher, UserFactory, Auth
repositories/       UserRepository, PersonRepository, AttendanceRepository (encryption boundary)
includes/          Shared header/footer templates
assets/            CSS and JS
sql/schema.sql     3NF database schema
seed.php           Creates the default admin account
index.php          Login
dashboard.php      Role-based landing page
manage_persons.php Student/Employee CRUD (admin only)
manage_users.php   User account CRUD (admin only)
mark_attendance.php  Daily attendance marking
view_reports.php   Search/filter attendance + CSV export link
export_csv.php     CSV export endpoint
```

## Security Notes

- Passwords hashed with bcrypt (`password_hash`/`password_verify`).
- All DB access via PDO prepared statements (parameterized queries) — no raw
  string interpolation into SQL.
- Session regenerated on login to prevent session fixation.
- Role checks (`Auth::requireRole()`) gate admin-only pages server-side.
- Input validated (email format, Tanzanian phone number pattern) before storage.
- Sensitive row data encrypted at rest as described above.

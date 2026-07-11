<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/repositories/PersonRepository.php';
require_once __DIR__ . '/classes/Student.php';
require_once __DIR__ . '/classes/Employee.php';
Auth::requireRole('admin');

$repo = new PersonRepository();
$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $repo->delete((int) $_GET['delete']);
    header('Location: manage_persons.php?deleted=1');
    exit;
}
if (isset($_GET['deleted'])) {
    $success = 'Record deleted successfully.';
}

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['id'] ?? '';
    $type      = $_POST['person_type'] ?? '';
    $fullName  = trim($_POST['full_name'] ?? '');
    $idNumber  = trim($_POST['id_number'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $category  = trim($_POST['category'] ?? '');

    if ($fullName === '' || $idNumber === '' || $email === '' || $phone === '' || $category === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (!preg_match('/^(0|\+255)[67]\d{8}$/', $phone)) {
        $error = 'Please provide a valid Tanzanian phone number (e.g. 0712345678 or +255712345678).';
    } else {
        $personId = $id !== '' ? (int) $id : null;

        $person = $type === 'student'
            ? new Student($fullName, $idNumber, $email, $phone, $category, $personId)
            : new Employee($fullName, $idNumber, $email, $phone, $category, $personId);

        if ($personId) {
            $repo->update($person);
            $success = 'Record updated successfully.';
        } else {
            $repo->create($person);
            $success = 'Record added successfully.';
        }
    }
}

// Handle edit-load
$editing = null;
if (isset($_GET['edit'])) {
    $editing = $repo->findById((int) $_GET['edit']);
}

$searchTerm = trim($_GET['q'] ?? '');
$persons = $searchTerm !== '' ? $repo->search($searchTerm) : $repo->all();

$pageTitle = 'Manage Students / Employees';
require __DIR__ . '/includes/header.php';
?>
<h1>Manage Students &amp; Employees</h1>

<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="card">
    <h2><?= $editing ? 'Edit Record' : 'Add New Student / Employee' ?></h2>
    <form method="POST" action="manage_persons.php">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= $editing->getId() ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>Type</label>
            <select name="person_type" required <?= $editing ? 'disabled' : '' ?>>
                <option value="student" <?= ($editing instanceof Student) ? 'selected' : '' ?>>Student</option>
                <option value="employee" <?= ($editing instanceof Employee) ? 'selected' : '' ?>>Employee</option>
            </select>
            <?php if ($editing): ?>
                <input type="hidden" name="person_type" value="<?= $editing->getType() ?>">
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= $editing ? htmlspecialchars($editing->getFullName()) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>ID Number (Reg No. / Employee No.)</label>
            <input type="text" name="id_number" value="<?= $editing ? htmlspecialchars($editing->getIdNumber()) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= $editing ? htmlspecialchars($editing->getEmail()) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Phone (e.g. 0712345678)</label>
            <input type="text" name="phone" value="<?= $editing ? htmlspecialchars($editing->getPhone()) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Course (student) / Department (employee)</label>
            <input type="text" name="category" value="<?= $editing ? htmlspecialchars($editing->getCategoryLabel()) : '' ?>" required>
        </div>
        <button type="submit" class="btn"><?= $editing ? 'Update' : 'Add' ?></button>
        <?php if ($editing): ?><a href="manage_persons.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All Records</h2>
    <form method="GET" action="manage_persons.php" class="filters">
        <div class="form-group">
            <label>Search by name or ID number</label>
            <input type="text" name="q" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="e.g. John or E001">
        </div>
        <button type="submit" class="btn">Search</button>
        <?php if ($searchTerm !== ''): ?><a href="manage_persons.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>

    <table>
        <tr>
            <th>ID</th><th>Type</th><th>Full Name</th><th>ID Number</th><th>Email</th><th>Phone</th><th>Course/Dept</th><th>Actions</th>
        </tr>
        <?php foreach ($persons as $p): ?>
        <tr>
            <td><?= $p->getId() ?></td>
            <td><?= ucfirst($p->getType()) ?></td>
            <td><?= htmlspecialchars($p->getFullName()) ?></td>
            <td><?= htmlspecialchars($p->getIdNumber()) ?></td>
            <td><?= htmlspecialchars($p->getEmail()) ?></td>
            <td><?= htmlspecialchars($p->getPhone()) ?></td>
            <td><?= htmlspecialchars($p->getCategoryLabel()) ?></td>
            <td>
                <a class="btn btn-sm" href="manage_persons.php?edit=<?= $p->getId() ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="manage_persons.php?delete=<?= $p->getId() ?>" data-confirm="Delete this record? This will also remove its attendance history.">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($persons)): ?>
        <tr><td colspan="8">No records found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

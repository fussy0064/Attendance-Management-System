<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/repositories/UserRepository.php';
Auth::requireRole('admin');

$repo = new UserRepository();
$error = '';
$success = '';

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    if ($deleteId === Auth::userId()) {
        $error = 'You cannot delete your own account while logged in.';
    } else {
        $repo->delete($deleteId);
        header('Location: manage_users.php?deleted=1');
        exit;
    }
}
if (isset($_GET['deleted'])) {
    $success = 'User deleted successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'teacher';

    if ($username === '' || $password === '' || $email === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($repo->usernameExists($username)) {
        $error = 'That username is already taken.';
    } else {
        $repo->create($username, $password, $email, $role);
        $success = 'User account created successfully.';
    }
}

$users = $repo->all();

$pageTitle = 'Manage Users';
require __DIR__ . '/includes/header.php';
?>
<h1>Manage System Users</h1>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="card">
    <h2>Add New User (Admin / Teacher)</h2>
    <form method="POST" action="manage_users.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn">Create User</button>
    </form>
</div>

<div class="card">
    <h2>All Users</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u->getId() ?></td>
            <td><?= htmlspecialchars($u->getUsername()) ?></td>
            <td><?= htmlspecialchars($u->getEmail()) ?></td>
            <td><?= htmlspecialchars($u->getRole()) ?></td>
            <td>
                <?php if ($u->getId() !== Auth::userId()): ?>
                <a class="btn btn-sm btn-danger" href="manage_users.php?delete=<?= $u->getId() ?>" data-confirm="Delete this user account?">Delete</a>
                <?php else: ?>
                <span class="badge">You</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

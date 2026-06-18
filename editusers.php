<?php
session_start();

include("connect.php");   // $conn (MySQLi)
include("loggedin.php");
include("functions.php");

echo '<title>User</title></head><body>';
include("tables.php");
$conn = getConnection();

$isCreate = (isset($_GET['action']) && $_GET['action'] === 'create') ||
            (isset($_POST['action']) && $_POST['action'] === 'create');

/**
 * =========================
 * SHOW FORM
 * =========================
 */
if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	// --- EDIT MODE ---
	$id = clean($_GET['id']);

	if (empty($id) || !is_numeric($id)) {
		die('<div class="message message-error">Invalid request.</div>');
	}

	$stmt = $conn->prepare("SELECT id, name, admin FROM users WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		die('<div class="message message-error">User not found.</div>');
	}

	$row = $result->fetch_assoc();

	echo '
<div class="card">
    <div class="card-header">Edit User</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="name">Username</label>
                <input type="text" name="name" id="name" class="form-input" value="' . htmlspecialchars($row['name']) . '" data-rules=\'{"required":true,"minLength":2}\'>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password (leave blank to keep current)</label>
                <input type="password" name="password" id="password" class="form-input" data-rules=\'{"minLength":3}\'>
            </div>

            <div class="form-row">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="admin"> Make Admin (Admin only)
                </label>
            </div>

            <div class="form-row" style="margin-top:0.5rem;">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="delete"> Delete this user
                </label>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Update Details" class="btn btn-primary">
                <a href="users.php" class="btn btn-secondary">Cancel</a>
                <input type="hidden" name="id" value="' . $id . '">
                <input type="hidden" name="action" value="update">
            </div>

        </form>
    </div>
</div>';

} elseif ($isCreate && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	// --- CREATE MODE ---
	admin();

	echo '
<div class="card">
    <div class="card-header">Create User</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="name">Username</label>
                <input type="text" name="name" id="name" class="form-input" placeholder="Enter username" data-rules=\'{"required":true,"minLength":2}\'>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-input" data-rules=\'{"required":true,"minLength":3}\'>
            </div>

            <div class="form-row">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="admin"> Make Admin
                </label>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Create User" class="btn btn-primary">
                <a href="users.php" class="btn btn-secondary">Cancel</a>
                <input type="hidden" name="action" value="create">
            </div>

        </form>
    </div>
</div>';

/**
 * =========================
 * HANDLE POST
 * =========================
 */
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$action   = $_POST['action'] ?? '';
	$id       = clean($_POST['id'] ?? '');
	$name     = clean($_POST['name'] ?? '');
	$password = clean($_POST['password'] ?? '');

	$delete = isset($_POST['delete']);
	$admin  = isset($_POST['admin']);

	/**
	 * CREATE
	 */
	if ($action === 'create') {
		admin();

		if (empty($name) || empty($password)) {
			die('<div class="message message-error">Username and password are required.</div>');
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$adminVal = $admin ? 1 : 0;

		$stmt = $conn->prepare("INSERT INTO users (name, password, admin, date, ip) VALUES (?, ?, ?, '', '')");
		$stmt->bind_param("ssi", $name, $hashedPassword, $adminVal);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'User created successfully.']; header("Location: users.php"); exit;
	}

	/**
	 * UPDATE / DELETE — require id
	 */
	if (empty($id) || !is_numeric($id)) {
		die('<div class="message message-error">Invalid ID.</div>');
	}

	if ($delete && $admin) {
		die('<div class="message message-error">You cannot make a user admin while deleting them.</div>');
	}

	if ($delete) {
		admin();

		$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'User successfully deleted.']; header("Location: users.php"); exit;
	}

	if ($admin) {
		admin();

		$stmt = $conn->prepare("UPDATE users SET admin = 1 WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'User now has admin status.']; header("Location: users.php"); exit;
	}

	if (empty($name) && empty($password)) {
		die('<div class="message message-error">You did not enter anything.</div>');
	}

	$fields = [];
	$params = [];
	$types  = "";

	if (!empty($name)) {
		$fields[] = "name = ?";
		$params[] = $name;
		$types .= "s";
	}

	if (!empty($password)) {
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$fields[] = "password = ?";
		$params[] = $hashedPassword;
		$types .= "s";
	}

	$params[] = $id;
	$types .= "i";

	$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

	$stmt = $conn->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();

	$_SESSION['flash'] = ['type' => 'success', 'text' => 'User updated successfully.']; header("Location: users.php"); exit;
}

else {
	die('<div class="message message-error">Invalid access. <a href="index.php">Go back</a></div>');
}
?>

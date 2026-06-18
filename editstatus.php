<?php
session_start();

include("connect.php");   // $conn (MySQLi)
include("loggedin.php");
include("functions.php");

echo '<title>Status</title></head><body>';
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

	$stmt = $conn->prepare("SELECT id, status FROM status WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		die('<div class="message message-error">No status exists for this id.</div>');
	}

	$row = $result->fetch_assoc();

	echo '
<div class="card">
    <div class="card-header">Edit Status</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="status">Name</label>
                <input type="text" name="status" id="status" class="form-input" value="' . htmlspecialchars($row['status']) . '" data-rules=\'{"required":true,"minLength":1}\'>
            </div>

            <div class="form-row">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="delete"> Delete this status
                </label>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Update Status" class="btn btn-primary">
                <a href="status.php" class="btn btn-secondary">Cancel</a>
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
    <div class="card-header">Create Status</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="status">Name</label>
                <input type="text" name="status" id="status" class="form-input" placeholder="Enter status name" data-rules=\'{"required":true,"minLength":1}\'>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Create Status" class="btn btn-primary">
                <a href="status.php" class="btn btn-secondary">Cancel</a>
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

	$action = $_POST['action'] ?? '';
	$statusVal = clean($_POST['status'] ?? '');

	if (empty($statusVal)) {
		die('<div class="message message-error">Please enter a status.</div>');
	}

	/**
	 * CREATE
	 */
	if ($action === 'create') {
		admin();

		$stmt = $conn->prepare("INSERT INTO status (status) VALUES (?)");
		$stmt->bind_param("s", $statusVal);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Status created successfully.']; header("Location: status.php"); exit;
	}

	/**
	 * UPDATE / DELETE
	 */
	$id = clean($_POST['id'] ?? '');
	if (empty($id) || !is_numeric($id)) {
		die('<div class="message message-error">Invalid ID.</div>');
	}

	if (isset($_POST['delete'])) {
		admin();

		$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM projects WHERE status = ?");
		$stmt->bind_param("s", $statusVal);
		$stmt->execute();

		$count = $stmt->get_result()->fetch_assoc()['total'];

		if ($count > 0) {
			die('<div class="message message-error">Unable to delete this status. A project is using it.</div>');
		}

		$stmt = $conn->prepare("DELETE FROM status WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Status successfully deleted.']; header("Location: status.php"); exit;
	}

	$stmt = $conn->prepare("UPDATE status SET status = ? WHERE id = ?");
	$stmt->bind_param("si", $statusVal, $id);
	$stmt->execute();

	$_SESSION['flash'] = ['type' => 'success', 'text' => 'Status updated successfully.']; header("Location: status.php"); exit;
}

else {
	die('<div class="message message-error">Invalid access. <a href="index.php">Go back</a></div>');
}
?>

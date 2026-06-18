<?php
session_start();

include("connect.php");   // $conn (MySQLi)
include("loggedin.php");
include("functions.php");

echo '<title>Project</title></head><body>';
include("tables.php");

$conn = getConnection();

/**
 * =========================
 * DETERMINE MODE: create or edit
 * =========================
 */
$isCreate = (isset($_GET['action']) && $_GET['action'] === 'create') ||
            (isset($_POST['action']) && $_POST['action'] === 'create');

/**
 * =========================
 * SHOW FORM (GET request)
 * =========================
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

	// Default empty values for create mode
	$row = [
		'id'           => '',
		'name'         => '',
		'date'         => date("n/j/Y"),
		'des'          => '',
		'cat'          => '',
		'status'       => '',
		'sort'         => 0,
		'private'      => 0,
		'last_changed' => '',
		'last_user'    => ''
	];
	$formTitle = 'Create Project';
	$formAction = 'create';

	if (!$isCreate && isset($_GET['id'])) {
		// Edit mode — fetch existing project
		$id = clean($_GET['id']);

		if (empty($id) || !is_numeric($id)) {
			die('<div class="message message-error">Invalid request.</div>');
		}

		$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			die('<div class="message message-error">No project exists with this id.</div>');
		}

		$row = $result->fetch_assoc();

		// Access control
		if ($row['private'] == 1 && ($_SESSION['admin'] ?? 0) != 1) {
			die('<div class="message message-error">An admin has marked this project un-editable.</div>');
		}

		$formTitle = 'Edit Project';
		$formAction = 'update';
	}

	/**
	 * Fetch categories and statuses for dropdowns
	 */
	$categories = $conn->query("SELECT cat FROM category");
	$statuses   = $conn->query("SELECT status FROM status");

	echo '
<div class="card">
    <div class="card-header">' . $formTitle . '</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="name">Name</label>
                <input type="text" name="name" id="name" class="form-input"
                       value="' . htmlspecialchars($row['name']) . '"
                       data-rules=\'{"required":true,"minLength":1}\'>
            </div>

            <div class="form-group">
                <label class="form-label" for="date">Date</label>
                <input type="text" name="date" id="date" class="form-input"
                       value="' . htmlspecialchars($row['date']) . '"
                       data-rules=\'{"required":true,"pattern":"date"}\'>
            </div>

            <div class="form-group">
                <label class="form-label" for="des">Description</label>
                <textarea name="des" id="des" class="form-textarea"
                          data-rules=\'{"required":true,"minLength":1}\'>' . htmlspecialchars($row['des']) . '</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="cat">Category</label>
                <select name="cat" id="cat" class="form-select">';

	if ($categories && $categories->num_rows > 0) {
		while ($c = $categories->fetch_row()) {
			$selected = ($c[0] === $row['cat']) ? "selected" : "";
			echo "<option value='{$c[0]}' $selected>{$c[0]}</option>";
		}
	} else {
		echo '<option value="">No categories available</option>';
	}

	echo '      </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select">';

	if ($statuses && $statuses->num_rows > 0) {
		while ($s = $statuses->fetch_row()) {
			$selected = ($s[0] === $row['status']) ? "selected" : "";
			echo "<option value='{$s[0]}' $selected>{$s[0]}</option>";
		}
	} else {
		echo '<option value="">No statuses available</option>';
	}

	echo '      </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="sort">Sort Order</label>
                <input type="text" name="sort" id="sort" class="form-input"
                       value="' . htmlspecialchars($row['sort']) . '"
                       style="max-width:100px;"
                       data-rules=\'{"required":true,"integer":true}\'>
            </div>';

	if (!$isCreate) {
		// Show private/delete only when editing
		echo '
            <div class="form-row">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="private" ' . ($row['private'] ? "checked" : "") . '> Mark as private
                </label>
            </div>

            <div class="form-row" style="margin-top:0.5rem;">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="delete"> Delete this project
                </label>
            </div>

            <div style="margin-top:1rem; padding:0.75rem; background:#f8fafc; border-radius:8px; font-size:0.8125rem; color:#64748b;">
                <span>Last changed: ' . $row['last_changed'] . ' by ' . $row['last_user'] . '</span>
            </div>';
	}

	echo '
            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="' . ($isCreate ? 'Create Project' : 'Update Project') . '" class="btn btn-primary">
                <a href="projects.php" class="btn btn-secondary">Cancel</a>
                <input type="hidden" name="action" value="' . $formAction . '">';

	if (!$isCreate) {
		echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
	}

	echo '      </div>

        </form>
    </div>
</div>';
}

/**
 * =========================
 * HANDLE POST (create / update / delete)
 * =========================
 */
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$action = $_POST['action'] ?? '';
	$id     = clean($_POST['id'] ?? '');
	$status = clean($_POST['status'] ?? '');
	$name   = clean($_POST['name'] ?? '');
	$date   = clean($_POST['date'] ?? '');
	$des    = clean($_POST['des'] ?? '');
	$cat    = clean($_POST['cat'] ?? '');
	$sort   = clean($_POST['sort'] ?? '');

	$delete = isset($_POST['delete']);
	$priv   = isset($_POST['private']) ? 1 : 0;

	$time = date("n/j/Y h:i:s A");
	$user = $_SESSION['user'] ?? 'unknown';

	// Validation
	foreach ([$status, $name, $date, $des, $cat, $sort] as $val) {
		if (empty($val)) {
			die('<div class="message message-error">You did not fill out all required fields.</div>');
		}
	}

	if (!is_numeric($sort)) {
		die('<div class="message message-error">Sort value must be a whole number.</div>');
	}

	/**
	 * CREATE PROJECT
	 */
	if ($action === 'create') {

		$stmt = $conn->prepare("
			INSERT INTO projects (name, date, des, cat, status, sort, last_changed, last_user)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)
		");
		$stmt->bind_param("ssssssss", $name, $date, $des, $cat, $status, $sort, $time, $user);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Project created successfully.'];
		header("Location: projects.php");
		exit;
	}

	/**
	 * UPDATE / DELETE — require an existing id
	 */
	if (empty($id) || !is_numeric($id)) {
		die('<div class="message message-error">Invalid ID.</div>');
	}

	// Verify project exists
	$stmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();

	if ($stmt->get_result()->num_rows === 0) {
		die('<div class="message message-error">No project exists with this id.</div>');
	}

	/**
	 * DELETE PROJECT
	 */
	if ($delete) {

		admin();

		$stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Project successfully deleted.'];
		header("Location: projects.php");
		exit;
	}

	/**
	 * UPDATE PROJECT
	 */
	$stmt = $conn->prepare("
		UPDATE projects
		SET name=?, date=?, des=?, cat=?, sort=?, last_changed=?, private=?, last_user=?, status=?
		WHERE id=?
	");

	$stmt->bind_param(
		"ssssissssi",
		$name, $date, $des, $cat, $sort, $time, $priv, $user, $status, $id
	);

	$stmt->execute();

	$_SESSION['flash'] = ['type' => 'success', 'text' => 'Project updated successfully.'];
	header("Location: projects.php");
	exit;
}

else {
	die('<div class="message message-error">Invalid access. <a href="index.php">Go back</a></div>');
}
?>

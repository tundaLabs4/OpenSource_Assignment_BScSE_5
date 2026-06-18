<?php
session_start();

include("connect.php");   // expects $conn (MySQLi)
include("loggedin.php");
include("functions.php");

echo '<title>Category</title></head><body>';
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

	$stmt = $conn->prepare("SELECT id, cat FROM category WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		die('<div class="message message-error">Error, invalid record.</div>');
	}

	$row = $result->fetch_assoc();
	$cat = htmlspecialchars($row['cat']);

	echo '
<div class="card">
    <div class="card-header">Edit Category</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="cat">Name</label>
                <input type="text" name="cat" id="cat" class="form-input" value="' . $cat . '" data-rules=\'{"required":true,"minLength":1}\'>
            </div>

            <div class="form-row">
                <label class="form-label" style="margin-bottom:0;">
                    <input type="checkbox" name="delete"> Delete this category
                </label>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Update Category" class="btn btn-primary">
                <a href="cat.php" class="btn btn-secondary">Cancel</a>
                <input type="hidden" name="id" value="' . $id . '">
                <input type="hidden" name="action" value="update">
            </div>

        </form>
    </div>
</div>';

} elseif ($isCreate && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	// --- CREATE MODE ---
	echo '
<div class="card">
    <div class="card-header">Create Category</div>
    <div class="card-body">
        <form method="POST" data-validate novalidate>

            <div class="form-group">
                <label class="form-label" for="cat">Name</label>
                <input type="text" name="cat" id="cat" class="form-input" placeholder="Enter category name" data-rules=\'{"required":true,"minLength":1}\'>
            </div>

            <div class="form-row" style="margin-top:1.25rem;">
                <input type="submit" value="Create Category" class="btn btn-primary">
                <a href="cat.php" class="btn btn-secondary">Cancel</a>
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
	$cat    = clean($_POST['cat'] ?? '');

	if (empty($cat)) {
		die('<div class="message message-error">Please enter a category name.</div>');
	}

	/**
	 * CREATE
	 */
	if ($action === 'create') {
		$stmt = $conn->prepare("INSERT INTO category (cat) VALUES (?)");
		$stmt->bind_param("s", $cat);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Category created successfully.']; header("Location: cat.php"); exit;
	}

	/**
	 * UPDATE / DELETE — require id
	 */
	$id = clean($_POST['id'] ?? '');
	if (empty($id) || !is_numeric($id)) {
		die('<div class="message message-error">Invalid ID.</div>');
	}

	if (isset($_POST['delete'])) {
		admin();

		$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM projects WHERE cat = ?");
		$stmt->bind_param("s", $cat);
		$stmt->execute();
		$count = $stmt->get_result()->fetch_assoc()['total'];

		if ($count > 0) {
			die('<div class="message message-error">Cannot delete: a project is using this category.</div>');
		}

		$stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		$_SESSION['flash'] = ['type' => 'success', 'text' => 'Category successfully deleted.']; header("Location: cat.php"); exit;
	}

	$stmt = $conn->prepare("UPDATE category SET cat = ? WHERE id = ?");
	$stmt->bind_param("si", $cat, $id);
	$stmt->execute();

	$_SESSION['flash'] = ['type' => 'success', 'text' => 'Category updated successfully.']; header("Location: cat.php"); exit;
}

else {
	die('<div class="message message-error">Invalid access. <a href="index.php">Go back</a></div>');
}
?>

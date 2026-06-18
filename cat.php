<?php
session_start();

include("connect.php");   // expects $conn (MySQLi)
include("loggedin.php");
include("functions.php");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

echo '<title>Categories</title></head><body>';
include("tables.php");
$conn = getConnection();

if ($flash): echo '<div class="message message-' . $flash['type'] . '">' . htmlspecialchars($flash['text']) . '</div>';
endif;

echo '<div class="action-bar">';
echo '<a href="editcat.php?action=create" class="btn btn-primary">Create a new category</a>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">Categories</div>';
echo '<div class="card-body" style="padding:0;">';

$result = $conn->query("SELECT id, cat FROM category");
$total = $result->num_rows;

echo tableHead() . '
<thead><tr><th>Category</th></tr></thead>
<tbody>';

while ($row = $result->fetch_assoc()) {

	$id  = $row['id'];
	$cat = htmlspecialchars($row['cat']);

	echo '<tr>
        <td>
            <a href="editcat.php?id=' . $id . '">' . $cat . ' ( ' . $id . ' )</a>
        </td>
    </tr>';
}

echo '</tbody></table>';
echo '</div></div>';

echo '<div class="footer"><span>Total categories: ' . $total . '</span></div>';
echo '</body></html>';
?>

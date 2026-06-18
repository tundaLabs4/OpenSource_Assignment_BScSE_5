<?php
session_start();

include("connect.php");   // $conn (MySQLi)
include("loggedin.php");
include("functions.php");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

echo '<title>Status</title></head><body>';
include("tables.php");

$conn = getConnection();

if ($flash): echo '<div class="message message-' . $flash['type'] . '">' . htmlspecialchars($flash['text']) . '</div>';
endif;

echo '<div class="action-bar">';
echo '<a href="editstatus.php?action=create" class="btn btn-primary">Create a new status</a>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">Status</div>';
echo '<div class="card-body" style="padding:0;">';

$result = $conn->query("SELECT id, status FROM status");
$total = $result->num_rows;

echo tableHead() . '
<thead><tr><th>Status</th></tr></thead>
<tbody>';

while ($row = $result->fetch_assoc()) {

	$id = $row['id'];
	$status = htmlspecialchars($row['status']);

	echo '<tr>
        <td>
            <a href="editstatus.php?id=' . $id . '">' . $status . ' ( ' . $id . ' )</a>
        </td>
    </tr>';
}

echo '</tbody></table>';
echo '</div></div>';

echo '<div class="footer"><span>Total: ' . $total . '</span></div>';
echo '</body></html>';
?>
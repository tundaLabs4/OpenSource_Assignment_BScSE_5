<?php
session_start();

include("connect.php");   // $conn (MySQLi)
include("loggedin.php");
include("functions.php");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

echo '<title>System Users</title></head><body>';
include("tables.php");

$conn = getConnection();

if ($flash): echo '<div class="message message-' . $flash['type'] . '">' . htmlspecialchars($flash['text']) . '</div>';
endif;

echo '<div class="action-bar">';
echo '<a href="editusers.php?action=create" class="btn btn-primary">Create a new user</a>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">Users</div>';
echo '<div class="card-body" style="padding:0;">';

$result = $conn->query("SELECT id, name, date, ip FROM users");
$total = $result->num_rows;

echo tableHead() . '
<thead>
<tr>
    <th>Name</th>
    <th>Last Login</th>
    <th>Login IP</th>
</tr>
</thead>
<tbody>';

while ($row = $result->fetch_assoc()) {

	$id   = $row['id'];
	$name = htmlspecialchars($row['name']);
	$date = $row['date'];
	$ip   = $row['ip'];

	echo '<tr>
        <td>
            <a href="editusers.php?id=' . $id . '">' . $name . ' ( ' . $id . ' )</a>
        </td>
        <td>' . $date . '</td>
        <td>' . $ip . '</td>
    </tr>';
}

echo '</tbody></table>';
echo '</div></div>';

echo '<div class="footer"><span>Total users: ' . $total . '</span></div>';
echo '</body></html>';
?>
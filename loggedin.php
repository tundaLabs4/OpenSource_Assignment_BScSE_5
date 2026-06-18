<?php
if (!isset($_SESSION['user']) || !isset($_SESSION['admin']))
	echo '<div class="message message-info">You need to be logged in to use this feature.<br>Please login <a href="login.php" style="color:inherit;">here</a></div>' . die();
?>

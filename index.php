<?php
session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>php project</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>

<?php
include("tables.php");
if ($flash): echo '<div class="message message-' . $flash['type'] . '">' . htmlspecialchars($flash['text']) . '</div>';
endif;
?>
<div class="card" style="text-align:center; padding:3rem 2rem;">
    <h1 style="font-size:1.75rem; font-weight:700; color:#0f172a; margin-bottom:0.5rem;">Welcome User</h1>
    <p style="color:#64748b; font-size:0.9375rem; margin-bottom:0;">This is a simple Project tracking system.</p>
    <?php if (!isset($_SESSION['user'])): ?>
        <p style="margin-top:1.5rem;"><a href="login.php" class="btn btn-primary">Login</a></p>
    <?php else: ?>
        <p style="margin-top:1.5rem; font-size:0.875rem; color:#475569;">
            Logged in as <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
            &middot; <a href="projects.php" class="btn btn-primary">View Projects</a>
        </p>
    <?php endif; ?>
</div>

</body>
</html>

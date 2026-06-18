<?php
session_start();
$bodyClass = 'login-page';

include("connect.php");
include("functions.php");

echo '<title>Register</title></head><body>';
$conn = getConnection();

// If already logged in, redirect
if (isset($_SESSION['user'])) {
	header("Location: index.php");
	exit;
}

$regError = '';
$regSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$name     = trim($_POST['username'] ?? '');
	$pass     = trim($_POST['password'] ?? '');
	$passConf = trim($_POST['password_confirm'] ?? '');

	// Validation
	if (empty($name)) {
		$regError = 'Please enter a username.';
	} elseif (strlen($name) < 2) {
		$regError = 'Username must be at least 2 characters.';
	} elseif (empty($pass)) {
		$regError = 'Please enter a password.';
	} elseif (strlen($pass) < 3) {
		$regError = 'Password must be at least 3 characters.';
	} elseif ($pass !== $passConf) {
		$regError = 'Passwords do not match.';
	} else {
		// Check if username already exists
		$stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
		$stmt->bind_param("s", $name);
		$stmt->execute();
		if ($stmt->get_result()->num_rows > 0) {
			$regError = 'Username already taken. Please choose another.';
		} else {
			// Create user (non-admin by default)
			$hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
			$stmt = $conn->prepare("INSERT INTO users (name, password, admin, date, ip) VALUES (?, ?, 0, '', '')");
			$stmt->bind_param("ss", $name, $hashedPassword);
			$stmt->execute();

			$_SESSION['flash'] = ['type' => 'success', 'text' => 'Registration successful. You can now log in.'];
			header("Location: login.php");
			exit;
		}
	}
}

?>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo" style="background:#3a7d4e;">PTS</div>
            <h1>Create Account</h1>
            <p>Register to start tracking projects</p>
        </div>

        <div class="login-body">

            <?php if ($regError): ?>
                <div class="message message-error"><?php echo htmlspecialchars($regError); ?></div>
            <?php endif; ?>

            <form action="" method="POST" data-validate novalidate>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-input"
                           placeholder="Choose a username"
                           data-rules='{"required":true,"minLength":2}'
                           autocomplete="username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-input"
                           placeholder="Choose a password"
                           data-rules='{"required":true,"minLength":3}'
                           autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-input"
                           placeholder="Re-enter your password"
                           data-rules='{"required":true,"minLength":3}'
                           autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:0.625rem 0; font-size:0.9375rem; margin-top:0.5rem;">
                    Create Account
                </button>
            </form>
        </div>

        <div class="login-footer">
            Already have an account? <a href="login.php" style="color:#2c6fbb; text-decoration:none; font-weight:600;">Sign in</a>
        </div>
    </div>
</div>
</body></html>

<?php
session_start();
$bodyClass = 'login-page';

include("connect.php");
include("functions.php");

echo '<title>Login</title></head><body>';
$conn = getConnection();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user'])) {
	$_SESSION['flash'] = ['type' => 'info', 'text' => 'You are already logged in.'];
	header("Location: index.php");
	exit;
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$name = trim($_POST['username'] ?? '');
	$pass = trim($_POST['password'] ?? '');
	$ip   = $_SERVER['REMOTE_ADDR'];

	if (empty($name)) {
		$loginError = 'Please enter your username.';
	} elseif (empty($pass)) {
		$loginError = 'Please enter your password.';
	} else {
		// Fetch user by name — password stored as bcrypt hash
		$stmt = $conn->prepare("SELECT id, name, admin, password FROM users WHERE name = ?");
		$stmt->bind_param("s", $name);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			$loginError = 'Username or password is incorrect. Please try again.';
		} else {
			$row = $result->fetch_assoc();

			if (!password_verify($pass, $row['password'])) {
				$loginError = 'Username or password is incorrect. Please try again.';
			} else {
				$_SESSION['admin'] = $row['admin'];
				$_SESSION['user']  = $row['name'];

				$update = $conn->prepare("UPDATE users SET date = NOW(), ip = ? WHERE id = ?");
				$update->bind_param("si", $ip, $row['id']);
				$update->execute();

				$_SESSION['flash'] = ['type' => 'success', 'text' => 'Successfully logged in.'];
				header("Location: index.php");
				exit;
			}
		}
	}
}

?>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">PTS</div>
            <h1>Welcome Back</h1>
            <p>Sign in to your project tracker</p>
        </div>

        <div class="login-body">

            <?php if ($loginError): ?>
                <div class="message message-error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>

            <form action="" method="POST" data-validate novalidate>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-input"
                           placeholder="Enter your username"
                           data-rules='{"required":true,"minLength":2}'
                           autocomplete="username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-input"
                           placeholder="Enter your password"
                           data-rules='{"required":true,"minLength":1}'
                           autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:0.625rem 0; font-size:0.9375rem; margin-top:0.5rem;">
                    Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            Don't have an account? <a href="register.php" style="color:#2c6fbb; text-decoration:none; font-weight:600;">Register</a>
        </div>
    </div>
</div>
</body></html>

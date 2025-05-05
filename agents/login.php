<?php
/**
 * Login Page
 * Handles login for all user roles (admin, agent, user).
 */

// --- Dependencies ---
// Assuming these are relative to the root directory where login.php resides
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php'; // Needs sanitize, setFlashMessage, displayFlashMessage, redirect

// --- Session Start ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Redirect if already logged in ---
// Redirect logged-in users away from login page
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'admin') {
            redirect(ADMIN_URL . 'dashboard.php'); // Redirect admin
            exit;
        } elseif ($_SESSION['user_role'] === 'agent') {
            redirect(ROOT_URL . 'agent/dashboard.php'); // Redirect agent (adjust path if needed)
            exit;
        }
    }
    // Redirect logged-in regular users to homepage or their dashboard
    redirect(ROOT_URL); // Redirect to homepage
    exit;
}


// --- Variables ---
$page_title = 'Login';
$email = '';
$password = '';
$errors = [];

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Password is not sanitized before verification

    // --- Validation ---
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    // --- If No Validation Errors, Attempt Login ---
    if (empty($errors)) {
        try {
            $db = new Database();
            $db->query("SELECT id, name, email, password, role, status, profile_pic FROM users WHERE email = :email");
            $db->bind(':email', $email);
            $user = $db->single();

            // 1. Check if user exists
            if ($user) {
                // 2. Verify password
                if (password_verify($password, $user['password'])) {
                    // 3. Check if account is active
                    if ($user['status'] == 1) {
                        // --- Login Success ---

                        // Regenerate session ID for security
                        session_regenerate_id(true);

                        // Store essential user info in session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_profile_pic'] = $user['profile_pic']; // Store relative path

                        // Role-specific actions & redirection
                        if ($user['role'] === 'admin') {
                            // Admin logged in
                            setFlashMessage('success', 'Welcome back, Admin!');
                            redirect(ADMIN_URL . 'dashboard.php'); // Redirect to admin dashboard
                            exit;
                        } elseif ($user['role'] === 'agent') {
                            // Agent logged in - Fetch agent_id
                            $db->query("SELECT id FROM agents WHERE user_id = :user_id");
                            $db->bind(':user_id', $user['id']);
                            $agent_rec = $db->single();

                            if ($agent_rec) {
                                $_SESSION['agent_id'] = $agent_rec['id']; // Store agent ID
                                setFlashMessage('success', 'Login successful. Welcome back!');
                                redirect(ROOT_URL . 'agent/dashboard.php'); // Redirect to agent dashboard
                                exit;
                            } else {
                                // User has agent role but no agent record - treat as error
                                error_log("Login Error: User ID {$user['id']} has role 'agent' but no matching record in agents table.");
                                unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_profile_pic']); // Clear partial session
                                $errors['login'] = 'Agent profile not found. Please contact support.';
                            }
                        } else {
                            // Regular user logged in
                            setFlashMessage('success', 'Login successful!');
                            redirect(ROOT_URL); // Redirect to homepage or user dashboard
                            exit;
                        }

                    } else {
                        // Account inactive
                        $errors['login'] = 'Your account is inactive. Please contact support.';
                    }
                } else {
                    // Invalid password
                    $errors['login'] = 'Invalid email or password.';
                }
            } else {
                // User not found
                $errors['login'] = 'Invalid email or password.';
            }

        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $errors['login'] = 'An error occurred during login. Please try again.';
        }
    }
    // If errors occurred (validation or login process), set a general flash message
    if (!empty($errors)) {
         setFlashMessage('error', $errors['login'] ?? 'Please check the form for errors.');
    }
}


// --- Include Public Header ---
// Use a simple header suitable for public pages like login/register
// It should include Bootstrap CSS
include_once 'includes/header.php'; // Or your main header if it adapts
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <h2 class="text-center mb-4"><?php echo SITE_NAME ?? 'Website'; ?> Login</h2>

                    <?php displayFlashMessage(); // Display feedback messages ?>

                    <form action="login.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) || isset($errors['login']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="Enter your email">
                            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['password']) || isset($errors['login']) ? 'is-invalid' : ''; ?>" id="password" name="password" required placeholder="Enter your password">
                             <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                             <?php if (isset($errors['login'])): ?><div class="invalid-feedback d-block"><?php echo $errors['login']; ?></div><?php endif; ?>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">Don't have an account? <a href="register.php">Sign up</a></p>
                             <p class="mb-0 mt-1"><small>Are you an agent? <a href="agent-register.php">Register here</a></small></p> </div>
                    </form>
                </div> </div> </div> </div> </div> <?php
// --- Include Public Footer ---
// Should include Bootstrap JS bundle
include_once 'includes/footer-public.php'; // Or your main footer
?>
```

---

Next, the `logout.php` script. This is usually very simple.


```php
<?php
/**
 * Logout Script
 * Destroys the user session and redirects to the login page or homepage.
 */

// --- Dependencies (Optional - only if needed before session destroy) ---
require_once 'includes/config.php'; // Needed for ROOT_URL redirection

// --- Session Handling ---
// Start the session to access session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Unset all session variables
$_SESSION = array();

// 2. If using session cookies, delete the cookie
// Recommended to ensure complete logout
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Set expiry in the past
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to login page or homepage
// Use ROOT_URL defined in config.php
$redirect_url = defined('ROOT_URL') ? ROOT_URL . 'login.php' : 'login.php'; // Fallback
header("Location: " . $redirect_url);
exit; // Ensure no further code execution

?>
```

---

**To implement these:**

1.  **Save Files:** Save the first block as `login.php` and the second as `logout.php` in your main project directory (e.g., `GunturProperties/`).
2.  **Create Public Header/Footer:** You'll likely need simplified `includes/header-public.php` and `includes/footer-public.php` files that just include the necessary Bootstrap CSS/JS and basic HTML structure, without the admin/agent sidebars or dashboard headers.
3.  **Update `config.php`:** Make sure `ROOT_URL` and `ADMIN_URL` are correctly defined.
4.  **Test:** Try accessing `login.php`, logging in as different user roles (admin, agent, normal user if you have them), and using the `logout.php` script (e.g., by linking to it from the user dropdown in the headers).

With these in place, you have the foundation for user authentication. We can now proceed to build the other agent-specific pages like `profile.php` and `properties.php`. Which one would you like to create ne

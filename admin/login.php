<?php
/**
 * Admin Login Page
 */
// Include configuration files
require_once 'includes/config.php';  // This already starts the session and defines constants
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Initialize variables
$email = '';
$error = '';

// Default admin credentials
// $default_email = 'admin@gunturproperties.com';
// $default_password = 'admin123';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('dashboard/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check if default credentials are used
        // if ($email === $default_email && $password === $default_password) {
        //     // Set session variables for default admin
        //     $_SESSION['user_id'] = 1;
        //     $_SESSION['user_name'] = 'Admin';
        //     $_SESSION['user_email'] = $default_email;
        //     $_SESSION['user_role'] = 'admin';
            
        //     // Redirect to dashboard
        //     setFlashMessage('success', 'Login successful! Welcome back, Admin.');
        //     redirect('dashboard/dashboard.php');
        // } else {
            // Check if user exists in database
            $db = new Database();
            $db->query("SELECT * FROM users WHERE email = :email AND status = 1");
            $db->bind(':email', $email);
            $user = $db->single();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to dashboard
                setFlashMessage('success', 'Login successful! Welcome back, ' . $user['name'] . '.');
                redirect('dashboard/dashboard.php');
            } else {
                $error = 'Invalid email or password';
            }
        // }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Guntur Properties</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Backup styles in case external CSS doesn't load */
        body.login-page {
            font-family: 'Urbanist', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 400px;
            overflow: hidden;
        }
        .login-logo {
            background-color: #3b71ca;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .login-logo h1 {
            margin: 0;
            font-size: 24px;
        }
        .login-form-container {
            padding: 30px 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 10px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #3b71ca;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <h1>Guntur Properties</h1>
            <p>Admin Panel</p>
        </div>
        
        <div class="login-form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <!-- <p><small>Default login: admin@gunturproperties.com / admin123</small></p> -->
                <p>Back to <a href="../index.php">Main Website</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-script.js"></script>
</body>
</html>
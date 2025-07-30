<?php
require_once '../includes/db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password!";
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT user_id, username, password, full_name, user_type FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                if ($user['user_type'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../index.php');
                }
            } else {
                $error_message = "Invalid username or password!";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Login - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="form-container">
                <h2 class="text-center mb-4">
                    <i class="fas fa-sign-in-alt"></i> Login
                </h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                </div>
                
                <div class="mt-4 p-3 bg-light rounded">
                    <h6>Demo Credentials:</h6>
                    <p class="mb-1"><strong>Admin:</strong> username: admin, password: admin123</p>
                    <p class="mb-0"><strong>Customer:</strong> username: demo, password: demo123</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

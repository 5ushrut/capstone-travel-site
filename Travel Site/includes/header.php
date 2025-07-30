<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Travel Agency Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../assets/css/style.css' : 'assets/css/style.css'; ?>" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../assets/images/favicon.ico' : 'assets/images/favicon.ico'; ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../index.php' : 'index.php'; ?>">
                <i class="fas fa-plane me-2"></i> Travel Agency
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" 
                           href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../index.php' : 'index.php'; ?>">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'active' : ''; ?>" 
                           href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../pages/booking.php' : ((strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? 'booking.php' : 'pages/booking.php'); ?>">
                            <i class="fas fa-ticket-alt me-1"></i> Book Now
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>" 
                           href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../pages/contact.php' : ((strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? 'contact.php' : 'pages/contact.php'); ?>">
                            <i class="fas fa-envelope me-1"></i> Contact
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-2"></i> 
                                <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><h6 class="dropdown-header">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (isAdmin()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'dashboard.php' : 'admin/dashboard.php'; ?>">
                                            <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'manage_bookings.php' : 'admin/manage_bookings.php'; ?>">
                                            <i class="fas fa-list me-2"></i> Manage Bookings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'reports.php' : 'admin/reports.php'; ?>">
                                            <i class="fas fa-chart-bar me-2"></i> Reports
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? 'my_bookings.php' : 'pages/my_bookings.php'; ?>">
                                            <i class="fas fa-suitcase me-2"></i> My Bookings
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../pages/logout.php' : 'pages/logout.php'; ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../pages/login.php' : 'pages/login.php'; ?>">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2 px-3" href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../pages/register.php' : 'pages/register.php'; ?>">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($_GET['message'])): ?>
        <div class="container mt-3">
            <?php 
            $message = htmlspecialchars($_GET['message']);
            $alert_class = 'info';
            $alert_icon = 'fas fa-info-circle';
            $alert_message = '';
            
            switch ($message) {
                case 'logged_out':
                    $alert_class = 'success';
                    $alert_icon = 'fas fa-check-circle';
                    $alert_message = 'You have been successfully logged out.';
                    break;
                case 'login_required':
                    $alert_class = 'warning';
                    $alert_icon = 'fas fa-exclamation-triangle';
                    $alert_message = 'Please login to access this page.';
                    break;
                case 'admin_required':
                    $alert_class = 'danger';
                    $alert_icon = 'fas fa-ban';
                    $alert_message = 'Admin access required for this page.';
                    break;
                case 'booking_success':
                    $alert_class = 'success';
                    $alert_icon = 'fas fa-check-circle';
                    $alert_message = 'Your booking has been confirmed successfully!';
                    break;
                case 'payment_success':
                    $alert_class = 'success';
                    $alert_icon = 'fas fa-credit-card';
                    $alert_message = 'Payment completed successfully!';
                    break;
                default:
                    $alert_message = ucfirst(str_replace('_', ' ', $message));
                    break;
            }
            ?>
            
            <div class="alert alert-<?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                <i class="<?php echo $alert_icon; ?> me-2"></i>
                <?php echo $alert_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="min-vh-100">

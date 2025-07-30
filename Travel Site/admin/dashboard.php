<?php
require_once '../includes/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../pages/login.php?message=admin_required');
}

// Get dashboard statistics
$stats = [];
try {
    $pdo = getConnection();
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $stats['total_bookings'] = $stmt->fetchColumn();
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_price) as revenue FROM bookings WHERE booking_status IN ('confirmed', 'completed')");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'");
    $stats['total_customers'] = $stmt->fetchColumn();
    
    // Pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE booking_status = 'pending'");
    $stats['pending_bookings'] = $stmt->fetchColumn();
    
    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, u.full_name, u.email 
        FROM bookings b 
        JOIN users u ON b.user_id = u.user_id 
        ORDER BY b.created_at DESC 
        LIMIT 10
    ");
    $recent_bookings = $stmt->fetchAll();
    
    // Popular destinations
    $stmt = $pdo->query("
        SELECT destination, COUNT(*) as bookings, SUM(total_price) as revenue 
        FROM bookings 
        WHERE booking_status IN ('confirmed', 'completed')
        GROUP BY destination 
        ORDER BY bookings DESC
    ");
    $popular_destinations = $stmt->fetchAll();
    
    // Monthly revenue data for chart
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
               SUM(total_price) as revenue,
               COUNT(*) as bookings
        FROM bookings 
        WHERE booking_status IN ('confirmed', 'completed')
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthly_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

$page_title = 'Admin Dashboard - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <div class="dashboard-stats"><?php echo number_format($stats['total_bookings']); ?></div>
                    <div><i class="fas fa-ticket-alt"></i> Total Bookings</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <div class="dashboard-stats">₹<?php echo number_format($stats['total_revenue']); ?></div>
                    <div><i class="fas fa-rupee-sign"></i> Total Revenue</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body">
                    <div class="dashboard-stats"><?php echo number_format($stats['total_customers']); ?></div>
                    <div><i class="fas fa-users"></i> Total Customers</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card dashboard-card bg-warning text-white">
                <div class="card-body">
                    <div class="dashboard-stats"><?php echo number_format($stats['pending_bookings']); ?></div>
                    <div><i class="fas fa-clock"></i> Pending Bookings</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="manage_bookings.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list"></i> Manage Bookings
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="reports.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="../pages/contact.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-envelope"></i> Contact Messages
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="../index.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-home"></i> View Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-clock"></i> Recent Bookings</h5>
                    <a href="manage_bookings.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_bookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Destination</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                            <td><?php echo ucfirst($booking['destination']); ?></td>
                                            <td>₹<?php echo number_format($booking['total_price']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($booking['booking_status']) {
                                                    case 'confirmed': $status_class = 'success'; break;
                                                    case 'pending': $status_class = 'warning'; break;
                                                    case 'cancelled': $status_class = 'danger'; break;
                                                    case 'completed': $status_class = 'info'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No bookings found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Popular Destinations -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Popular Destinations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($popular_destinations)): ?>
                        <?php foreach ($popular_destinations as $dest): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0"><?php echo ucfirst($dest['destination']); ?></h6>
                                    <small class="text-muted"><?php echo $dest['bookings']; ?> bookings</small>
                                </div>
                                <div class="text-end">
                                    <strong>₹<?php echo number_format($dest['revenue']); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">No data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue Chart -->
    <?php if (!empty($monthly_data)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Monthly Revenue Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" width="400" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($monthly_data)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($monthly_data, 'month')) . "'"; ?>],
        datasets: [{
            label: 'Revenue (₹)',
            data: [<?php echo implode(',', array_column($monthly_data, 'revenue')); ?>],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            },
            title: {
                display: true,
                text: 'Last 12 Months Revenue'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return '₹' + value.toLocaleString('en-IN');
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

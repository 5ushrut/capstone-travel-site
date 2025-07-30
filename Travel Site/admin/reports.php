<?php
require_once '../includes/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../pages/login.php?message=admin_required');
}

// Date range filter
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t');

try {
    $pdo = getConnection();
    
    // Revenue report
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as booking_date,
            COUNT(*) as total_bookings,
            SUM(total_price) as daily_revenue,
            SUM(CASE WHEN booking_status = 'confirmed' THEN total_price ELSE 0 END) as confirmed_revenue
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY booking_date DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $daily_reports = $stmt->fetchAll();
    
    // Destination wise report
    $stmt = $pdo->prepare("
        SELECT 
            destination,
            COUNT(*) as total_bookings,
            SUM(total_price) as total_revenue,
            AVG(total_price) as avg_price,
            SUM(passengers) as total_passengers
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY destination
        ORDER BY total_revenue DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $destination_reports = $stmt->fetchAll();
    
    // Transport wise report
    $stmt = $pdo->prepare("
        SELECT 
            transport_type,
            COUNT(*) as total_bookings,
            SUM(total_price) as total_revenue,
            AVG(total_price) as avg_price
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY transport_type
        ORDER BY total_revenue DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $transport_reports = $stmt->fetchAll();
    
    // Status wise report
    $stmt = $pdo->prepare("
        SELECT 
            booking_status,
            COUNT(*) as total_bookings,
            SUM(total_price) as total_revenue
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY booking_status
        ORDER BY total_bookings DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $status_reports = $stmt->fetchAll();
    
    // Summary statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(total_price) as total_revenue,
            AVG(total_price) as avg_booking_value,
            SUM(passengers) as total_passengers
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $summary = $stmt->fetch();
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

$page_title = 'Reports - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo htmlspecialchars($end_date); ?>" required>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Generate Report
                    </button>
                </div>
                
                <div class="col-md-3">
                    <a href="?export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <?php if (isset($summary)): ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo number_format($summary['total_bookings']); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>₹<?php echo number_format($summary['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>₹<?php echo number_format($summary['avg_booking_value']); ?></h3>
                        <p>Avg Booking Value</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo number_format($summary['total_passengers']); ?></h3>
                        <p>Total Passengers</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Daily Revenue Chart -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Daily Revenue (<?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($daily_reports)): ?>
                        <canvas id="dailyRevenueChart" width="400" height="100"></canvas>
                    <?php else: ?>
                        <p class="text-center text-muted">No data available for the selected date range.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Destination Report -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Destination Wise Report</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($destination_reports)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Destination</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                        <th>Avg Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($destination_reports as $dest): ?>
                                        <tr>
                                            <td><?php echo ucfirst($dest['destination']); ?></td>
                                            <td><?php echo $dest['total_bookings']; ?></td>
                                            <td>₹<?php echo number_format($dest['total_revenue']); ?></td>
                                            <td>₹<?php echo number_format($dest['avg_price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No destination data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Transport Report -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bus"></i> Transport Wise Report</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($transport_reports)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Transport</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                        <th>Avg Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transport_reports as $transport): ?>
                                        <tr>
                                            <td><?php echo ucfirst($transport['transport_type']); ?></td>
                                            <td><?php echo $transport['total_bookings']; ?></td>
                                            <td>₹<?php echo number_format($transport['total_revenue']); ?></td>
                                            <td>₹<?php echo number_format($transport['avg_price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No transport data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Status Report -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Booking Status Report</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($status_reports)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="statusChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                                <th>Revenue</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total = array_sum(array_column($status_reports, 'total_bookings'));
                                            foreach ($status_reports as $status): 
                                                $percentage = $total > 0 ? ($status['total_bookings'] / $total * 100) : 0;
                                            ?>
                                                <tr>
                                                    <td><?php echo ucfirst($status['booking_status']); ?></td>
                                                    <td><?php echo $status['total_bookings']; ?></td>
                                                    <td>₹<?php echo number_format($status['total_revenue']); ?></td>
                                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No status data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($daily_reports) || !empty($status_reports)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Revenue Chart
<?php if (!empty($daily_reports)): ?>
const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
const dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_map(function($r) { return date('M d', strtotime($r['booking_date'])); }, $daily_reports)) . "'"; ?>],
        datasets: [{
            label: 'Daily Revenue (₹)',
            data: [<?php echo implode(',', array_column($daily_reports, 'daily_revenue')); ?>],
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
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString('en-IN');
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Status Pie Chart
<?php if (!empty($status_reports)): ?>
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php echo "'" . implode("','", array_map(function($s) { return ucfirst($s['booking_status']); }, $status_reports)) . "'"; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_column($status_reports, 'total_bookings')); ?>],
            backgroundColor: [
                '#198754',
                '#ffc107', 
                '#dc3545',
                '#0dcaf0',
                '#6f42c1'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

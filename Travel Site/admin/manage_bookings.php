<?php
require_once '../includes/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../pages/login.php?message=admin_required');
}

$success_message = '';
$error_message = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = sanitize($_POST['action']);
    
    try {
        $pdo = getConnection();
        
        if ($action === 'update_status') {
            $new_status = sanitize($_POST['new_status']);
            $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
            
            if (in_array($new_status, $valid_statuses)) {
                $stmt = $pdo->prepare("UPDATE bookings SET booking_status = ? WHERE booking_id = ?");
                if ($stmt->execute([$new_status, $booking_id])) {
                    $success_message = "Booking status updated successfully!";
                } else {
                    $error_message = "Failed to update booking status.";
                }
            } else {
                $error_message = "Invalid status selected.";
            }
        } elseif ($action === 'delete_booking') {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
            if ($stmt->execute([$booking_id])) {
                $success_message = "Booking deleted successfully!";
            } else {
                $error_message = "Failed to delete booking.";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && $status_filter !== 'all') {
    $where_conditions[] = "b.booking_status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR b.destination LIKE ? OR b.booking_id = ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search]);
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

try {
    $pdo = getConnection();
    
    // Get total count
    $count_query = "SELECT COUNT(*) FROM bookings b JOIN users u ON b.user_id = u.user_id $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_bookings = $stmt->fetchColumn();
    $total_pages = ceil($total_bookings / $per_page);
    
    // Get bookings
    $query = "
        SELECT b.*, u.full_name, u.email, u.phone,
               p.payment_status, p.transaction_id, p.payment_method
        FROM bookings b 
        JOIN users u ON b.user_id = u.user_id 
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        $where_clause
        ORDER BY b.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $bookings = [];
}

$page_title = 'Manage Bookings - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-list"></i> Manage Bookings</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Bookings</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by customer name, email, destination, or booking ID">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Bookings (<?php echo number_format($total_bookings); ?> total)</h5>
            <a href="reports.php" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export Report
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Destination</th>
                                <th>Transport</th>
                                <th>Departure</th>
                                <th>Passengers</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $booking['booking_id']; ?></strong>
                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['full_name']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst($booking['destination']); ?></td>
                                    <td><?php echo ucfirst($booking['transport_type']); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($booking['departure_date'])); ?>
                                        <?php if ($booking['return_date']): ?>
                                            <br><small class="text-muted">Return: <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $booking['passengers']; ?></td>
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
                                    <td>
                                        <?php if ($booking['payment_status']): ?>
                                            <span class="badge bg-<?php echo $booking['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($booking['payment_status']); ?>
                                            </span>
                                            <?php if ($booking['transaction_id']): ?>
                                                <br><small class="text-muted"><?php echo $booking['transaction_id']; ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Payment</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><h6 class="dropdown-header">Update Status</h6></li>
                                                <li>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="new_status" value="confirmed">
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="fas fa-check"></i> Confirm
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="new_status" value="completed">
                                                        <button type="submit" class="dropdown-item text-info">
                                                            <i class="fas fa-flag-checkered"></i> Complete
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="new_status" value="cancelled">
                                                        <button type="submit" class="dropdown-item text-warning">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <input type="hidden" name="action" value="delete_booking">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Bookings pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5>No bookings found</h5>
                    <p class="text-muted">No bookings match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

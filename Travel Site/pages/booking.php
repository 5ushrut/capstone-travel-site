<?php
require_once '../includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php?message=login_required');
}

$success_message = '';
$error_message = '';
$selected_destination = isset($_GET['destination']) ? sanitize($_GET['destination']) : '';

// Destination data
$destinations = [
    'amritsar' => ['name' => 'Amritsar', 'price' => 5000, 'image' => 'amritsar.jpg'],
    'chennai' => ['name' => 'Chennai', 'price' => 4500, 'image' => 'chennai.jpg'],
    'goa' => ['name' => 'Goa', 'price' => 6000, 'image' => 'goa.jpg'],
    'agra' => ['name' => 'Agra', 'price' => 3500, 'image' => 'agra.jpg']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination = sanitize($_POST['destination']);
    $transport_type = sanitize($_POST['transport_type']);
    $departure_date = sanitize($_POST['departure_date']);
    $return_date = sanitize($_POST['return_date']);
    $passengers = intval($_POST['passengers']);
    $special_requests = sanitize($_POST['special_requests']);
    
    if (empty($destination) || empty($transport_type) || empty($departure_date) || $passengers < 1) {
        $error_message = "Please fill in all required fields!";
    } elseif (strtotime($departure_date) <= time()) {
        $error_message = "Departure date must be in the future!";
    } elseif (!empty($return_date) && strtotime($return_date) <= strtotime($departure_date)) {
        $error_message = "Return date must be after departure date!";
    } else {
        try {
            $pdo = getConnection();
            
            // Calculate total price
            $base_price = $destinations[$destination]['price'];
            $transport_multiplier = ($transport_type === 'flight') ? 2 : (($transport_type === 'train') ? 1.2 : 1);
            $total_price = $base_price * $passengers * $transport_multiplier;
            
            // Insert booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, destination, transport_type, departure_date, return_date, 
                passengers, total_price, special_requests, booking_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            if ($stmt->execute([$_SESSION['user_id'], $destination, $transport_type, $departure_date, 
                              $return_date, $passengers, $total_price, $special_requests])) {
                $booking_id = $pdo->lastInsertId();
                redirect("payment.php?booking_id=$booking_id");
            } else {
                $error_message = "Booking failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Book Your Journey - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="form-container">
                <h2 class="mb-4">
                    <i class="fas fa-ticket-alt"></i> Book Your Journey
                </h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="destination" class="form-label">Destination *</label>
                            <select class="form-select" id="destination" name="destination" required>
                                <option value="">Choose destination...</option>
                                <?php foreach ($destinations as $key => $dest): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($selected_destination === $key) ? 'selected' : ''; ?>>
                                        <?php echo $dest['name']; ?> - ₹<?php echo number_format($dest['price']); ?> per person
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="transport_type" class="form-label">Transport Type *</label>
                            <select class="form-select" id="transport_type" name="transport_type" required>
                                <option value="">Choose transport...</option>
                                <option value="bus">Bus (Standard Rate)</option>
                                <option value="train">Train (+20%)</option>
                                <option value="flight">Flight (+100%)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departure_date" class="form-label">Departure Date *</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="return_date" class="form-label">Return Date (Optional)</label>
                            <input type="date" class="form-control" id="return_date" name="return_date">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="passengers" class="form-label">Number of Passengers *</label>
                            <input type="number" class="form-control" id="passengers" name="passengers" 
                                   min="1" max="10" value="1" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimated Cost</label>
                            <div class="form-control bg-light" id="estimated_cost">
                                Select destination and passengers
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="special_requests" class="form-label">Special Requests</label>
                        <textarea class="form-control" id="special_requests" name="special_requests" 
                                  rows="3" placeholder="Any special requirements or requests..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> Proceed to Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-info-circle"></i> Booking Information</h5>
                </div>
                <div class="card-body">
                    <h6>Transport Options:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Bus:</strong> Standard pricing</li>
                        <li><strong>Train:</strong> +20% of base price</li>
                        <li><strong>Flight:</strong> +100% of base price</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Popular Destinations:</h6>
                    <?php foreach ($destinations as $key => $dest): ?>
                        <div class="mb-2">
                            <strong><?php echo $dest['name']; ?>:</strong> 
                            ₹<?php echo number_format($dest['price']); ?> per person
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <h6>Need Help?</h6>
                    <p class="small">Contact our support team for assistance with your booking.</p>
                    <a href="contact.php" class="btn btn-outline-primary btn-sm">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate estimated cost dynamically
function updateCost() {
    const destination = document.getElementById('destination').value;
    const transport = document.getElementById('transport_type').value;
    const passengers = parseInt(document.getElementById('passengers').value) || 1;
    
    const prices = {
        'amritsar': 5000,
        'chennai': 4500,
        'goa': 6000,
        'agra': 3500
    };
    
    const multipliers = {
        'bus': 1,
        'train': 1.2,
        'flight': 2
    };
    
    if (destination && transport) {
        const basePrice = prices[destination];
        const multiplier = multipliers[transport];
        const totalCost = basePrice * multiplier * passengers;
        document.getElementById('estimated_cost').textContent = '₹' + totalCost.toLocaleString('en-IN');
    } else {
        document.getElementById('estimated_cost').textContent = 'Select destination and transport';
    }
}

document.getElementById('destination').addEventListener('change', updateCost);
document.getElementById('transport_type').addEventListener('change', updateCost);
document.getElementById('passengers').addEventListener('input', updateCost);

// Initial cost calculation
updateCost();
</script>

<?php include '../includes/footer.php'; ?>

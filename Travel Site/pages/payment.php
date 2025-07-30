<?php
require_once '../includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php?message=login_required');
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$success_message = '';
$error_message = '';

// Get booking details
$booking = null;
if ($booking_id > 0) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT b.*, u.full_name, u.email, u.phone 
            FROM bookings b 
            JOIN users u ON b.user_id = u.user_id 
            WHERE b.booking_id = ? AND b.user_id = ?
        ");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            redirect('booking.php?error=booking_not_found');
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking) {
    $payment_method = sanitize($_POST['payment_method']);
    $card_number = sanitize($_POST['card_number'] ?? '');
    $card_name = sanitize($_POST['card_name'] ?? '');
    $expiry_month = sanitize($_POST['expiry_month'] ?? '');
    $expiry_year = sanitize($_POST['expiry_year'] ?? '');
    $cvv = sanitize($_POST['cvv'] ?? '');
    
    if (empty($payment_method)) {
        $error_message = "Please select a payment method!";
    } elseif (in_array($payment_method, ['credit_card', 'debit_card']) && 
              (empty($card_number) || empty($card_name) || empty($expiry_month) || empty($expiry_year) || empty($cvv))) {
        $error_message = "Please fill in all card details!";
    } elseif (in_array($payment_method, ['credit_card', 'debit_card']) && 
              (!preg_match('/^[0-9]{16}$/', str_replace(' ', '', $card_number)))) {
        $error_message = "Please enter a valid 16-digit card number!";
    } elseif (in_array($payment_method, ['credit_card', 'debit_card']) && 
              (!preg_match('/^[0-9]{3,4}$/', $cvv))) {
        $error_message = "Please enter a valid CVV!";
    } else {
        try {
            $pdo = getConnection();
            
            // Generate transaction ID
            $transaction_id = 'TXN' . date('Ymdhis') . rand(1000, 9999);
            
            // Insert payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, payment_method, amount, transaction_id, payment_status, payment_date) 
                VALUES (?, ?, ?, ?, 'completed', NOW())
            ");
            
            if ($stmt->execute([$booking_id, $payment_method, $booking['total_price'], $transaction_id])) {
                // Update booking status
                $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                
                redirect("payment_success.php?booking_id=$booking_id&transaction_id=$transaction_id");
            } else {
                $error_message = "Payment processing failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Payment - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <?php if ($booking): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-container">
                            <h2 class="mb-4">
                                <i class="fas fa-credit-card"></i> Complete Payment
                            </h2>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="paymentForm">
                                <div class="mb-4">
                                    <h5>Select Payment Method</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="credit_card" value="credit_card" required>
                                                <label class="form-check-label" for="credit_card">
                                                    <i class="fas fa-credit-card"></i> Credit Card
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="debit_card" value="debit_card" required>
                                                <label class="form-check-label" for="debit_card">
                                                    <i class="fas fa-credit-card"></i> Debit Card
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="net_banking" value="net_banking" required>
                                                <label class="form-check-label" for="net_banking">
                                                    <i class="fas fa-university"></i> Net Banking
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="upi" value="upi" required>
                                                <label class="form-check-label" for="upi">
                                                    <i class="fas fa-mobile-alt"></i> UPI
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Details Section -->
                                <div id="cardDetails" style="display: none;">
                                    <h5>Card Details</h5>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="card_number" class="form-label">Card Number</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                                   placeholder="1234 5678 9012 3456" maxlength="19">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="card_name" class="form-label">Name on Card</label>
                                            <input type="text" class="form-control" id="card_name" name="card_name" 
                                                   placeholder="Enter name as on card">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="expiry_month" class="form-label">Month</label>
                                            <select class="form-select" id="expiry_month" name="expiry_month">
                                                <option value="">MM</option>
                                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                                    <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="expiry_year" class="form-label">Year</label>
                                            <select class="form-select" id="expiry_year" name="expiry_year">
                                                <option value="">YYYY</option>
                                                <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="cvv" class="form-label">CVV</label>
                                            <input type="password" class="form-control" id="cvv" name="cvv" 
                                                   placeholder="123" maxlength="4">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other Payment Methods -->
                                <div id="netBankingDetails" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> You will be redirected to your bank's secure login page.
                                    </div>
                                </div>
                                
                                <div id="upiDetails" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> You will be redirected to your UPI app to complete the payment.
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($booking['total_price'], 2); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="fas fa-receipt"></i> Booking Summary</h5>
                            </div>
                            <div class="card-body">
                                <h6>Booking ID: <span class="text-primary"><?php echo $booking['booking_id']; ?></span></h6>
                                <hr>
                                
                                <div class="mb-2">
                                    <strong>Passenger:</strong> <?php echo htmlspecialchars($booking['full_name']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Destination:</strong> <?php echo ucfirst($booking['destination']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Transport:</strong> <?php echo ucfirst($booking['transport_type']); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Departure:</strong> <?php echo date('M d, Y', strtotime($booking['departure_date'])); ?>
                                </div>
                                <?php if ($booking['return_date']): ?>
                                    <div class="mb-2">
                                        <strong>Return:</strong> <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-2">
                                    <strong>Passengers:</strong> <?php echo $booking['passengers']; ?>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between">
                                    <strong>Total Amount:</strong>
                                    <strong class="text-success">₹<?php echo number_format($booking['total_price'], 2); ?></strong>
                                </div>
                                
                                <?php if ($booking['special_requests']): ?>
                                    <hr>
                                    <div class="mb-2">
                                        <strong>Special Requests:</strong><br>
                                        <small><?php echo htmlspecialchars($booking['special_requests']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                <h6>Secure Payment</h6>
                                <small class="text-muted">Your payment information is encrypted and secure.</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger text-center">
                    <h4><i class="fas fa-exclamation-triangle"></i> Booking Not Found</h4>
                    <p>The booking you're trying to pay for could not be found.</p>
                    <a href="booking.php" class="btn btn-primary">Make New Booking</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('cardDetails');
    const netBankingDetails = document.getElementById('netBankingDetails');
    const upiDetails = document.getElementById('upiDetails');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all details sections
            cardDetails.style.display = 'none';
            netBankingDetails.style.display = 'none';
            upiDetails.style.display = 'none';
            
            // Show relevant section
            if (this.value === 'credit_card' || this.value === 'debit_card') {
                cardDetails.style.display = 'block';
                makeCardFieldsRequired(true);
            } else if (this.value === 'net_banking') {
                netBankingDetails.style.display = 'block';
                makeCardFieldsRequired(false);
            } else if (this.value === 'upi') {
                upiDetails.style.display = 'block';
                makeCardFieldsRequired(false);
            }
        });
    });
    
    function makeCardFieldsRequired(required) {
        const cardFields = ['card_number', 'card_name', 'expiry_month', 'expiry_year', 'cvv'];
        cardFields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.required = required;
            }
        });
    }
    
    // Format card number
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        e.target.value = value;
    });
});
</script>

<?php include '../includes/footer.php'; ?>

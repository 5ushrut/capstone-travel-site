<?php
require_once '../includes/db.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Name, email, and message are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address!";
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $error_message = "Phone number must be 10 digits!";
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
                $success_message = "Thank you for your message! We'll get back to you soon.";
                // Clear form data
                $_POST = [];
            } else {
                $error_message = "Failed to send message. Please try again.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = 'Contact Us - Travel Agency Management System';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="text-center mb-5">
                <h2><i class="fas fa-envelope"></i> Contact Us</h2>
                <p class="lead">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="form-container">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                   pattern="[0-9]{10}" placeholder="10 digit mobile number">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <select class="form-select" id="subject" name="subject">
                                <option value="">Choose a subject...</option>
                                <option value="Booking Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Booking Inquiry') ? 'selected' : ''; ?>>Booking Inquiry</option>
                                <option value="Payment Issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Payment Issue') ? 'selected' : ''; ?>>Payment Issue</option>
                                <option value="Cancellation" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Cancellation') ? 'selected' : ''; ?>>Cancellation</option>
                                <option value="General Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Question') ? 'selected' : ''; ?>>General Question</option>
                                <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6" 
                                  placeholder="Please describe your inquiry in detail..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-info-circle"></i> Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6><i class="fas fa-map-marker-alt text-primary"></i> Address</h6>
                        <p>Eklavya Shikshan Sanstha's Polytechnic<br>
                        Pune, Maharashtra, India</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-phone text-primary"></i> Phone</h6>
                        <p>+91 12345 67890</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-envelope text-primary"></i> Email</h6>
                        <p>info@travelagency.com</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-clock text-primary"></i> Business Hours</h6>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                        Saturday: 9:00 AM - 4:00 PM<br>
                        Sunday: Closed</p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-headset"></i> Quick Support</h5>
                </div>
                <div class="card-body">
                    <p>For immediate assistance with your booking or travel plans:</p>
                    <ul class="list-unstyled">
                        <li><strong>Booking Support:</strong> +91 12345 67890</li>
                        <li><strong>Payment Issues:</strong> +91 12345 67890</li>
                        <li><strong>Emergency Travel:</strong> +91 12345 67890</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

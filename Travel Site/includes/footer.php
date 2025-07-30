    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-plane"></i> Travel Agency</h5>
                    <p>Your trusted partner for memorable journeys across India. Experience the beauty and culture of our incredible destinations.</p>
                </div>
                
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li><a href="pages/booking.php" class="text-decoration-none">Book Now</a></li>
                        <li><a href="pages/contact.php" class="text-decoration-none">Contact Us</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php" class="text-decoration-none">Admin Panel</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-phone"></i> +91 12345 67890</p>
                    <p><i class="fas fa-envelope"></i> info@travelagency.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Pune, Maharashtra, India</p>
                </div>
            </div>
            
            <hr class="my-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2021 Travel Agency Management System. Diploma Project by Sushrut Satpute</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>

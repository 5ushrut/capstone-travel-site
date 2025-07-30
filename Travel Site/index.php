<?php
require_once 'includes/db.php';
$page_title = 'Home - Travel Agency Management System';
include 'includes/header.php';
?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
    </div>
    
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="assets/images/amritsar.jpg" class="d-block w-100" alt="Golden Temple, Amritsar">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4">Golden Temple, Amritsar</h1>
                <p class="lead">Experience spiritual serenity at the holiest Sikh shrine</p>
                <a href="pages/booking.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
        </div>
        
        <div class="carousel-item">
            <img src="assets/images/chennai.jpg" class="d-block w-100" alt="Chennai Temples">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4">Chennai Heritage</h1>
                <p class="lead">Discover the rich cultural heritage of South India</p>
                <a href="pages/booking.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
        </div>
        
        <div class="carousel-item">
            <img src="assets/images/goa.jpg" class="d-block w-100" alt="Goa Beaches">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4">Goa Paradise</h1>
                <p class="lead">Relax on pristine beaches with stunning sunsets</p>
                <a href="pages/booking.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
        </div>
        
        <div class="carousel-item">
            <img src="assets/images/agra.jpg" class="d-block w-100" alt="Taj Mahal, Agra">
            <div class="carousel-caption d-none d-md-block">
                <h1 class="display-4">Taj Mahal, Agra</h1>
                <p class="lead">Marvel at the symbol of eternal love</p>
                <a href="pages/booking.php" class="btn btn-primary btn-lg">Book Now</a>
            </div>
        </div>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-12 mb-5">
                <h2 class="display-5">Why Choose Our Travel Agency?</h2>
                <p class="lead">Experience the best of India with our premium travel services</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-bus fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Multiple Transport Options</h5>
                        <p class="card-text">Choose from bus, train, or flight options for your perfect journey</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Secure Booking</h5>
                        <p class="card-text">Safe and secure online payment gateway for all transactions</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">24/7 Support</h5>
                        <p class="card-text">Round-the-clock customer support for all your travel needs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Destinations -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="display-5">Popular Destinations</h2>
                <p class="lead">Explore India's most beautiful and culturally rich destinations</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card destination-card">
                    <img src="assets/images/amritsar.jpg" class="card-img-top" alt="Amritsar">
                    <div class="card-body">
                        <h5 class="card-title">Amritsar</h5>
                        <p class="card-text">Visit the Golden Temple and experience Sikh culture</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Starting from ₹5,000</small>
                            <a href="pages/booking.php?destination=amritsar" class="btn btn-primary btn-sm">Book</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card destination-card">
                    <img src="assets/images/chennai.jpg" class="card-img-top" alt="Chennai">
                    <div class="card-body">
                        <h5 class="card-title">Chennai</h5>
                        <p class="card-text">Explore ancient temples and South Indian heritage</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Starting from ₹4,500</small>
                            <a href="pages/booking.php?destination=chennai" class="btn btn-primary btn-sm">Book</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card destination-card">
                    <img src="assets/images/goa.jpg" class="card-img-top" alt="Goa">
                    <div class="card-body">
                        <h5 class="card-title">Goa</h5>
                        <p class="card-text">Relax on beautiful beaches and enjoy vibrant nightlife</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Starting from ₹6,000</small>
                            <a href="pages/booking.php?destination=goa" class="btn btn-primary btn-sm">Book</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card destination-card">
                    <img src="assets/images/agra.jpg" class="card-img-top" alt="Agra">
                    <div class="card-body">
                        <h5 class="card-title">Agra</h5>
                        <p class="card-text">Marvel at the iconic Taj Mahal and Mughal architecture</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Starting from ₹3,500</small>
                            <a href="pages/booking.php?destination=agra" class="btn btn-primary btn-sm">Book</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

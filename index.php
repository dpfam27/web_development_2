<?php
include 'connect.php';
include 'auth.php'; 

$page_title = "Home - WheyStore";
include 'header.php';
?>

<!-- PROMO BANNER -->
<div class="promo-banner text-center py-2">
    <div class="container">
        <span class="promo-text">
            <i class="fas fa-gift mr-2"></i> 
            WELCOME DEAL! Use code <span class="badge badge-light text-dark mx-1 p-1">SALE10</span> to get <strong>10% OFF</strong> your first order!
        </span>
        <a href="products.php" class="btn btn-sm btn-outline-light ml-3">SHOP NOW</a>
    </div>
</div>

<!-- HERO SECTION -->
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Awaken Your Power</h1>
        <p class="hero-subtitle">
            We provide 100% authentic Whey Protein & Supplements.<br>
            Accompanying you on the journey to your dream physique.
        </p>
        <a href="products.php" class="btn btn-primary btn-hero">SHOP NOW <i class="fas fa-arrow-right ml-2"></i></a>
    </div>
</div>

<?php include 'footer.php'; ?>
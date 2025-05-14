<?php
session_start(); // Start a session to track if user has seen splash page

include 'header.php';

// Check if this is the first time visiting
if(!isset($_SESSION['visited'])) {
    // Set the session variable to remember the visit
    $_SESSION['visited'] = true;
    // Redirect to splash page
    header("Location: splash.php");
    exit; // Important to prevent further code execution
}
?>
    <!-- Hero Section -->
    <section class="hero-section">
      <!-- Optional shooting star effect -->
      <div class="shooting-star"></div>

      <div class="container">
        <div class="hero-content">
          <div class="hero-text">
            <div class="section-tag">
              <!-- Sparkle stars with sequential animation -->
              <div class="sparkle-star sparkle-star-large">
                <svg
                  viewBox="0 0 36 36"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                    fill="#2048A8"
                  />
                </svg>
              </div>
              <div class="sparkle-star sparkle-star-medium">
                <svg
                  viewBox="0 0 36 36"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                    fill="#2048A8"
                  />
                </svg>
              </div>
              <div class="sparkle-star sparkle-star-small">
                <svg
                  viewBox="0 0 36 36"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                    fill="#2048A8"
                  />
                </svg>
              </div>
            </div>
            <h1>Discover Your Dream Property</h1>
            <p>
              Your journey to finding your ideal home with our handpicked
              collection of premium real estate properties.
            </p>
            <div class="hero-btns">
              <a href="#" class="btn btn-outline">Learn more</a>
              <a href="properties.php" class="btn btn-primary"
                >Explore properties</a
              >
            </div>
            <!-- Stats with counting animation -->
            <div class="hero-stats">
              <div class="stat-item">
                <h2 class="counter" data-target="200">200+</h2>
                <p>Happy Customers</p>
              </div>
              <div class="stat-item">
                <h2 class="counter" data-target="1500">1500+</h2>
                <p>Properties for Clients</p>
              </div>
              <div class="stat-item">
                <h2 class="counter" data-target="9">9+</h2>
                <p>Years of experience</p>
              </div>
            </div>
            <div class="find-home-callout">
              <img src="assets/images/woodHome.png" alt="Find Home" />
              <div class="callout-text">
                <p>Where Stories Begin 📖✨</p>
                <p>Where Memories Stay 🏡💫</p>
              </div>
            </div>
          </div>
          <div class="hero-image">
            <img src="assets/images/home.png" alt="Luxury Property" />
          </div>
        </div>
      </div>
    </section>
    <!-- Services Section -->
    <section class="services-section">
      <div class="container">
        <div class="services-grid">
          <div class="service-card">
            <div class="service-icon">
              <img
                src="assets/images/propertyValueIcon.png"
                alt="Property Value"
              />
            </div>
            <h3>Unlock Property Value</h3>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <img src="assets/images/DreamHomeIcon.png" alt="Find Home" />
            </div>
            <h3>Find your Dream home</h3>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <img
                src="assets/images/HassleFreeIcon.png"
                alt="Property Management"
              />
            </div>
            <h3>Hassle-Free Property Management</h3>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <img src="assets/images/intelligentIcon.png" alt="Investing" />
            </div>
            <h3>Intelligent Investing, Insightful Planning</h3>
          </div>
        </div>
      </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
      <div class="container">
        <div class="search-container">
          <div class="search-input">
            <input type="text" placeholder="Search For A Property" />
            <button class="search-btn">
              <i class="fas fa-search"></i> Find Property
            </button>
          </div>
          <div class="search-filters">
            <div class="filter-dropdown">
              <button class="dropdown-btn">
                <i class="fas fa-home"></i> Property Type
                <i class="fas fa-chevron-down"></i>
              </button>
            </div>
            <div class="filter-dropdown">
              <button class="dropdown-btn">
                <i class="fas fa-rupee-sign"></i> Pricing Range
                <i class="fas fa-chevron-down"></i>
              </button>
            </div>
            <div class="filter-dropdown">
              <button class="dropdown-btn">
                <i class="fas fa-ruler-combined"></i> Property Size
                <i class="fas fa-chevron-down"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="featured-properties">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">
            <div class="sparkle-star sparkle-star-large">
              <svg
                viewBox="0 0 36 36"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                  fill="#2048A8"
                />
              </svg>
            </div>
            <div class="sparkle-star sparkle-star-medium">
              <svg
                viewBox="0 0 36 36"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                  fill="#2048A8"
                />
              </svg>
            </div>
            <div class="sparkle-star sparkle-star-small">
              <svg
                viewBox="0 0 36 36"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z"
                  fill="#2048A8"
                />
              </svg>
            </div>
          </div>
          <h2>Featured Properties</h2>
          <p>
            Discover our curated collection of featured properties — each one
            showcasing unique homes and valuable investment opportunities. Tap
            'View Details' to learn more.
          </p>
        </div>
        <div class="view-all">
          <a href="properties.php" class="btn btn-outline"
            >View All Properties</a
          >
        </div>

        <!-- Property Carousel Integration -->
        <div class="carousel-container">
          <div class="carousel">
            <!-- Carousel items will be added dynamically by JavaScript -->
          </div>

          <div class="nav-arrows">
            <div class="arrow-btn prev">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b71ca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </div>
            <div class="arrow-btn next">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b71ca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </div>
          </div>

          <div class="progress-dots">
            <!-- Dots will be added dynamically by JavaScript -->
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <h2>Why Choose Us</h2>
        <p class="section-subtitle">
          Enhancing your home-buying journey with expert guidance, honest
          advice, and truly personalized service.
        </p>

        <div class="benefits-grid">
          <div class="benefit-card">
            <div class="benefit-icon">
              <img
                src="assets/images/knowledgeIcon.png"
                alt="Expert Knowledge"
              />
            </div>
            <h3>Expert Knowledge</h3>
            <p>
              Benefit from in-depth understanding and access to unique property
              opportunities.
            </p>
          </div>

          <div class="benefit-card">
            <div class="benefit-icon">
              <img src="assets/images/brokersIcon.png" alt="Avoid Brokers" />
            </div>
            <h3>Avoid Brokers</h3>
            <p>
              We connect property buyers to verified owners to save brokerage.
            </p>
          </div>

          <div class="benefit-card">
            <div class="benefit-icon">
              <img src="assets/images/mapIcon.png" alt="Top-tier Locations" />
            </div>
            <h3>Top-tier Locations</h3>
            <p>
              Discover properties in the choicest/most in-demand neighborhoods.
            </p>
          </div>

          <div class="benefit-card">
            <div class="benefit-icon">
              <img
                src="assets/images/ExclusiveIcon.png"
                alt="Exclusive Listings"
              />
            </div>
            <h3>Exclusive Listings</h3>
            <p>
              Access to properties you simply cannot find elsewhere to match
              your experience.
            </p>
          </div>
        </div>
      </div>
    </section>
<?php include 'footer.php'?>
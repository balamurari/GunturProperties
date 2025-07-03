<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Property Carousel - Guntur Properties</title>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Premium Carousel CSS - White & Blue Theme */
        :root {
            --primary-color: #3f51b5;
            --primary-dark: #303f9f;
            --primary-light: #5c6bc0;
            --secondary-color: #4caf50;
            --accent-color: #ff9800;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --text-dark: #333333;
            --text-light: #666666;
            --text-muted: #9e9e9e;
            --border-color: #e9ecef;
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 25px rgba(63, 81, 181, 0.15);
            --shadow-strong: 0 20px 40px rgba(63, 81, 181, 0.2);
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--white);
            overflow-x: hidden;
        }

        /* Premium Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--white) 0%, var(--light-gray) 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 15% 25%, rgba(63, 81, 181, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 85% 75%, rgba(63, 81, 181, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        .hero-section main {
            max-width: 1500px;
            margin: 0 auto;
            padding: 100px 24px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 80px;
            align-items: center;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }

        /* Left Content - Enhanced */
        .hero-content {
            max-width: 550px;
            position: relative;
        }

        .hero-content::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            opacity: 0.1;
            z-index: -1;
        }

        .hero-section span {
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            position: relative;
            opacity: 0;
            animation: slideUp 0.8s ease 0.2s forwards;
        }

        .hero-section span::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 2px;
            animation: expandLine 1s ease 1s forwards;
        }

        .hero-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 28px;
            line-height: 1.1;
            opacity: 0;
            animation: slideUp 0.8s ease 0.4s forwards;
        }

        .hero-section p {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 40px;
            line-height: 1.7;
            opacity: 0;
            animation: slideUp 0.8s ease 0.6s forwards;
        }

        .hero-section a {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--primary-color);
            color: var(--white);
            padding: 18px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition-smooth);
            border: 2px solid var(--primary-color);
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: slideUp 0.8s ease 0.8s forwards;
        }

        .hero-section a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .hero-section a:hover::before {
            left: 100%;
        }

        .hero-section a:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-strong);
        }

        .hero-section a i {
            transition: var(--transition-smooth);
        }

        .hero-section a:hover i {
            transform: translateX(6px);
        }

        /* Premium Swiper Container */
        .hero-swiper {
            width: 100%;
            height: 600px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-strong);
            background: var(--white);
            border: 1px solid var(--border-color);
            position: relative;
            opacity: 0;
            animation: slideInRight 1s ease 0.5s forwards;
        }

        /* Premium Slide Design */
        .swiper-slide {
            position: relative;
            background: var(--light-gray);
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .swiper-slide img,
        .swiper-slide[style*="background-image"] {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .swiper-slide:hover img,
        .swiper-slide:hover {
            transform: scale(1.08);
        }

        /* Premium Property Info Card - Bottom Overlay */
        .hero-slide-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(
                180deg, 
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.95) 40%,
                rgba(255, 255, 255, 0.98) 100%
            );
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0;
            transform: translateY(100%);
            opacity: 0;
            transition: var(--transition-smooth);
            z-index: 10;
        }

        /* Show overlay on hover */
        .swiper-slide:hover .hero-slide-overlay {
            transform: translateY(0);
            opacity: 1;
        }

        .hero-slide-link {
            color: inherit;
            text-decoration: none;
            display: block;
            padding: 24px 28px;
        }

        /* Property Content Layout */
        .hero-slide-content {
            width: 100%;
        }

        /* Property Header */
        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .property-main-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .hero-slide-price {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary-color);
            font-family: 'Inter', sans-serif;
        }

        .property-status {
            background: var(--primary-color);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Location */
        .hero-slide-location {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 18px;
        }

        .hero-slide-location i {
            color: var(--primary-color);
            font-size: 16px;
        }

        /* Key Features Grid */
        .property-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(63, 81, 181, 0.08);
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            border: 1px solid rgba(63, 81, 181, 0.1);
            transition: var(--transition-smooth);
        }

        .feature-item:hover {
            background: rgba(63, 81, 181, 0.12);
            transform: translateY(-1px);
        }

        .feature-item i {
            color: var(--primary-color);
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        /* Special Features Row */
        .special-features {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .special-tag {
            background: linear-gradient(135deg, var(--accent-color), #ffa726);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .facing-tag {
            background: linear-gradient(135deg, var(--secondary-color), #66bb6a);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Fallback Content */
        .fallback-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--white) 100%);
            padding: 60px 40px;
            text-align: center;
        }

        .fallback-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--text-dark);
            margin-bottom: 16px;
        }

        .fallback-content p {
            color: var(--text-light);
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .hero-cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: var(--white);
            padding: 14px 28px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition-smooth);
        }

        .hero-cta-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Premium Navigation */
        .swiper-button-next,
        .swiper-button-prev {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(63, 81, 181, 0.1);
            border-radius: 50%;
            color: var(--primary-color) !important;
            font-size: 18px;
            transition: var(--transition-bounce);
            box-shadow: var(--shadow-light);
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: var(--primary-color);
            color: var(--white) !important;
            border-color: var(--primary-color);
            transform: scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 18px;
            font-weight: 700;
        }

        /* Premium Pagination */
        .swiper-pagination {
            bottom: 24px !important;
        }

        .swiper-pagination-bullet {
            width: 14px;
            height: 14px;
            background: rgba(255, 255, 255, 0.6);
            border: 2px solid var(--white);
            opacity: 1;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-light);
        }

        .swiper-pagination-bullet-active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: scale(1.3);
            box-shadow: var(--shadow-medium);
        }

        /* Background Decorations */
        .bg, .bg2 {
            position: absolute;
            opacity: 0.04;
            z-index: 1;
            pointer-events: none;
            color: var(--primary-color);
            font-size: 200px;
        }

        .bg {
            right: -5%;
            bottom: -10%;
            animation: float 6s ease-in-out infinite;
        }

        .bg2 {
            left: -5%;
            top: -5%;
            font-size: 150px;
            animation: float 8s ease-in-out infinite reverse;
        }

        /* Animations */
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes expandLine {
            to { width: 80px; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-section main {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
                padding: 80px 20px;
            }

            .hero-swiper {
                height: 500px;
                max-width: 700px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .hero-section main {
                padding: 60px 16px;
                gap: 40px;
            }

            .hero-swiper {
                height: 450px;
                border-radius: 16px;
            }

            .hero-slide-link {
                padding: 20px 20px;
            }

            .property-features {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .feature-item {
                padding: 6px 10px;
                font-size: 12px;
            }

            .hero-slide-price {
                font-size: 20px;
            }

            .property-main-info h3 {
                font-size: 18px;
            }

            .swiper-button-next,
            .swiper-button-prev {
                width: 48px;
                height: 48px;
            }
        }

        @media (max-width: 480px) {
            .hero-swiper {
                height: 400px;
                border-radius: 12px;
            }

            .hero-slide-link {
                padding: 16px 16px;
            }

            .property-features {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .property-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .special-features {
                justify-content: center;
            }

            .bg, .bg2 {
                display: none;
            }
        }

        /* Performance optimizations */
        .swiper-slide {
            will-change: transform;
        }

        .hero-slide-overlay {
            will-change: transform, opacity;
        }
    </style>
</head>
<body>
    <section class="hero-section">
        <main>
            <div class="hero-content">
                <span>Discover</span>
                <h1>Your Dream Property</h1>
                <p>Find the perfect home that matches your lifestyle and budget. Browse through our carefully curated collection of premium properties in Guntur.</p>
                <a href="#properties">
                    <span>Explore Featured Properties</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <!-- Premium Property Carousel -->
            <div class="swiper hero-swiper">
                <div class="swiper-wrapper">
                    <!-- Sample Property 1 - Villa -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&h=700&fit=crop'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="#property-1" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <div class="property-header">
                                        <div class="property-main-info">
                                            <h3>Modern Villa with Garden</h3>
                                            <div class="hero-slide-price">₹85 Lakhs</div>
                                        </div>
                                        <div class="property-status">For Sale</div>
                                    </div>
                                    
                                    <div class="hero-slide-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Brodipet, Guntur</span>
                                    </div>
                                    
                                    <div class="property-features">
                                        <div class="feature-item">
                                            <i class="fas fa-bed"></i>
                                            <span>3 BHK</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bath"></i>
                                            <span>2 Bath</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>1200 sq ft</span>
                                        </div>
                                    </div>
                                    
                                    <div class="special-features">
                                        <span class="special-tag">Featured</span>
                                        <span class="facing-tag">East Facing</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sample Property 2 - Apartment -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=900&h=700&fit=crop'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="#property-2" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <div class="property-header">
                                        <div class="property-main-info">
                                            <h3>Luxury Apartment</h3>
                                            <div class="hero-slide-price">₹1.2 Cr</div>
                                        </div>
                                        <div class="property-status">For Sale</div>
                                    </div>
                                    
                                    <div class="hero-slide-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Lakshmipuram, Guntur</span>
                                    </div>
                                    
                                    <div class="property-features">
                                        <div class="feature-item">
                                            <i class="fas fa-bed"></i>
                                            <span>4 BHK</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bath"></i>
                                            <span>3 Bath</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>2000 sq ft</span>
                                        </div>
                                    </div>
                                    
                                    <div class="special-features">
                                        <span class="special-tag">Premium</span>
                                        <span class="facing-tag">North Facing</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sample Property 3 - House -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=900&h=700&fit=crop'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="#property-3" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <div class="property-header">
                                        <div class="property-main-info">
                                            <h3>Cozy Family Home</h3>
                                            <div class="hero-slide-price">₹45 Lakhs</div>
                                        </div>
                                        <div class="property-status">For Sale</div>
                                    </div>
                                    
                                    <div class="hero-slide-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Kothapet, Guntur</span>
                                    </div>
                                    
                                    <div class="property-features">
                                        <div class="feature-item">
                                            <i class="fas fa-bed"></i>
                                            <span>2 BHK</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bath"></i>
                                            <span>2 Bath</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>900 sq ft</span>
                                        </div>
                                    </div>
                                    
                                    <div class="special-features">
                                        <span class="special-tag">Best Deal</span>
                                        <span class="facing-tag">South Facing</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Rental Property -->
                    <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=900&h=700&fit=crop'); background-size: cover; background-position: center;">
                        <div class="hero-slide-overlay">
                            <a href="#property-4" class="hero-slide-link">
                                <div class="hero-slide-content">
                                    <div class="property-header">
                                        <div class="property-main-info">
                                            <h3>Premium Rental</h3>
                                            <div class="hero-slide-price">₹25K/mo</div>
                                        </div>
                                        <div class="property-status" style="background: var(--accent-color);">For Rent</div>
                                    </div>
                                    
                                    <div class="hero-slide-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Arundelpet, Guntur</span>
                                    </div>
                                    
                                    <div class="property-features">
                                        <div class="feature-item">
                                            <i class="fas fa-bed"></i>
                                            <span>3 BHK</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bath"></i>
                                            <span>2 Bath</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>1100 sq ft</span>
                                        </div>
                                    </div>
                                    
                                    <div class="special-features">
                                        <span class="special-tag">Furnished</span>
                                        <span class="facing-tag">West Facing</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Fallback Slide -->
                    <div class="swiper-slide fallback-slide">
                        <div class="fallback-content">
                            <h2>Featured Properties Coming Soon</h2>
                            <p>We're working hard to bring you the best featured properties in Guntur. Check back soon for amazing deals!</p>
                            <a href="#all-properties" class="hero-cta-btn">
                                <span>View All Properties</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Premium Navigation -->
                <div class="swiper-pagination hero-pagination"></div>
                <div class="swiper-button-next hero-nav-next"></div>
                <div class="swiper-button-prev hero-nav-prev"></div>
            </div>
            
            <!-- Background Decorations -->
            <i class="fas fa-home bg"></i>
            <i class="fas fa-building bg2"></i>
        </main>
    </section>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Premium Carousel Controller
        class PremiumCarouselController {
            constructor() {
                this.swiper = null;
                this.isAutoplayPaused = false;
                this.touchStartY = 0;
                this.init();
            }

            init() {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.initializeCarousel());
                } else {
                    this.initializeCarousel();
                }
            }

            initializeCarousel() {
                const swiperContainer = document.querySelector('.hero-swiper');
                if (!swiperContainer || typeof Swiper === 'undefined') {
                    console.warn('Swiper container not found or Swiper not loaded');
                    return;
                }

                this.swiper = new Swiper('.hero-swiper', {
                    // Core settings
                    loop: true,
                    speed: 1000,
                    
                    // Autoplay with smart pausing
                    autoplay: {
                        delay: 6000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    
                    // Premium effect
                    effect: 'coverflow',
                    coverflowEffect: {
                        rotate: 15,
                        stretch: 0,
                        depth: 200,
                        modifier: 1.5,
                        slideShadows: true,
                    },
                    
                    // Fallback to fade on mobile for performance
                    breakpoints: {
                        768: {
                            effect: 'fade',
                            fadeEffect: {
                                crossFade: true
                            }
                        }
                    },
                    
                    // Navigation
                    navigation: {
                        nextEl: '.hero-nav-next',
                        prevEl: '.hero-nav-prev',
                    },
                    
                    // Pagination
                    pagination: {
                        el: '.hero-pagination',
                        clickable: true,
                        dynamicBullets: true,
                        dynamicMainBullets: 3,
                    },
                    
                    // Interaction
                    keyboard: {
                        enabled: true,
                        onlyInViewport: true,
                    },
                    
                    mousewheel: {
                        thresholdDelta: 70,
                    },
                    
                    // Accessibility
                    a11y: {
                        prevSlideMessage: 'Previous property',
                        nextSlideMessage: 'Next property',
                        paginationBulletMessage: 'Go to property {{index}}',
                    },
                    
                    // Events
                    on: {
                        init: () => this.onSwiperInit(),
                        slideChangeTransitionStart: () => this.onSlideChangeStart(),
                        slideChangeTransitionEnd: () => this.onSlideChangeEnd(),
                        touchStart: (swiper, event) => this.onTouchStart(event),
                        autoplayPause: () => this.onAutoplayPause(),
                        autoplayResume: () => this.onAutoplayResume(),
                    }
                });

                this.setupAdvancedInteractions();
                this.setupResponsiveHandling();
                this.setupPerformanceOptimizations();
            }

            onSwiperInit() {
                console.log('Premium Carousel initialized');
                this.preloadImages();
                this.setupHoverEffects();
                this.setupKeyboardShortcuts();
            }

            onSlideChangeStart() {
                // Add slide transition effects
                this.addSlideTransitionEffect();
            }

            onSlideChangeEnd() {
                // Log analytics or perform actions after slide change
                const activeIndex = this.swiper.activeIndex;
                console.log('Active slide:', activeIndex);
            }

            onTouchStart(event) {
                this.touchStartY = event.touches[0].clientY;
            }

            onAutoplayPause() {
                this.isAutoplayPaused = true;
                this.showAutoplayIndicator(false);
            }

            onAutoplayResume() {
                this.isAutoplayPaused = false;
                this.showAutoplayIndicator(true);
            }

            // Advanced hover effects
            setupHoverEffects() {
                const slides = document.querySelectorAll('.swiper-slide');
                
                slides.forEach((slide, index) => {
                    const overlay = slide.querySelector('.hero-slide-overlay');
                    const propertyFeatures = slide.querySelectorAll('.feature-item');
                    
                    if (overlay) {
                        // Enhanced mouse enter
                        slide.addEventListener('mouseenter', () => {
                            this.showPropertyDetails(slide, overlay, propertyFeatures);
                            // Pause autoplay on hover for better UX
                            if (this.swiper.autoplay) {
                                this.swiper.autoplay.stop();
                            }
                        });
                        
                        // Enhanced mouse leave
                        slide.addEventListener('mouseleave', () => {
                            this.hidePropertyDetails(slide, overlay, propertyFeatures);
                            // Resume autoplay
                            if (this.swiper.autoplay) {
                                this.swiper.autoplay.start();
                            }
                        });
                        
                        // Touch support for mobile
                        slide.addEventListener('touchstart', (e) => {
                            e.preventDefault();
                            this.showPropertyDetails(slide, overlay, propertyFeatures);
                        });
                    }
                });

                // Hide details when touching outside on mobile
                document.addEventListener('touchstart', (e) => {
                    if (!e.target.closest('.swiper-slide')) {
                        this.hideAllPropertyDetails();
                    }
                });
            }

            showPropertyDetails(slide, overlay, features) {
                // Smooth slide up animation
                overlay.style.transform = 'translateY(0)';
                overlay.style.opacity = '1';
                
                // Stagger animation for feature items
                features.forEach((feature, index) => {
                    setTimeout(() => {
                        feature.style.transform = 'translateY(0) scale(1)';
                        feature.style.opacity = '1';
                    }, index * 50);
                });
                
                // Add subtle image zoom
                const slideImage = slide.querySelector('img') || slide;
                if (slideImage) {
                    slideImage.style.transform = 'scale(1.08)';
                }
            }

            hidePropertyDetails(slide, overlay, features) {
                // Smooth slide down animation
                overlay.style.transform = 'translateY(100%)';
                overlay.style.opacity = '0';
                
                // Reset feature items
                features.forEach(feature => {
                    feature.style.transform = 'translateY(10px) scale(0.95)';
                    feature.style.opacity = '0';
                });
                
                // Reset image zoom
                const slideImage = slide.querySelector('img') || slide;
                if (slideImage) {
                    slideImage.style.transform = 'scale(1)';
                }
            }

            hideAllPropertyDetails() {
                const slides = document.querySelectorAll('.swiper-slide');
                slides.forEach(slide => {
                    const overlay = slide.querySelector('.hero-slide-overlay');
                    const features = slide.querySelectorAll('.feature-item');
                    if (overlay) {
                        this.hidePropertyDetails(slide, overlay, features);
                    }
                });
            }

            // Image preloading for smooth transitions
            preloadImages() {
                const slides = document.querySelectorAll('.swiper-slide[style*="background-image"]');
                slides.forEach(slide => {
                    const bgImage = slide.style.backgroundImage;
                    if (bgImage) {
                        const img = new Image();
                        const urlMatch = bgImage.match(/url\(['"]?(.*?)['"]?\)/);
                        if (urlMatch) {
                            img.src = urlMatch[1];
                            img.onload = () => {
                                slide.classList.add('loaded');
                            };
                        }
                    }
                });
            }

            // Advanced interactions
            setupAdvancedInteractions() {
                // Double-tap to go to property details
                let tapCount = 0;
                document.querySelectorAll('.swiper-slide').forEach(slide => {
                    slide.addEventListener('touchend', (e) => {
                        tapCount++;
                        if (tapCount === 1) {
                            setTimeout(() => {
                                if (tapCount === 1) {
                                    // Single tap - show details
                                    const overlay = slide.querySelector('.hero-slide-overlay');
                                    const features = slide.querySelectorAll('.feature-item');
                                    if (overlay) {
                                        this.showPropertyDetails(slide, overlay, features);
                                    }
                                } else if (tapCount === 2) {
                                    // Double tap - go to property page
                                    const link = slide.querySelector('.hero-slide-link');
                                    if (link) {
                                        window.location.href = link.href;
                                    }
                                }
                                tapCount = 0;
                            }, 300);
                        }
                    });
                });
            }

            // Keyboard shortcuts
            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    if (!this.swiper) return;
                    
                    switch(e.key) {
                        case ' ': // Spacebar to pause/resume
                            e.preventDefault();
                            if (this.isAutoplayPaused) {
                                this.swiper.autoplay.start();
                            } else {
                                this.swiper.autoplay.stop();
                            }
                            break;
                        case 'h': // Show help
                            this.showKeyboardHelp();
                            break;
                    }
                });
            }

            // Slide transition effects
            addSlideTransitionEffect() {
                const activeSlide = document.querySelector('.swiper-slide-active');
                if (activeSlide) {
                    // Add ripple effect
                    this.createRippleEffect(activeSlide);
                }
            }

            createRippleEffect(slide) {
                const ripple = document.createElement('div');
                ripple.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 0;
                    height: 0;
                    border-radius: 50%;
                    background: radial-gradient(circle, rgba(63, 81, 181, 0.3), transparent);
                    transform: translate(-50%, -50%);
                    animation: ripple 1s ease-out;
                    pointer-events: none;
                    z-index: 5;
                `;
                
                slide.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 1000);
            }

            // Performance optimizations
            setupPerformanceOptimizations() {
                // Intersection Observer for performance
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Start autoplay when carousel is visible
                            if (this.swiper.autoplay && !this.isAutoplayPaused) {
                                this.swiper.autoplay.start();
                            }
                        } else {
                            // Pause autoplay when carousel is not visible
                            if (this.swiper.autoplay) {
                                this.swiper.autoplay.stop();
                            }
                        }
                    });
                }, { threshold: 0.5 });

                const swiperContainer = document.querySelector('.hero-swiper');
                if (swiperContainer) {
                    observer.observe(swiperContainer);
                }
            }

            // Responsive handling
            setupResponsiveHandling() {
                let resizeTimeout;
                
                const handleResize = () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        if (this.swiper) {
                            this.swiper.update();
                        }
                        this.handleResponsiveChanges();
                    }, 250);
                };
                
                window.addEventListener('resize', handleResize);
                this.handleResponsiveChanges();
            }

            handleResponsiveChanges() {
                const isMobile = window.innerWidth < 768;
                const isTablet = window.innerWidth < 1024 && window.innerWidth >= 768;
                
                // Adjust settings based on device
                if (this.swiper) {
                    if (isMobile) {
                        // More frequent updates on mobile
                        this.swiper.params.autoplay.delay = 4000;
                    } else {
                        this.swiper.params.autoplay.delay = 6000;
                    }
                }
            }

            // Utility methods
            showAutoplayIndicator(isPlaying) {
                // Could add a visual indicator for autoplay status
                console.log(isPlaying ? 'Autoplay resumed' : 'Autoplay paused');
            }

            showKeyboardHelp() {
                console.log('Keyboard shortcuts: Space (pause/resume), Arrow keys (navigate), H (help)');
            }

            // Public API
            pause() {
                if (this.swiper && this.swiper.autoplay) {
                    this.swiper.autoplay.stop();
                }
            }

            resume() {
                if (this.swiper && this.swiper.autoplay) {
                    this.swiper.autoplay.start();
                }
            }

            goToSlide(index) {
                if (this.swiper) {
                    this.swiper.slideTo(index);
                }
            }

            destroy() {
                if (this.swiper) {
                    this.swiper.destroy(true, true);
                    this.swiper = null;
                }
            }
        }

        // Initialize Premium Carousel
        const premiumCarousel = new PremiumCarouselController();

        // Global API
        window.PremiumCarousel = {
            instance: premiumCarousel,
            pause: () => premiumCarousel.pause(),
            resume: () => premiumCarousel.resume(),
            goTo: (index) => premiumCarousel.goToSlide(index),
            destroy: () => premiumCarousel.destroy()
        };

        // Handle page visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                premiumCarousel.pause();
            } else {
                premiumCarousel.resume();
            }
        });

        // Add dynamic styles for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    width: 300px;
                    height: 300px;
                    opacity: 0;
                }
            }
            
            .feature-item {
                transform: translateY(10px) scale(0.95);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            
            .swiper-slide.loaded {
                background-size: cover !important;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
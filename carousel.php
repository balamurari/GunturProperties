<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Property Carousel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3f51b5;
            --primary-dark: #303f9f;
            --secondary-color: #4caf50;
            --accent-color: #ff9800;
            --white: #ffffff;
            --black: #000000;
            --light-gray: #f8f9fa;
            --dark-gray: #333333;
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 15px 50px rgba(0, 0, 0, 0.2);
            --gradient-overlay: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .carousel-wrapper {
            width: 100%;
            max-width: 1400px;
            position: relative;
        }

        .carousel-header {
            text-align: center;
            margin-bottom: 40px;
            color: var(--white);
        }

        .carousel-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--white) 0%, #e3f2fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 20px rgba(255,255,255,0.3);
        }

        .carousel-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            height: 600px;
            border-radius: 24px;
            overflow: hidden;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-heavy);
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            transition: var(--transition);
        }

        .carousel-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            overflow: hidden;
            transform: translateX(100%) scale(0.8);
            opacity: 0;
            transition: var(--transition);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .carousel-slide.active {
            transform: translateX(0) scale(1);
            opacity: 1;
            z-index: 3;
        }

        .carousel-slide.prev {
            transform: translateX(-30%) scale(0.85);
            opacity: 0.7;
            z-index: 2;
            filter: blur(2px);
        }

        .carousel-slide.next {
            transform: translateX(30%) scale(0.85);
            opacity: 0.7;
            z-index: 2;
            filter: blur(2px);
        }

        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-overlay);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
            color: var(--white);
        }

        .property-badge {
            position: absolute;
            top: 30px;
            left: 30px;
            background: var(--accent-color);
            color: var(--white);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: var(--shadow-light);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        .property-price {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .property-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .property-location {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .property-features {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .property-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: var(--transition);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: var(--white);
            box-shadow: var(--shadow-light);
        }

        .btn-primary:hover {
            background: #43a047;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: var(--white);
            border: 2px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Navigation Controls */
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .carousel-nav.prev {
            left: 20px;
        }

        .carousel-nav.next {
            right: 20px;
        }

        .nav-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: var(--white);
            font-size: 1.4rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-light);
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        .nav-btn:active {
            transform: scale(0.95);
        }

        /* Dots Navigation */
        .carousel-dots {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .dot.active {
            background: var(--white);
            transform: scale(1.2);
            box-shadow: 0 0 20px rgba(255,255,255,0.8);
        }

        .dot::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border: 2px solid transparent;
            border-radius: 50%;
            transition: var(--transition);
        }

        .dot.active::before {
            border-color: rgba(255,255,255,0.5);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        /* Progress Bar */
        .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: var(--accent-color);
            transition: width 5s linear;
            z-index: 10;
        }

        /* Property Count */
        .property-count {
            position: absolute;
            top: 30px;
            right: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 10px 16px;
            border-radius: 20px;
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 10;
        }

        /* Loading Animation */
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .carousel-container {
                height: 500px;
            }
            
            .slide-overlay {
                padding: 30px;
            }
            
            .property-price {
                font-size: 2rem;
            }
            
            .property-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .carousel-header h1 {
                font-size: 2.2rem;
            }
            
            .carousel-container {
                height: 450px;
                border-radius: 16px;
            }
            
            .slide-overlay {
                padding: 25px;
            }
            
            .property-price {
                font-size: 1.8rem;
            }
            
            .property-title {
                font-size: 1.3rem;
            }
            
            .property-features {
                gap: 15px;
            }
            
            .nav-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .carousel-nav.prev {
                left: 15px;
            }
            
            .carousel-nav.next {
                right: 15px;
            }
            
            .property-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .carousel-header h1 {
                font-size: 1.8rem;
            }
            
            .carousel-header p {
                font-size: 1rem;
            }
            
            .carousel-container {
                height: 400px;
                border-radius: 12px;
            }
            
            .slide-overlay {
                padding: 20px;
            }
            
            .property-badge {
                top: 20px;
                left: 20px;
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            
            .property-count {
                top: 20px;
                right: 20px;
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            
            .property-price {
                font-size: 1.5rem;
            }
            
            .property-title {
                font-size: 1.1rem;
            }
            
            .property-location {
                font-size: 1rem;
            }
            
            .feature {
                font-size: 0.85rem;
            }
        }

        /* Touch Gestures */
        .carousel-container {
            touch-action: pan-y pinch-zoom;
        }

        /* Accessibility */
        .carousel-slide:focus {
            outline: 3px solid var(--accent-color);
            outline-offset: 4px;
        }

        .nav-btn:focus,
        .dot:focus,
        .btn:focus {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .slide-overlay {
                background: rgba(0,0,0,0.9);
            }
            
            .property-badge {
                background: var(--black);
                border: 2px solid var(--white);
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="carousel-wrapper">
        <div class="carousel-header">
            <h1>Premium Properties</h1>
            <p>Discover Your Dream Home</p>
        </div>
        
        <div class="carousel-container">
            <div class="loading" id="loading">
                <div class="spinner"></div>
            </div>
            
            <div class="property-count">
                <span id="current-slide">1</span> / <span id="total-slides">5</span>
            </div>
            
            <div class="carousel" id="carousel">
                <!-- Slides will be dynamically generated -->
            </div>
            
            <div class="carousel-nav prev" id="prevBtn">
                <div class="nav-btn">
                    <i class="fas fa-chevron-left"></i>
                </div>
            </div>
            
            <div class="carousel-nav next" id="nextBtn">
                <div class="nav-btn">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="carousel-dots" id="dotsContainer">
                <!-- Dots will be dynamically generated -->
            </div>
            
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div>

    <script>
        class PremiumCarousel {
            constructor() {
                this.properties = [
                    {
                        id: 1,
                        image: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
                        price: '₹2,45,00,000',
                        title: 'Luxury Villa with Garden',
                        location: 'Guntur, Andhra Pradesh',
                        badge: 'Featured',
                        beds: 4,
                        baths: 3,
                        area: '2,400',
                        type: 'Villa'
                    },
                    {
                        id: 2,
                        image: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
                        price: '₹1,85,00,000',
                        title: 'Modern Apartment Complex',
                        location: 'Vijayawada, Andhra Pradesh',
                        badge: 'New Launch',
                        beds: 3,
                        baths: 2,
                        area: '1,800',
                        type: 'Apartment'
                    },
                    {
                        id: 3,
                        image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1475&q=80',
                        price: '₹3,20,00,000',
                        title: 'Contemporary Family Home',
                        location: 'Amaravati, Andhra Pradesh',
                        badge: 'Premium',
                        beds: 5,
                        baths: 4,
                        area: '3,200',
                        type: 'House'
                    },
                    {
                        id: 4,
                        image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1453&q=80',
                        price: '₹1,25,00,000',
                        title: 'Cozy Suburban House',
                        location: 'Tenali, Andhra Pradesh',
                        badge: 'Best Value',
                        beds: 2,
                        baths: 2,
                        area: '1,200',
                        type: 'House'
                    },
                    {
                        id: 5,
                        image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80',
                        price: '₹4,50,00,000',
                        title: 'Luxury Penthouse Suite',
                        location: 'Visakhapatnam, Andhra Pradesh',
                        badge: 'Exclusive',
                        beds: 4,
                        baths: 3,
                        area: '2,800',
                        type: 'Penthouse'
                    }
                ];

                this.currentSlide = 0;
                this.isPlaying = true;
                this.interval = null;
                this.startX = 0;
                this.startY = 0;
                this.distX = 0;
                this.distY = 0;
                this.threshold = 150;

                this.init();
            }

            init() {
                this.createSlides();
                this.createDots();
                this.setupEventListeners();
                this.startAutoPlay();
                this.hideLoading();
            }

            createSlides() {
                const carousel = document.getElementById('carousel');
                carousel.innerHTML = '';

                this.properties.forEach((property, index) => {
                    const slide = document.createElement('div');
                    slide.className = `carousel-slide ${index === 0 ? 'active' : ''}`;
                    slide.style.backgroundImage = `url(${property.image})`;
                    slide.setAttribute('tabindex', '0');
                    slide.setAttribute('role', 'img');
                    slide.setAttribute('aria-label', property.title);

                    slide.innerHTML = `
                        <div class="slide-overlay">
                            <div class="property-badge">${property.badge}</div>
                            <div class="property-price">${property.price}</div>
                            <div class="property-title">${property.title}</div>
                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                ${property.location}
                            </div>
                            <div class="property-features">
                                <div class="feature">
                                    <i class="fas fa-bed"></i>
                                    ${property.beds} Beds
                                </div>
                                <div class="feature">
                                    <i class="fas fa-bath"></i>
                                    ${property.baths} Baths
                                </div>
                                <div class="feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    ${property.area} sq ft
                                </div>
                                <div class="feature">
                                    <i class="fas fa-home"></i>
                                    ${property.type}
                                </div>
                            </div>
                            <div class="property-actions">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-secondary">
                                    <i class="fas fa-heart"></i>
                                    Save Property
                                </a>
                            </div>
                        </div>
                    `;

                    carousel.appendChild(slide);
                });

                document.getElementById('total-slides').textContent = this.properties.length;
            }

            createDots() {
                const dotsContainer = document.getElementById('dotsContainer');
                dotsContainer.innerHTML = '';

                this.properties.forEach((_, index) => {
                    const dot = document.createElement('div');
                    dot.className = `dot ${index === 0 ? 'active' : ''}`;
                    dot.setAttribute('tabindex', '0');
                    dot.setAttribute('role', 'button');
                    dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
                    dot.addEventListener('click', () => this.goToSlide(index));
                    dot.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.goToSlide(index);
                        }
                    });
                    dotsContainer.appendChild(dot);
                });
            }

            updateSlides() {
                const slides = document.querySelectorAll('.carousel-slide');
                const dots = document.querySelectorAll('.dot');

                slides.forEach((slide, index) => {
                    slide.classList.remove('active', 'prev', 'next');
                    
                    if (index === this.currentSlide) {
                        slide.classList.add('active');
                    } else if (index === this.getPrevIndex()) {
                        slide.classList.add('prev');
                    } else if (index === this.getNextIndex()) {
                        slide.classList.add('next');
                    }
                });

                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentSlide);
                });

                document.getElementById('current-slide').textContent = this.currentSlide + 1;
                this.updateProgressBar();
            }

            getPrevIndex() {
                return this.currentSlide === 0 ? this.properties.length - 1 : this.currentSlide - 1;
            }

            getNextIndex() {
                return this.currentSlide === this.properties.length - 1 ? 0 : this.currentSlide + 1;
            }

            goToSlide(index) {
                this.currentSlide = index;
                this.updateSlides();
                this.resetAutoPlay();
            }

            nextSlide() {
                this.currentSlide = this.getNextIndex();
                this.updateSlides();
            }

            prevSlide() {
                this.currentSlide = this.getPrevIndex();
                this.updateSlides();
            }

            startAutoPlay() {
                if (this.isPlaying) {
                    this.interval = setInterval(() => {
                        this.nextSlide();
                    }, 5000);
                    this.updateProgressBar();
                }
            }

            stopAutoPlay() {
                if (this.interval) {
                    clearInterval(this.interval);
                    this.interval = null;
                }
                this.resetProgressBar();
            }

            resetAutoPlay() {
                this.stopAutoPlay();
                this.startAutoPlay();
            }

            updateProgressBar() {
                const progressBar = document.getElementById('progressBar');
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = '100%';
                }, 50);
            }

            resetProgressBar() {
                const progressBar = document.getElementById('progressBar');
                progressBar.style.width = '0%';
            }

            setupEventListeners() {
                // Navigation buttons
                document.getElementById('prevBtn').addEventListener('click', () => {
                    this.prevSlide();
                    this.resetAutoPlay();
                });

                document.getElementById('nextBtn').addEventListener('click', () => {
                    this.nextSlide();
                    this.resetAutoPlay();
                });

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') {
                        this.prevSlide();
                        this.resetAutoPlay();
                    } else if (e.key === 'ArrowRight') {
                        this.nextSlide();
                        this.resetAutoPlay();
                    } else if (e.key === ' ') {
                        e.preventDefault();
                        this.isPlaying ? this.stopAutoPlay() : this.startAutoPlay();
                        this.isPlaying = !this.isPlaying;
                    }
                });

                // Pause on hover
                const container = document.querySelector('.carousel-container');
                container.addEventListener('mouseenter', () => {
                    this.stopAutoPlay();
                });

                container.addEventListener('mouseleave', () => {
                    if (this.isPlaying) {
                        this.startAutoPlay();
                    }
                });

                // Touch gestures
                container.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
                container.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: true });
                container.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });

                // Resize handler
                window.addEventListener('resize', () => {
                    this.updateSlides();
                });
            }

            handleTouchStart(e) {
                this.startX = e.touches[0].clientX;
                this.startY = e.touches[0].clientY;
            }

            handleTouchMove(e) {
                if (!this.startX || !this.startY) return;

                this.distX = e.touches[0].clientX - this.startX;
                this.distY = e.touches[0].clientY - this.startY;
            }

            handleTouchEnd(e) {
                if (!this.distX || !this.distY) return;

                if (Math.abs(this.distX) > Math.abs(this.distY)) {
                    if (Math.abs(this.distX) > this.threshold) {
                        if (this.distX > 0) {
                            this.prevSlide();
                        } else {
                            this.nextSlide();
                        }
                        this.resetAutoPlay();
                    }
                }

                this.startX = 0;
                this.startY = 0;
                this.distX = 0;
                this.distY = 0;
            }

            hideLoading() {
                setTimeout(() => {
                    const loading = document.getElementById('loading');
                    loading.style.opacity = '0';
                    setTimeout(() => {
                        loading.style.display = 'none';
                    }, 300);
                }, 1000);
            }
        }

        // Initialize carousel when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new PremiumCarousel();
        });

        // Prevent context menu on images
        document.addEventListener('contextmenu', (e) => {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
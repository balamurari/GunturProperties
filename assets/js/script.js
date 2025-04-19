/**
 * Guntur Properties - Main JavaScript File
 * Version: 1.3
 */

//this is realted to the navbar logo js
// Reset animation on logo hover
document.addEventListener('DOMContentLoaded', function() {
    const logo = document.querySelector('.logo');
    const animatedElements = document.querySelectorAll('.floor, .roof, .crane-base, .crane-arm, .crane-line');
    
    if (logo && animatedElements.length) {
      logo.addEventListener('mouseenter', () => {
        animatedElements.forEach(el => {
          el.style.animation = 'none';
          setTimeout(() => {
            el.style.animation = '';
          }, 10);
        });
      });
    }
  });

// Single event listener for DOM content loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize twinkling stars if they exist in the page
    initStarsAnimation();
    
    // Initialize stats counter animation
    initStatsCounter();
    
    // Initialize mobile navigation
    initMobileNav();
    
    // Initialize both slider types (use only one or both depending on your design)
    // Original property slider (can be removed if using only the gallery slider)
    // initPropertySlider(); 
    
    // New gallery slider (the 3-photo layout)
    initPropertyGallery();
    
    // Dropdown Filters
    initDropdownFilters();
    
    // Favorite Toggle
    initFavoriteToggle();
});

/**
 * Initialize Property Gallery Slider
 */
function initPropertyGallery() {
    const slides = document.querySelectorAll('.property-slide');
    const dots = document.querySelectorAll('.property-gallery-container .slider-dots .dot');
    const nextBtn = document.querySelector('.property-gallery-container .next-btn');
    const prevBtn = document.querySelector('.property-gallery-container .prev-btn');
    
    if (!slides.length || !dots.length || !nextBtn || !prevBtn) return;
    
    let currentSlide = 0;
    let autoSlideInterval;
    
    // Show the specified slide
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
    }
    
    // Go to next slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Go to previous slide
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
    
    // Event listeners
    nextBtn.addEventListener('click', () => {
        nextSlide();
        resetAutoSlide();
    });
    
    prevBtn.addEventListener('click', () => {
        prevSlide();
        resetAutoSlide();
    });
    
    // Add click events to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            resetAutoSlide();
        });
    });
    
    // Auto slide functionality
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000);
    }
    
    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }
    
    // Initialize
    showSlide(currentSlide);
    startAutoSlide();
}

/**
 * Initialize Stars Animation
 */
function initStarsAnimation() {
    // Check if the hero section with stars exists
    const heroSection = document.querySelector('.hero-section');
    if (!heroSection) return;
    
    // Check if sparkle stars already exist
    if (document.querySelector('.section-tag .sparkle-star')) {
        // Sparkle stars are already in the HTML, no need to create them
        return;
    }
    
    // Look for section-tag to add sparkle stars
    const sectionTag = heroSection.querySelector('.section-tag');
    if (sectionTag) {
        // Clear existing content (dots)
        sectionTag.innerHTML = '';
        
        // Create sparkle stars
        const starLarge = document.createElement('div');
        starLarge.className = 'sparkle-star sparkle-star-large';
        starLarge.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        const starMedium = document.createElement('div');
        starMedium.className = 'sparkle-star sparkle-star-medium';
        starMedium.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        const starSmall = document.createElement('div');
        starSmall.className = 'sparkle-star sparkle-star-small';
        starSmall.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        // Add stars to section tag
        sectionTag.appendChild(starLarge);
        sectionTag.appendChild(starMedium);
        sectionTag.appendChild(starSmall);
    }
    
    // Also add stars to featured properties section if it exists
    const featuredSection = document.querySelector('.featured-properties .section-tag');
    if (featuredSection && !featuredSection.querySelector('.sparkle-star')) {
        // Clear existing content (dots)
        featuredSection.innerHTML = '';
        
        // Create sparkle stars
        const starLarge = document.createElement('div');
        starLarge.className = 'sparkle-star sparkle-star-large';
        starLarge.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        const starMedium = document.createElement('div');
        starMedium.className = 'sparkle-star sparkle-star-medium';
        starMedium.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        const starSmall = document.createElement('div');
        starSmall.className = 'sparkle-star sparkle-star-small';
        starSmall.innerHTML = '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 0L23.5 12.5L36 18L23.5 23.5L18 36L12.5 23.5L0 18L12.5 12.5L18 0Z" fill="#2048A8"/></svg>';
        
        // Add stars to featured section
        featuredSection.appendChild(starLarge);
        featuredSection.appendChild(starMedium);
        featuredSection.appendChild(starSmall);
    }
}

/**
 * Initialize Stats Counter Animation
 */
function initStatsCounter() {
    // Set up the Intersection Observer to trigger counting when stats are visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            // If the element is in the viewport
            if (entry.isIntersecting) {
                // Start the counting animations
                startCounting();
                // Once we've started the animation, no need to observe anymore
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 }); // Trigger when at least 50% of the stats section is visible

    // Observe the stats section
    const statsSection = document.querySelector('.hero-stats');
    if (statsSection) {
        observer.observe(statsSection);
    }
}

/**
 * Function to animate counting for all stats
 */
function startCounting() {
    // Get all stat elements
    const statElements = document.querySelectorAll('.stat-item h2');
    
    statElements.forEach(statElement => {
        // Get the target number (remove the '+' if present)
        const targetNumber = parseInt(statElement.textContent.replace(/\D/g, ''));
        // Store the original text to preserve any '+' or other symbols
        const originalText = statElement.textContent;
        const hasPlusSign = originalText.includes('+');
        
        // Calculate animation duration based on the number size
        // Larger numbers will take longer to count
        const duration = Math.min(2000, Math.max(1000, targetNumber * 3));
        
        // Start from 0
        let startNumber = 0;
        // If it's a particularly large number, don't start from 0 to make the animation smoother
        if (targetNumber > 1000) {
            startNumber = Math.floor(targetNumber * 0.7); // Start at 70% of the target
        }
        
        // Set the element to start number
        statElement.textContent = startNumber + (hasPlusSign ? '+' : '');
        
        // Add animating class for the scaling effect
        statElement.classList.add('animating');
        
        // Get the timestamp when animation starts
        const startTime = performance.now();
        
        // Animation function
        function updateCount(currentTime) {
            // Calculate how far through the animation we are (0 to 1)
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            
            // Use easeOutQuad easing function for more natural counting
            const easedProgress = 1 - (1 - progress) * (1 - progress);
            
            // Calculate the current number to display
            const currentNumber = Math.floor(startNumber + (targetNumber - startNumber) * easedProgress);
            
            // Update the element text
            statElement.textContent = currentNumber + (hasPlusSign ? '+' : '');
            
            // If we're not done, request another animation frame
            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                // Animation is complete, restore the original text to ensure accuracy
                statElement.textContent = originalText;
                // Remove the animating class
                setTimeout(() => {
                    statElement.classList.remove('animating');
                }, 200);
            }
        }
        
        // Start the animation
        requestAnimationFrame(updateCount);
    });
}

/**
 * Mobile Navigation Functionality
 */
function initMobileNav() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');
    
    // Check if mobile nav already exists
    if (document.querySelector('.mobile-nav')) {
        return;
    }
    
    if (menuToggle && nav) {
        // Create mobile nav overlay
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        document.body.appendChild(overlay);
        
        // Create mobile nav container
        const mobileNav = document.createElement('div');
        mobileNav.className = 'mobile-nav';
        
        // Clone the navigation menu
        mobileNav.appendChild(nav.cloneNode(true));
        
        // Add close button to mobile nav
        const closeBtn = document.createElement('button');
        closeBtn.className = 'mobile-nav-close';
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        mobileNav.insertBefore(closeBtn, mobileNav.firstChild);
        
        // Add contact button if it exists
        const contactBtn = document.querySelector('.contact-btn');
        if (contactBtn) {
            const mobileContactBtn = contactBtn.cloneNode(true);
            mobileNav.appendChild(mobileContactBtn);
        }
        
        document.body.appendChild(mobileNav);
        
        // Toggle mobile menu
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileNav.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu when clicking close button
        closeBtn.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
        });
        
        // Close menu when clicking overlay
        overlay.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
        });
        
        // Close menu when clicking on a nav link
        const navLinks = mobileNav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
        
        // Prevent closing when clicking inside the mobile nav
        mobileNav.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

/**
 * Initialize Property Slider
 */
function initPropertySlider() {
    const sliderContainer = document.querySelector('.property-slider');
    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');
    const dots = document.querySelectorAll('.slider-dots .dot');
    
    if (!sliderContainer || !nextBtn || !prevBtn) return;
    
    // Sample properties data (in a real scenario, this would come from backend)
    const properties = [
        {
            image: 'assets/images/carouselFront.png',
            title: 'Luxury Villa in Park Avenue',
            price: '$2,500,000',
            beds: 4,
            baths: 3,
            area: '3,500 sq ft'
        },
        {
            image: 'assets/images/carousel back 1.png',
            title: 'Modern Apartment Downtown',
            price: '$850,000',
            beds: 2,
            baths: 2,
            area: '1,200 sq ft'
        },
        {
            image: 'assets/images/carousel back 2.png',
            title: 'Seaside Cottage with Ocean View',
            price: '$1,200,000',
            beds: 3,
            baths: 2,
            area: '2,100 sq ft'
        }
    ];
    
    let currentSlide = 0;
    let autoSlideInterval;
    
    // Create slides from properties data
    function createSlides() {
        sliderContainer.innerHTML = '';
        
        properties.forEach((property, index) => {
            const slide = document.createElement('div');
            slide.className = 'property-slide';
            slide.style.display = index === currentSlide ? 'block' : 'none';
            
            slide.innerHTML = `
                <div class="property-card">
                    <div class="property-images">
                        <img src="${property.image}" alt="${property.title}">
                    </div>
                    <div class="property-favorite">
                        <button class="favorite-btn">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-details">
                        <a href="#" class="view-details-btn">View Property Details</a>
                    </div>
                </div>
            `;
            
            sliderContainer.appendChild(slide);
        });
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
        
        // Initialize favorite buttons
        initFavoriteToggle();
    }
    
    // Create initial slides
    createSlides();
    
    // Next slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % properties.length;
        createSlides();
    }
    
    // Previous slide
    function prevSlide() {
        currentSlide = (currentSlide - 1 + properties.length) % properties.length;
        createSlides();
    }
    
    // Add event listeners
    nextBtn.addEventListener('click', function() {
        nextSlide();
        resetAutoSlide();
    });
    
    prevBtn.addEventListener('click', function() {
        prevSlide();
        resetAutoSlide();
    });
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            currentSlide = index;
            createSlides();
            resetAutoSlide();
        });
    });
    
    // Start auto slide
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000);
    }
    
    // Reset auto slide timer
    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }
    
    // Initialize auto slide
    startAutoSlide();
}

/**
 * Initialize Dropdown Filters
 */
function initDropdownFilters() {
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');
    
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Create dropdown content if it doesn't exist
            let dropdown = this.nextElementSibling;
            
            if (!dropdown || !dropdown.classList.contains('dropdown-content')) {
                dropdown = document.createElement('div');
                dropdown.className = 'dropdown-content';
                
                // Sample filter options based on button type
                let options = [];
                
                if (this.textContent.includes('Property Type')) {
                    options = ['Apartment', 'House', 'Villa', 'Office', 'Land'];
                } else if (this.textContent.includes('Pricing Range')) {
                    options = ['Under $500,000', '$500,000 - $1,000,000', '$1,000,000 - $2,000,000', 'Above $2,000,000'];
                } else if (this.textContent.includes('Property Size')) {
                    options = ['Under 1,000 sq ft', '1,000 - 2,000 sq ft', '2,000 - 3,000 sq ft', 'Above 3,000 sq ft'];
                }
                
                // Create options
                options.forEach(option => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.textContent = option;
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        btn.querySelector('span') ? btn.querySelector('span').textContent = option : btn.innerHTML = `<i class="${btn.querySelector('i').className}"></i> ${option} <i class="fas fa-chevron-down"></i>`;
                        dropdown.classList.remove('active');
                    });
                    dropdown.appendChild(item);
                });
                
                this.parentNode.appendChild(dropdown);
            }
            
            // Toggle dropdown
            dropdown.classList.toggle('active');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-content.active').forEach(active => {
                if (active !== dropdown) {
                    active.classList.remove('active');
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) {
            document.querySelectorAll('.dropdown-content.active').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
}

/**
 * Initialize Favorite Toggle
 */
function initFavoriteToggle() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const icon = this.querySelector('i');
            
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#e74c3c';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        });
    });
}

// Optional: Allow manual triggering of the animation for testing
window.resetCountAnimation = function() {
    const statElements = document.querySelectorAll('.stat-item h2');
    statElements.forEach(el => {
        el.textContent = el.textContent.replace(/\d+/g, '0');
    });
    setTimeout(startCounting, 500);
};


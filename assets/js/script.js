/**
 * Guntur Properties - Main JavaScript File
 * Version: 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');
    
    if (menuToggle && nav) {
        // Create mobile nav container
        const mobileNav = document.createElement('div');
        mobileNav.className = 'mobile-nav';
        mobileNav.appendChild(nav.cloneNode(true));
        document.body.appendChild(mobileNav);
        
        // Toggle mobile menu
        menuToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileNav.contains(e.target) && !menuToggle.contains(e.target) && mobileNav.classList.contains('active')) {
                mobileNav.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
    }
    
    // Property Slider
    initPropertySlider();
    
    // Dropdown Filters
    initDropdownFilters();
    
    // Favorite Toggle
    initFavoriteToggle();
});

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
    nextBtn.addEventListener('click', function() {
        currentSlide = (currentSlide + 1) % properties.length;
        createSlides();
    });
    
    // Previous slide
    prevBtn.addEventListener('click', function() {
        currentSlide = (currentSlide - 1 + properties.length) % properties.length;
        createSlides();
    });
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            currentSlide = index;
            createSlides();
        });
    });
    
    // Auto slide every 5 seconds
    setInterval(function() {
        currentSlide = (currentSlide + 1) % properties.length;
        createSlides();
    }, 5000);
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
        btn.addEventListener('click', function() {
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

/**
 * Add CSS for elements created by JavaScript
 */
const dynamicStyles = document.createElement('style');
dynamicStyles.textContent = `
    .mobile-nav {
        position: fixed;
        top: 0;
        right: -300px;
        width: 300px;
        height: 100vh;
        background-color: var(--primary-color);
        z-index: 1001;
        padding: 60px 30px 30px;
        transition: right 0.3s ease;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .mobile-nav.active {
        right: 0;
    }
    
    .mobile-nav ul {
        flex-direction: column;
        gap: 20px;
    }
    
    .mobile-nav ul li a {
        display: block;
        padding: 10px 0;
        font-size: 18px;
    }
    
    body.menu-open {
        overflow: hidden;
    }
    
    .dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: var(--white);
        border: 1px solid var(--gray);
        border-radius: 5px;
        z-index: 10;
        box-shadow: var(--box-shadow);
        display: none;
        margin-top: 5px;
    }
    
    .dropdown-content.active {
        display: block;
    }
    
    .dropdown-content a {
        display: block;
        padding: 10px 15px;
        color: var(--text-dark);
        text-decoration: none;
        border-bottom: 1px solid var(--gray);
    }
    
    .dropdown-content a:last-child {
        border-bottom: none;
    }
    
    .dropdown-content a:hover {
        background-color: var(--gray-light);
    }
`;

document.head.appendChild(dynamicStyles);
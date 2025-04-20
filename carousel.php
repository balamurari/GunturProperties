<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Carousel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            max-width: 1200px;
            height: 450px; /* Adjusted to match the aspect ratio in your images */
            overflow: hidden;
            border-radius: 12px;
            margin: 0 auto;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .carousel-item {
            position: absolute;
            height: 100%;
            transition: all 0.6s ease;
            opacity: 0;
            overflow: hidden;
        }

        /* Left item (previous) positioning and styling */
        .carousel-item.prev {
            left: 0;
            width: 25%;
            height: 80%;
            top: 10%;
            z-index: 1;
            filter: blur(4px);
            opacity: 0.6;
            transform: translateX(0);
        }

        /* Active item (center) positioning and styling */
        .carousel-item.active {
            left: 20%;
            width: 60%;
            height: 100%;
            top: 0;
            z-index: 2;
            opacity: 1;
            filter: blur(0);
            transform: translateX(0);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Next item (right) positioning and styling */
        .carousel-item.next {
            left: 75%;
            width: 25%;
            height: 80%;
            top: 10%;
            z-index: 1;
            filter: blur(4px);
            opacity: 0.6;
            transform: translateX(0);
        }

        /* Hidden items */
        .carousel-item.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .slide-content {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        /* Property details button */
        .property-details {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: #3b71ca;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Navigation controls */
        .nav-controls {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            z-index: 10;
            gap: 6px;
        }

        .nav-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-dot.active {
            background-color: white;
        }

        /* Nav arrow buttons */
        .nav-arrows {
            position: absolute;
            width: 100%;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 10;
            pointer-events: none;
        }

        .arrow-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            pointer-events: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .arrow-btn:hover {
            background-color: #f8f9fa;
            transform: scale(1.05);
        }

        .arrow-btn.prev {
            margin-left: 5%;
        }

        .arrow-btn.next {
            margin-right: 5%;
        }

        /* Progress dots */
        .progress-dots {
            position: absolute;
            bottom: 15px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 5px;
            z-index: 5;
        }

        .dot {
            width: 30px;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        .dot.active {
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="carousel-container">
        <div class="carousel">
            <!-- Carousel items will be added dynamically -->
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
            <!-- Dots will be added dynamically -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Our slides data - using colors for demonstration
            const slides = [
                { color: '#FF5252', text: 'Slide 1' }, // Red
                { color: '#4CAF50', text: 'Slide 2' }, // Green
                { color: '#2196F3', text: 'Slide 3' }, // Blue
                { color: '#FFC107', text: 'Slide 4' }, // Amber
                { color: '#9C27B0', text: 'Slide 5' }  // Purple
            ];

            const carousel = document.querySelector('.carousel');
            const dotsContainer = document.querySelector('.progress-dots');
            const prevBtn = document.querySelector('.arrow-btn.prev');
            const nextBtn = document.querySelector('.arrow-btn.next');
            
            let currentIndex = 0;
            
            // Initialize carousel with slides
            function initCarousel() {
                // Add slides
                slides.forEach((slide, index) => {
                    const item = document.createElement('div');
                    item.classList.add('carousel-item');
                    
                    // Create slide content (will be replaced with image in real implementation)
                    item.innerHTML = `
                        <div class="slide-content" style="background-color: ${slide.color}">
                            ${slide.text}
                            ${index === currentIndex ? '<div class="property-details">View Property Details</div>' : ''}
                        </div>
                    `;
                    
                    carousel.appendChild(item);
                    
                    // Create progress dot
                    const dot = document.createElement('div');
                    dot.classList.add('dot');
                    if (index === currentIndex) dot.classList.add('active');
                    dot.addEventListener('click', () => goToSlide(index));
                    dotsContainer.appendChild(dot);
                });
                
                updateCarousel();
            }
            
            // Update carousel display
            function updateCarousel() {
                const items = document.querySelectorAll('.carousel-item');
                const dots = document.querySelectorAll('.dot');
                
                items.forEach((item, index) => {
                    // Remove all classes
                    item.classList.remove('active', 'prev', 'next', 'hidden');
                    
                    // Remove property details button if exists
                    const detailsBtn = item.querySelector('.property-details');
                    if (detailsBtn) {
                        detailsBtn.remove();
                    }
                    
                    // Set appropriate class based on position
                    if (index === currentIndex) {
                        item.classList.add('active');
                        // Add property details button to active slide
                        const content = item.querySelector('.slide-content');
                        const btn = document.createElement('div');
                        btn.classList.add('property-details');
                        btn.textContent = 'View Property Details';
                        content.appendChild(btn);
                    } else if (index === getPrevIndex()) {
                        item.classList.add('prev');
                    } else if (index === getNextIndex()) {
                        item.classList.add('next');
                    } else {
                        item.classList.add('hidden');
                    }
                });
                
                // Update dots
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
            
            // Helper functions for slide navigation
            function getPrevIndex() {
                return (currentIndex === 0) ? slides.length - 1 : currentIndex - 1;
            }
            
            function getNextIndex() {
                return (currentIndex === slides.length - 1) ? 0 : currentIndex + 1;
            }
            
            // Navigation functions
            function goToSlide(index) {
                currentIndex = index;
                updateCarousel();
            }
            
            function goToPrev() {
                currentIndex = getPrevIndex();
                updateCarousel();
            }
            
            function goToNext() {
                currentIndex = getNextIndex();
                updateCarousel();
            }
            
            // Event listeners
            prevBtn.addEventListener('click', goToPrev);
            nextBtn.addEventListener('click', goToNext);
            
            // Auto-slide functionality
            let autoSlideInterval = setInterval(goToNext, 5000);
            
            // Pause auto-slide on hover
            carousel.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });
            
            carousel.addEventListener('mouseleave', () => {
                autoSlideInterval = setInterval(goToNext, 5000);
            });
            
            // Initialize carousel
            initCarousel();
        });
    </script>
</body>
</html>
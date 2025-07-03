document.addEventListener('DOMContentLoaded', function() {
  // Initialize Swiper
  const heroSwiper = new Swiper('.hero-swiper', {
    // Basic parameters
    slidesPerView: 1,
    spaceBetween: 0,
    loop: true,
    grabCursor: true,
    speed: 800,
    
    // Autoplay configuration
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
    },
    
    // Effects
    effect: 'fade',
    fadeEffect: {
      crossFade: true
    },
    
    // Enable different transitions based on screen size
    breakpoints: {
      768: {
        effect: 'slide'
      },
      992: {
        effect: 'coverflow',
        coverflowEffect: {
          rotate: 5,
          stretch: 0,
          depth: 100,
          modifier: 1,
          slideShadows: true
        }
      }
    },
    
    // Navigation arrows
    navigation: {
      nextEl: '.hero-nav-next',
      prevEl: '.hero-nav-prev',
    },
    
    // Pagination
    pagination: {
      el: '.hero-pagination',
      clickable: true,
      dynamicBullets: true,
    },
    
    // Accessibility
    a11y: {
      prevSlideMessage: 'Previous property',
      nextSlideMessage: 'Next property',
      paginationBulletMessage: 'Go to property {{index}}'
    },
    
    // Events
    on: {
      init: function() {
        console.log('Hero Swiper initialized');
        setupHoverEffects();
      },
      slideChangeTransitionStart: function() {
        resetAllOverlays();
      }
    }
  });
  
  // Reset all overlays
  function resetAllOverlays() {
    document.querySelectorAll('.hero-slide-overlay').forEach(overlay => {
      overlay.style.opacity = '0';
      overlay.style.transform = 'translateY(100%)';
      overlay.style.background = 'transparent';
    });
  }
  
  // Enhanced hover effects
  function setupHoverEffects() {
    const slides = document.querySelectorAll('.swiper-slide');
    
    slides.forEach(slide => {
      // Show details on hover
      slide.addEventListener('mouseenter', () => {
        const overlay = slide.querySelector('.hero-slide-overlay');
        if (overlay) {
          overlay.style.background = 'rgba(255, 255, 255, 0.95)';
          overlay.style.opacity = '1';
          overlay.style.transform = 'translateY(0)';
          
          // Stagger animation for details
          const details = overlay.querySelectorAll('.hero-detail-item');
          details.forEach((item, index) => {
            setTimeout(() => {
              item.style.opacity = '1';
              item.style.transform = 'translateY(0)';
            }, 100 + (index * 50));
          });
        }
        
        // Pause autoplay for better UX
        if (heroSwiper.autoplay && heroSwiper.autoplay.running) {
          heroSwiper.autoplay.stop();
        }
      });
      
      // Hide details when mouse leaves
      slide.addEventListener('mouseleave', () => {
        const overlay = slide.querySelector('.hero-slide-overlay');
        if (overlay) {
          overlay.style.opacity = '0';
          overlay.style.transform = 'translateY(100%)';
          
          // Reset detail items
          const details = overlay.querySelectorAll('.hero-detail-item');
          details.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
          });
        }
        
        // Resume autoplay
        if (heroSwiper.autoplay && !heroSwiper.autoplay.running) {
          heroSwiper.autoplay.start();
        }
      });
    });
    
    // Initialize detail items as hidden
    document.querySelectorAll('.hero-detail-item').forEach(item => {
      item.style.opacity = '0';
      item.style.transform = 'translateY(10px)';
    });
  }
  
  // Handle page visibility changes
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (heroSwiper.autoplay) {
        heroSwiper.autoplay.stop();
      }
    } else {
      if (heroSwiper.autoplay) {
        heroSwiper.autoplay.start();
      }
    }
  });
  
  // Optimize performance with IntersectionObserver
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        if (heroSwiper.autoplay) {
          heroSwiper.autoplay.start();
        }
      } else {
        if (heroSwiper.autoplay) {
          heroSwiper.autoplay.stop();
        }
      }
    });
  }, { threshold: 0.3 });
  
  const swiperContainer = document.querySelector('.hero-swiper');
  if (swiperContainer) {
    observer.observe(swiperContainer);
  }
  
  // Add light parallax effect for background images
  window.addEventListener('mousemove', (e) => {
    const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
    const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
    
    const bg = document.querySelector('.bg');
    const bg2 = document.querySelector('.bg2');
    
    if (bg) {
      bg.style.transform = `translate(${moveX}px, ${moveY}px)`;
    }
    
    if (bg2) {
      bg2.style.transform = `translate(${-moveX}px, ${-moveY}px)`;
    }
  });
});
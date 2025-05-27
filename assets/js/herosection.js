// 🔥 EXTRAORDINARY HERO SECTION - NEXT LEVEL JAVASCRIPT 🔥

class HeroSectionController {
    constructor() {
        this.swiper = null;
        this.isInitialized = false;
        this.animations = new Map();
        this.observers = new Map();
        this.rafId = null;
        this.mousePosition = { x: 0, y: 0 };
        this.lastScrollY = 0;
        this.scrollVelocity = 0;
        
        this.init();
    }

    async init() {
        await this.waitForDOM();
        this.setupAdvancedAnimations();
        this.initializeSwiper();
        this.setupParallaxSystem();
        this.setupInteractiveEffects();
        this.setupPerformanceOptimizations();
        this.setupAdvancedScrollEffects();
        this.createParticleSystem();
        this.initializeCustomCursor();
        this.setupResponsiveHandlers();
        
        this.isInitialized = true;
        this.dispatchReadyEvent();
    }

    waitForDOM() {
        return new Promise(resolve => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', resolve);
            } else {
                resolve();
            }
        });
    }

    // 🎬 ADVANCED SWIPER WITH CINEMATIC EFFECTS
    initializeSwiper() {
        const swiperContainer = document.querySelector('.hero-swiper');
        if (!swiperContainer || typeof Swiper === 'undefined') {
            console.warn('Swiper not found or not loaded');
            return;
        }

        this.swiper = new Swiper('.hero-swiper', {
            // Core Settings
            loop: true,
            speed: 1200,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },

            // Advanced Effects
            effect: 'creative',
            creativeEffect: {
                prev: {
                    shadow: true,
                    translate: ['-20%', 0, -1],
                    rotate: [0, 0, -15],
                },
                next: {
                    translate: ['100%', 0, 0],
                },
            },

            // Fallback to fade for mobile
            breakpoints: {
                768: {
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    }
                }
            },

            // Navigation
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
                dynamicMainBullets: 3,
                renderBullet: (index, className) => {
                    return `<span class="${className}"><span class="bullet-inner"></span></span>`;
                }
            },

            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },

            // Advanced Callbacks
            on: {
                init: () => this.onSwiperInit(),
                slideChangeTransitionStart: () => this.onSlideChangeStart(),
                slideChangeTransitionEnd: () => this.onSlideChangeEnd(),
                touchStart: () => this.onTouchInteraction(),
                autoplayPause: () => this.onAutoplayPause(),
                autoplayResume: () => this.onAutoplayResume(),
            }
        });
    }

    onSwiperInit() {
        this.animateSlideContent(0);
        this.addSlideMouseEffects();
    }

    onSlideChangeStart() {
        const currentSlide = this.swiper.slides[this.swiper.activeIndex];
        this.createSlideTransitionEffect(currentSlide);
    }

    onSlideChangeEnd() {
        this.animateSlideContent(this.swiper.activeIndex);
    }

    onTouchInteraction() {
        this.createRippleEffect(event);
    }

    onAutoplayPause() {
        this.pauseBackgroundAnimations();
    }

    onAutoplayResume() {
        this.resumeBackgroundAnimations();
    }

    // 🎨 CINEMATIC SLIDE ANIMATIONS
    animateSlideContent(slideIndex, delay = 0) {
        const slide = this.swiper.slides[slideIndex];
        if (!slide) return;

        const content = slide.querySelector('.hero-slide-content');
        const details = slide.querySelectorAll('.hero-detail-item');
        
        if (content) {
            // Reset and animate content
            content.style.transform = 'translateY(50px) scale(0.9)';
            content.style.opacity = '0';
            
            setTimeout(() => {
                content.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                content.style.transform = 'translateY(0) scale(1)';
                content.style.opacity = '1';
            }, delay);
        }

        // Stagger detail items animation
        details.forEach((detail, index) => {
            detail.style.transform = 'translateX(-30px)';
            detail.style.opacity = '0';
            
            setTimeout(() => {
                detail.style.transition = `all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) ${index * 100}ms`;
                detail.style.transform = 'translateX(0)';
                detail.style.opacity = '1';
            }, delay + 200);
        });
    }

    createSlideTransitionEffect(slide) {
        const overlay = slide.querySelector('.hero-slide-overlay');
        if (!overlay) return;

        // Create shimmer effect
        const shimmer = document.createElement('div');
        shimmer.style.cssText = `
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            z-index: 10;
            transition: left 1s ease;
        `;
        
        overlay.appendChild(shimmer);
        
        setTimeout(() => {
            shimmer.style.left = '100%';
        }, 50);
        
        setTimeout(() => {
            shimmer.remove();
        }, 1100);
    }

    // 🌟 ADVANCED PARALLAX SYSTEM
    setupParallaxSystem() {
        const parallaxElements = [
            { selector: '.bg', speed: 0.3, rotation: 0.05 },
            { selector: '.bg2', speed: -0.2, rotation: -0.03 },
            { selector: '.hero-section::before', speed: 0.1 },
            { selector: '.hero-swiper', speed: 0.05 }
        ];

        const handleScroll = () => {
            this.scrollVelocity = window.pageYOffset - this.lastScrollY;
            this.lastScrollY = window.pageYOffset;

            parallaxElements.forEach(({ selector, speed, rotation }) => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    const yPos = this.lastScrollY * speed;
                    const rotateVal = rotation ? this.lastScrollY * rotation : 0;
                    
                    element.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${rotateVal}deg)`;
                });
            });

            this.updateScrollBasedAnimations();
        };

        // Throttled scroll handler
        let ticking = false;
        const scrollHandler = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        };

        window.addEventListener('scroll', scrollHandler, { passive: true });
    }

    updateScrollBasedAnimations() {
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        const rect = heroSection.getBoundingClientRect();
        const isVisible = rect.bottom > 0 && rect.top < window.innerHeight;
        
        if (isVisible) {
            const scrollProgress = Math.max(0, Math.min(1, -rect.top / rect.height));
            this.updateElementsBasedOnScroll(scrollProgress);
        }
    }

    updateElementsBasedOnScroll(progress) {
        const textElements = document.querySelectorAll('.hero-section h1, .hero-section p');
        const slideElements = document.querySelectorAll('.hero-slide-content');
        
        textElements.forEach(el => {
            const scale = 1 + (progress * 0.05);
            const opacity = 1 - (progress * 0.3);
            el.style.transform = `scale(${scale})`;
            el.style.opacity = Math.max(0.3, opacity);
        });
    }

    // 🎯 INTERACTIVE MOUSE EFFECTS
    setupInteractiveEffects() {
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        let isMouseInside = false;

        heroSection.addEventListener('mouseenter', () => {
            isMouseInside = true;
            this.startMouseTracking();
        });

        heroSection.addEventListener('mouseleave', () => {
            isMouseInside = false;
            this.stopMouseTracking();
            this.resetMouseEffects();
        });

        heroSection.addEventListener('mousemove', (e) => {
            if (!isMouseInside) return;
            
            const rect = heroSection.getBoundingClientRect();
            this.mousePosition = {
                x: (e.clientX - rect.left) / rect.width - 0.5,
                y: (e.clientY - rect.top) / rect.height - 0.5
            };
        });
    }

    startMouseTracking() {
        if (this.rafId) return;
        
        const animate = () => {
            this.updateMouseBasedAnimations();
            this.rafId = requestAnimationFrame(animate);
        };
        
        animate();
    }

    stopMouseTracking() {
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
            this.rafId = null;
        }
    }

    updateMouseBasedAnimations() {
        const { x, y } = this.mousePosition;
        
        // Update background elements
        const bg1 = document.querySelector('.bg');
        const bg2 = document.querySelector('.bg2');
        
        if (bg1) {
            const transform = `translate3d(${x * 30}px, ${y * 30}px, 0) rotate(${x * 10}deg)`;
            bg1.style.transform = transform;
        }
        
        if (bg2) {
            const transform = `translate3d(${x * -20}px, ${y * -20}px, 0) rotate(${x * -5}deg)`;
            bg2.style.transform = transform;
        }

        // Update slide content with magnetic effect
        const slideContents = document.querySelectorAll('.hero-slide-content');
        slideContents.forEach(content => {
            const magnetStrength = 5;
            const transform = `translate3d(${x * magnetStrength}px, ${y * magnetStrength}px, 0)`;
            content.style.transform = transform;
        });

        // Update gradient overlays
        this.updateGradientOverlays(x, y);
    }

    updateGradientOverlays(x, y) {
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        const gradientX = 50 + (x * 20);
        const gradientY = 50 + (y * 20);
        
        heroSection.style.background = `
            radial-gradient(circle at ${gradientX}% ${gradientY}%, rgba(102, 126, 234, 0.4) 0%, transparent 60%),
            radial-gradient(circle at ${100-gradientX}% ${100-gradientY}%, rgba(255, 107, 107, 0.3) 0%, transparent 60%),
            linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%)
        `;
    }

    resetMouseEffects() {
        const elements = document.querySelectorAll('.bg, .bg2, .hero-slide-content');
        elements.forEach(el => {
            el.style.transform = '';
        });

        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.background = '';
        }
    }

    // ✨ PARTICLE SYSTEM
    createParticleSystem() {
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        const canvas = document.createElement('canvas');
        canvas.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
            opacity: 0.6;
        `;
        
        heroSection.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        const particles = [];
        const particleCount = window.innerWidth < 768 ? 30 : 60;

        // Resize canvas
        const resizeCanvas = () => {
            canvas.width = heroSection.offsetWidth;
            canvas.height = heroSection.offsetHeight;
        };
        
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Create particles
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * 3 + 1,
                speedX: (Math.random() - 0.5) * 0.5,
                speedY: (Math.random() - 0.5) * 0.5,
                opacity: Math.random() * 0.5 + 0.2,
                hue: Math.random() * 60 + 180 // Blue to cyan range
            });
        }

        // Animate particles
        const animateParticles = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(particle => {
                // Update position
                particle.x += particle.speedX;
                particle.y += particle.speedY;
                
                // Wrap around edges
                if (particle.x < 0) particle.x = canvas.width;
                if (particle.x > canvas.width) particle.x = 0;
                if (particle.y < 0) particle.y = canvas.height;
                if (particle.y > canvas.height) particle.y = 0;
                
                // Draw particle
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fillStyle = `hsla(${particle.hue}, 70%, 60%, ${particle.opacity})`;
                ctx.fill();
                
                // Draw connections
                particles.forEach(otherParticle => {
                    const dx = particle.x - otherParticle.x;
                    const dy = particle.y - otherParticle.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    if (distance < 100) {
                        ctx.beginPath();
                        ctx.moveTo(particle.x, particle.y);
                        ctx.lineTo(otherParticle.x, otherParticle.y);
                        ctx.strokeStyle = `hsla(${particle.hue}, 70%, 60%, ${0.1 * (1 - distance / 100)})`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                });
            });
            
            requestAnimationFrame(animateParticles);
        };
        
        animateParticles();
    }

    // 🎨 CUSTOM CURSOR EFFECTS
    initializeCustomCursor() {
        if (window.innerWidth < 768) return; // Skip on mobile
        
        const cursor = document.createElement('div');
        cursor.className = 'custom-cursor';
        cursor.style.cssText = `
            position: fixed;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, #4ecdc4, #ff6b6b);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            mix-blend-mode: difference;
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease;
        `;
        
        document.body.appendChild(cursor);
        
        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;
        
        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });
        
        const updateCursor = () => {
            cursorX += (mouseX - cursorX) * 0.1;
            cursorY += (mouseY - cursorY) * 0.1;
            
            cursor.style.left = cursorX + 'px';
            cursor.style.top = cursorY + 'px';
            
            requestAnimationFrame(updateCursor);
        };
        
        updateCursor();
        
        // Cursor hover effects
        document.querySelectorAll('a, button, .swiper-button-next, .swiper-button-prev').forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.style.transform = 'translate(-50%, -50%) scale(2)';
                cursor.style.background = 'radial-gradient(circle, #ff6b6b, #4ecdc4)';
            });
            
            el.addEventListener('mouseleave', () => {
                cursor.style.transform = 'translate(-50%, -50%) scale(1)';
                cursor.style.background = 'radial-gradient(circle, #4ecdc4, #ff6b6b)';
            });
        });
    }

    // 🔧 PERFORMANCE OPTIMIZATIONS
    setupPerformanceOptimizations() {
        // Intersection Observer for animations
        const observerOptions = {
            threshold: [0, 0.25, 0.5, 0.75, 1],
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const element = entry.target;
                const ratio = entry.intersectionRatio;
                
                if (ratio > 0) {
                    element.classList.add('in-view');
                    this.triggerElementAnimation(element, ratio);
                } else {
                    element.classList.remove('in-view');
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.hero-section h1, .hero-section p, .hero-swiper, .hero-detail-item').forEach(el => {
            observer.observe(el);
        });

        this.observers.set('main', observer);
    }

    triggerElementAnimation(element, ratio) {
        const opacity = Math.min(1, ratio * 2);
        const transform = `translateY(${(1 - ratio) * 50}px)`;
        
        element.style.opacity = opacity;
        element.style.transform = transform;
    }

    // 📱 RESPONSIVE HANDLERS
    setupResponsiveHandlers() {
        let resizeTimeout;
        
        const handleResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResponsiveChanges();
            }, 250);
        };
        
        window.addEventListener('resize', handleResize);
        this.handleResponsiveChanges(); // Initial call
    }

    handleResponsiveChanges() {
        const isMobile = window.innerWidth < 768;
        const isTablet = window.innerWidth < 1024 && window.innerWidth >= 768;
        
        // Update Swiper settings
        if (this.swiper) {
            this.swiper.update();
            
            if (isMobile) {
                this.swiper.autoplay.stop();
            } else {
                this.swiper.autoplay.start();
            }
        }
        
        // Adjust animations for performance
        if (isMobile) {
            document.documentElement.classList.add('mobile-optimized');
        } else {
            document.documentElement.classList.remove('mobile-optimized');
        }
    }

    // 🎬 ADVANCED SCROLL EFFECTS
    setupAdvancedScrollEffects() {
        let lastScrollTime = 0;
        const scrollThreshold = 16; // ~60fps
        
        const scrollHandler = (timestamp) => {
            if (timestamp - lastScrollTime >= scrollThreshold) {
                this.updateScrollEffects();
                lastScrollTime = timestamp;
            }
            requestAnimationFrame(scrollHandler);
        };
        
        requestAnimationFrame(scrollHandler);
    }

    updateScrollEffects() {
        const scrollY = window.pageYOffset;
        const heroSection = document.querySelector('.hero-section');
        
        if (!heroSection) return;
        
        const rect = heroSection.getBoundingClientRect();
        const isInView = rect.bottom > 0 && rect.top < window.innerHeight;
        
        if (isInView) {
            const progress = Math.max(0, -rect.top / rect.height);
            this.applyScrollBasedTransforms(progress);
        }
    }

    applyScrollBasedTransforms(progress) {
        const elements = {
            '.hero-section main > div:first-child': {
                transform: `translateY(${progress * 50}px)`,
                opacity: 1 - (progress * 0.5)
            },
            '.hero-swiper': {
                transform: `translateY(${progress * -30}px) scale(${1 - progress * 0.1})`,
                opacity: 1 - (progress * 0.3)
            }
        };
        
        Object.entries(elements).forEach(([selector, styles]) => {
            const element = document.querySelector(selector);
            if (element) {
                Object.assign(element.style, styles);
            }
        });
    }

    // 🎪 UTILITY METHODS
    createRippleEffect(event) {
        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();
        const ripple = document.createElement('span');
        
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            pointer-events: none;
            transform: scale(0);
            animation: ripple 0.6s linear;
            left: ${event.clientX - rect.left}px;
            top: ${event.clientY - rect.top}px;
            width: 20px;
            height: 20px;
            margin-left: -10px;
            margin-top: -10px;
        `;
        
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    pauseBackgroundAnimations() {
        document.querySelectorAll('.bg, .bg2').forEach(el => {
            el.style.animationPlayState = 'paused';
        });
    }

    resumeBackgroundAnimations() {
        document.querySelectorAll('.bg, .bg2').forEach(el => {
            el.style.animationPlayState = 'running';
        });
    }

    addSlideMouseEffects() {
        document.querySelectorAll('.hero-slide-link').forEach(link => {
            link.addEventListener('mouseenter', (e) => {
                this.createSlideHoverEffect(e.currentTarget, true);
            });
            
            link.addEventListener('mouseleave', (e) => {
                this.createSlideHoverEffect(e.currentTarget, false);
            });
        });
    }

    createSlideHoverEffect(slide, isEntering) {
        const content = slide.querySelector('.hero-slide-content');
        const overlay = slide.querySelector('.hero-slide-overlay');
        
        if (isEntering) {
            content.style.transform = 'translateY(-10px) scale(1.02)';
            overlay.style.background = `linear-gradient(
                135deg, 
                rgba(0, 0, 0, 0.2) 0%, 
                rgba(0, 0, 0, 0.05) 50%, 
                rgba(0, 0, 0, 0.3) 100%
            )`;
        } else {
            content.style.transform = 'translateY(0) scale(1)';
            overlay.style.background = '';
        }
    }

    // 🎉 EVENT DISPATCHING
    dispatchReadyEvent() {
        const event = new CustomEvent('heroSectionReady', {
            detail: {
                controller: this,
                swiper: this.swiper,
                version: '2.0.0'
            }
        });
        
        document.dispatchEvent(event);
    }

    // 🧹 CLEANUP
    destroy() {
        // Stop all animations
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        // Destroy Swiper
        if (this.swiper) {
            this.swiper.destroy(true, true);
        }
        
        // Clear observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        
        // Remove event listeners
        window.removeEventListener('scroll', this.scrollHandler);
        window.removeEventListener('resize', this.resizeHandler);
        
        // Clear animations map
        this.animations.clear();
    }
}

// 🚀 AUTO-INITIALIZATION
let heroController = null;

document.addEventListener('DOMContentLoaded', () => {
    heroController = new HeroSectionController();
});

// 🌐 GLOBAL API
window.HeroSection = {
    get controller() { return heroController; },
    get swiper() { return heroController?.swiper; },
    
    updateSwiper() {
        heroController?.swiper?.update();
    },
    
    destroy() {
        heroController?.destroy();
        heroController = null;
    },
    
    reinitialize() {
        if (heroController) {
            heroController.destroy();
        }
        heroController = new HeroSectionController();
    }
};

// 💫 CSS ANIMATIONS (Injected dynamically)
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .mobile-optimized * {
        animation-duration: 0.3s !important;
    }
    
    .custom-cursor {
        will-change: transform;
    }
`;
document.head.appendChild(styleSheet);
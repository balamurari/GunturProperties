<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story - Guntur Properties</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3f51b5;
            --secondary: #4caf50;
            --accent: #ff9800;
            --dark: #1a1a1a;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #eeeeee;
            --gray-300: #e0e0e0;
            --gray-400: #bdbdbd;
            --gray-500: #9e9e9e;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --gray-900: #212121;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--gray-900);
            background: var(--white);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, #5c6bc0 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 32px;
            max-width: 500px;
            margin: 0 auto;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 8px;
        }

        /* Main Content */
        .main {
            padding: 0px 0;
        }

        .section {
            margin-bottom: 120px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Story Grid */
        .story-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-bottom: 80px;
        }

        .story-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 48px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .story-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transition: var(--transition);
            transform-origin: left;
        }

        .story-card:hover {
            border-color: var(--gray-300);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .story-card:hover::before {
            transform: scaleX(1);
        }

        .story-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 1.5rem;
            color: var(--white);
        }

        .story-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.01em;
        }

        .story-text {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
        }

        /* Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .value-card {
            text-align: center;
            padding: 40px 24px;
            background: var(--gray-50);
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-100);
            transition: var(--transition);
            position: relative;
        }

        .value-card:hover {
            background: var(--white);
            border-color: var(--gray-200);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 1.75rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .value-card:hover .value-icon {
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .value-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .value-text {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Team Grid */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .team-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 32px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .team-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, transparent 100%);
            opacity: 0;
            transition: var(--transition);
        }

        .team-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .team-card:hover::after {
            opacity: 0.1;
        }

        .team-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .team-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 1.25rem;
            color: var(--white);
            flex-shrink: 0;
        }

        .team-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.3;
        }

        .team-text {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta-section {
            background: var(--gray-900);
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .cta-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            color: var(--gray-900);
            padding: 16px 32px;
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .cta-button i {
            font-size: 1.125rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 100px 0 60px;
            }

            .main {
                padding: 0px 0;
            }

            .section {
                margin-bottom: 80px;
            }

            .section-header {
                margin-bottom: 60px;
            }

            .story-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .story-card,
            .value-card,
            .team-card {
                padding: 32px 24px;
            }

            .values-grid,
            .team-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .container {
                padding: 0 20px;
            }

            .hero-stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }

            .cta-section {
                padding: 60px 0;
            }
        }

        @media (max-width: 480px) {
            .hero-stats {
                grid-template-columns: 1fr;
                max-width: 200px;
            }

            .story-card,
            .value-card,
            .team-card {
                padding: 24px 20px;
            }

            .container {
                padding: 0 16px;
            }
        }

        /* Smooth Scroll Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Focus States */
        .cta-button:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* Selection */
        ::selection {
            background: var(--primary);
            color: var(--white);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }
    </style>
</head>
<body>
    <?php include 'header.php';?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Our Story</h1>
                <p class="hero-subtitle">Building bridges between dreams and reality with complete transparency and professional excellence.</p>
                
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">3+</span>
                        <span class="stat-label">Years</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Clients</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Transparent</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Story Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Who We Are</h2>
                    <p class="section-subtitle">Your trusted partners in real estate, connecting buyers and sellers with transparency and excellence.</p>
                </div>

                <div class="story-grid">
                    <div class="story-card fade-in">
                        <div class="story-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="story-title">Our Journey</h3>
                        <p class="story-text">For the past <strong>3 years</strong>, Guntur Properties has been serving as a vital bridge between property sellers and buyers. What started as a vision to bring transparency to real estate has grown into a comprehensive service platform.</p>
                    </div>

                    <div class="story-card fade-in">
                        <div class="story-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="story-title">Our Mission</h3>
                        <p class="story-text">We believe every property transaction should be built on trust and transparency. Our commitment has helped hundreds of families find their dream homes while enabling property owners to achieve their goals.</p>
                    </div>
                </div>
            </section>

            <!-- Values Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Our Core Values</h2>
                    <p class="section-subtitle">The principles that guide every interaction and transaction we make.</p>
                </div>

                <div class="values-grid">
                    <div class="value-card fade-in">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="value-title">Complete Transparency</h3>
                        <p class="value-text">All our activities are completely transparent. From listings to pricing, documentation to transactions - everything is clear and open.</p>
                    </div>

                    <div class="value-card fade-in">
                        <div class="value-icon">
                            <i class="fas fa-bridge"></i>
                        </div>
                        <h3 class="value-title">Perfect Bridge</h3>
                        <p class="value-text">We work as the perfect bridge between sellers and buyers, understanding both parties' needs for smooth transactions.</p>
                    </div>

                    <div class="value-card fade-in">
                        <div class="value-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3 class="value-title">Professional Excellence</h3>
                        <p class="value-text">We strive for excellence in every aspect - from consultation to final handover. Quality is our standard.</p>
                    </div>
                </div>
            </section>

            <!-- Team Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Our Expert Teams</h2>
                    <p class="section-subtitle">Dedicated professionals working together to deliver exceptional service.</p>
                </div>

                <div class="team-grid">
                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h3 class="team-title">Tele Calling Team</h3>
                        </div>
                        <p class="team-text">Professional communication team ensuring prompt updates, scheduling visits, and maintaining consistent contact throughout the process.</p>
                    </div>

                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h3 class="team-title">Marketing Support</h3>
                        </div>
                        <p class="team-text">Creative marketing team managing campaigns, digital presence, and ensuring maximum property visibility across platforms.</p>
                    </div>

                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="team-title">Client Visit Team</h3>
                        </div>
                        <p class="team-text">Experienced professionals accompanying clients during visits, providing insights and ensuring comfortable viewing experiences.</p>
                    </div>

                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h3 class="team-title">Property Shooting</h3>
                        </div>
                        <p class="team-text">Skilled photography team capturing properties in their best light, creating stunning visuals that showcase unique features.</p>
                    </div>

                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3 class="team-title">Content Creation</h3>
                        </div>
                        <p class="team-text">Creative team producing video editing, voice-over work, and compelling scripts that tell each property's story.</p>
                    </div>

                    <div class="team-card fade-in">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h3 class="team-title">Promotion Team</h3>
                        </div>
                        <p class="team-text">Specialists ensuring properties reach the right audience through targeted campaigns and strategic advertising.</p>
                    </div>
                </div>
            </section>
        </div>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content fade-in">
                    <h2 class="cta-title">Ready to Work With Us?</h2>
                    <p class="cta-subtitle">Let our experienced team guide you through every step with complete transparency and professionalism.</p>
                    <a href="contact.php" class="cta-button">
                        <i class="fas fa-phone"></i>
                        Contact Us Today
                    </a>
                </div>
            </div>
        </section>
    </main>
        <?php include 'footer.php'; ?>


    <script>
        // Smooth scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Continuous counter animation
        const counters = document.querySelectorAll('.stat-number');
        
        counters.forEach(counter => {
            const originalText = counter.textContent;
            const baseNumber = parseInt(originalText);
            const suffix = originalText.replace(/[0-9]/g, '');
            
            // Create fluctuating animation
            const animateCounter = () => {
                let startTime = Date.now();
                const duration = 3000; // 3 seconds per cycle
                
                const updateNumber = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = (elapsed % duration) / duration;
                    
                    // Create a smooth wave-like fluctuation
                    const wave = Math.sin(progress * Math.PI * 2);
                    const fluctuation = Math.floor(wave * (baseNumber * 0.05)); // 5% fluctuation
                    const currentNumber = baseNumber + fluctuation;
                    
                    // Ensure number doesn't go below reasonable minimum
                    const finalNumber = Math.max(currentNumber, Math.floor(baseNumber * 0.95));
                    
                    counter.textContent = finalNumber + suffix;
                    
                    requestAnimationFrame(updateNumber);
                };
                
                updateNumber();
            };
            
            // Start animation when hero section is visible
            const heroObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(animateCounter, 500);
                        heroObserver.unobserve(entry.target);
                    }
                });
            });
            
            heroObserver.observe(document.querySelector('.hero'));
        });

        // Alternative: Continuous counting up animation
        const createContinuousCounter = (counter) => {
            const originalText = counter.textContent;
            const baseNumber = parseInt(originalText);
            const suffix = originalText.replace(/[0-9]/g, '');
            
            let currentValue = baseNumber;
            const increment = baseNumber >= 100 ? 1 : 0.1;
            const maxValue = baseNumber * 1.2; // 20% above base
            const minValue = baseNumber * 0.8; // 20% below base
            let direction = 1;
            
            const updateCounter = () => {
                currentValue += (increment * direction);
                
                // Reverse direction at boundaries
                if (currentValue >= maxValue) {
                    direction = -1;
                } else if (currentValue <= minValue) {
                    direction = 1;
                }
                
                // Update display
                const displayValue = Math.floor(currentValue);
                counter.textContent = displayValue + suffix;
                
                // Continue animation
                setTimeout(updateCounter, 100); // Update every 100ms
            };
            
            return updateCounter;
        };

       
        counters.forEach(counter => {
            const heroObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(createContinuousCounter(counter), 500);
                        heroObserver.unobserve(entry.target);
                    }
                });
            });
            
            heroObserver.observe(document.querySelector('.hero'));
        });
     
    </script>

</body>
</html>
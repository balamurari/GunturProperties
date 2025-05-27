<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Work - Guntur Properties</title>
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
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Main Content */
        .main {
            padding: 0px 0;
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

        /* Process Timeline */
        .process-timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }

        .process-timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            transform: translateX(-50%);
            z-index: 1;
        }

        .process-step {
            position: relative;
            margin-bottom: 80px;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .process-step.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .process-step:nth-child(even) {
            flex-direction: row-reverse;
        }

        .process-step:nth-child(even) .process-content {
            text-align: right;
        }

        .process-step:nth-child(even) .process-number {
            margin-left: 40px;
            margin-right: 0;
        }

        .process-step:nth-child(odd) .process-number {
            margin-right: 40px;
        }

        .process-content {
            flex: 1;
            max-width: 400px;
        }

        .process-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .process-card::before {
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

        .process-step:hover .process-card {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .process-step:hover .process-card::before {
            transform: scaleX(1);
        }

        .process-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--white);
            transition: var(--transition);
        }

        .process-step:hover .process-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .process-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }

        .process-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 16px;
        }

        .process-features {
            list-style: none;
        }

        .process-features li {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
        }

        .process-features li::before {
            content: '→';
            color: var(--primary);
            font-weight: bold;
            margin-right: 8px;
        }

        /* Process Number */
        .process-number {
            width: 80px;
            height: 80px;
            background: var(--white);
            border: 3px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            position: relative;
            z-index: 2;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .process-step:hover .process-number {
            background: var(--primary);
            color: var(--white);
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        /* Results Section */
        .results-section {
            background: var(--gray-50);
            padding: 80px 0;
            margin-top: 80px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .result-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 32px 24px;
            text-align: center;
            transition: var(--transition);
        }

        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .result-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            margin-bottom: 8px;
        }

        .result-label {
            color: var(--gray-600);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
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

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .cta-button.primary {
            background: var(--white);
            color: var(--gray-900);
        }

        .cta-button.secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .cta-button.primary:hover {
            background: var(--gray-100);
        }

        .cta-button.secondary:hover {
            background: var(--white);
            color: var(--gray-900);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 100px 0 60px;
            }

            .main {
                padding: 0px 0;
            }

            .section-header {
                margin-bottom: 60px;
            }

            .process-timeline::before {
                left: 20px;
            }

            .process-step {
                flex-direction: column !important;
                align-items: flex-start;
                margin-bottom: 60px;
                padding-left: 60px;
            }

            .process-step:nth-child(even) .process-content {
                text-align: left;
            }

            .process-number {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0 !important;
                width: 60px;
                height: 60px;
                font-size: 1.25rem;
            }

            .process-card {
                padding: 24px;
                width: 100%;
            }

            .results-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-button {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .container {
                padding: 0 20px;
            }
        }

        @media (max-width: 480px) {
            .results-grid {
                grid-template-columns: 1fr;
            }

            .process-card {
                padding: 20px;
            }

            .container {
                padding: 0 16px;
            }
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
                <h1 class="hero-title">Our Work Process</h1>
                <p class="hero-subtitle">From initial consultation to successful deal closure - discover how we transform property dreams into reality with our proven 8-step process.</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">How We Work</h2>
                <p class="section-subtitle">Our systematic approach ensures every client receives personalized attention and professional service throughout their real estate journey.</p>
            </div>

            <!-- Process Timeline -->
            <div class="process-timeline">
                <!-- Step 1: Client Consultation -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3 class="process-title">Client Consultation</h3>
                            <p class="process-description">We begin with detailed discussions to understand your specific requirements, budget, and preferences.</p>
                            <ul class="process-features">
                                <li>Understand client needs and budget</li>
                                <li>Explain our transparent process</li>
                                <li>Set clear expectations and timeline</li>
                                <li>Provide market insights and advice</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">1</div>
                </div>

                <!-- Step 2: Property Visit -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="process-title">Site Visit & Assessment</h3>
                            <p class="process-description">Our team visits the property location to conduct thorough assessment and documentation.</p>
                            <ul class="process-features">
                                <li>Professional site inspection</li>
                                <li>Location advantage analysis</li>
                                <li>Property condition evaluation</li>
                                <li>Legal documentation review</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">2</div>
                </div>

                <!-- Step 3: Professional Shooting -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h3 class="process-title">Professional Photography</h3>
                            <p class="process-description">High-quality photography and videography to showcase the property's best features.</p>
                            <ul class="process-features">
                                <li>Professional photography equipment</li>
                                <li>Multiple angles and lighting</li>
                                <li>Drone shots for large properties</li>
                                <li>Virtual tour creation</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">3</div>
                </div>

                <!-- Step 4: Content Creation -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3 class="process-title">Content Creation & Editing</h3>
                            <p class="process-description">Expert editing and content creation to produce compelling property presentations.</p>
                            <ul class="process-features">
                                <li>Professional video editing</li>
                                <li>Voice-over recordings</li>
                                <li>Property description writing</li>
                                <li>Marketing material design</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">4</div>
                </div>

                <!-- Step 5: Marketing & Promotion -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h3 class="process-title">Strategic Promotion</h3>
                            <p class="process-description">Multi-channel marketing campaign to reach the right audience effectively.</p>
                            <ul class="process-features">
                                <li>Social media advertising</li>
                                <li>Online property portals</li>
                                <li>Print and digital marketing</li>
                                <li>Network referrals</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">5</div>
                </div>

                <!-- Step 6: Telecalling Support -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h3 class="process-title">Telecalling & Follow-up</h3>
                            <p class="process-description">Dedicated team provides consistent communication and follow-up with interested buyers.</p>
                            <ul class="process-features">
                                <li>Professional telecalling team</li>
                                <li>Regular client updates</li>
                                <li>Inquiry management system</li>
                                <li>Appointment scheduling</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">6</div>
                </div>

                <!-- Step 7: Customer Engagement -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="process-title">Customer Engagement</h3>
                            <p class="process-description">We connect serious buyers with sellers, facilitating meetings and property viewings.</p>
                            <ul class="process-features">
                                <li>Qualified buyer screening</li>
                                <li>Coordinated property visits</li>
                                <li>Expert guidance during viewings</li>
                                <li>Negotiation facilitation</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">7</div>
                </div>

                <!-- Step 8: Deal Closure -->
                <div class="process-step">
                    <div class="process-content">
                        <div class="process-card">
                            <div class="process-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="process-title">Successful Deal Closure</h3>
                            <p class="process-description">Complete assistance through documentation and legal formalities for smooth transaction closure.</p>
                            <ul class="process-features">
                                <li>Documentation assistance</li>
                                <li>Legal compliance support</li>
                                <li>Transaction coordination</li>
                                <li>Post-closure follow-up</li>
                            </ul>
                        </div>
                    </div>
                    <div class="process-number">8</div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <section class="results-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Track Record</h2>
                    <p class="section-subtitle">Numbers that speak for our commitment and success in the real estate market.</p>
                </div>

                <div class="results-grid">
                    <div class="result-card">
                        <span class="result-number" id="counter1">500</span>
                        <span class="result-label">Happy Clients</span>
                    </div>
                    <div class="result-card">
                        <span class="result-number" id="counter2">300</span>
                        <span class="result-label">Properties Sold</span>
                    </div>
                    <div class="result-card">
                        <span class="result-number" id="counter3">95</span>
                        <span class="result-label">Success Rate %</span>
                    </div>
                    <div class="result-card">
                        <span class="result-number" id="counter4">30</span>
                        <span class="result-label">Days Avg Sale</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2 class="cta-title">Ready to Experience Our Process?</h2>
                    <p class="cta-subtitle">Let us help you navigate your real estate journey with complete transparency and professional excellence.</p>
                    <div class="cta-buttons">
                        <a href="contact.php" class="cta-button primary">
                            <i class="fas fa-phone"></i>
                            Start Your Journey
                        </a>
                        <a href="properties.php" class="cta-button secondary">
                            <i class="fas fa-eye"></i>
                            View Properties
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'footer.php';?>

    <script>
        // Scroll animations for process steps
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 200); // Stagger animation
                }
            });
        }, observerOptions);

        document.querySelectorAll('.process-step').forEach(step => {
            observer.observe(step);
        });

        // Continuous counter animation for results
        const counters = [
            { element: document.getElementById('counter1'), base: 500, suffix: '+' },
            { element: document.getElementById('counter2'), base: 300, suffix: '+' },
            { element: document.getElementById('counter3'), base: 95, suffix: '%' },
            { element: document.getElementById('counter4'), base: 30, suffix: '' }
        ];

        counters.forEach(counter => {
            if (counter.element) {
                const animateCounter = () => {
                    let startTime = Date.now();
                    const duration = 4000; // 4 seconds per cycle
                    
                    const updateNumber = () => {
                        const elapsed = Date.now() - startTime;
                        const progress = (elapsed % duration) / duration;
                        
                        // Create a smooth wave-like fluctuation
                        const wave = Math.sin(progress * Math.PI * 2);
                        const fluctuation = Math.floor(wave * (counter.base * 0.08)); // 8% fluctuation
                        const currentNumber = counter.base + fluctuation;
                        
                        // Ensure number doesn't go below reasonable minimum
                        const finalNumber = Math.max(currentNumber, Math.floor(counter.base * 0.92));
                        
                        counter.element.textContent = finalNumber + counter.suffix;
                        
                        requestAnimationFrame(updateNumber);
                    };
                    
                    updateNumber();
                };

                // Start animation when results section is visible
                const resultsObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(animateCounter, 500);
                            resultsObserver.unobserve(entry.target);
                        }
                    });
                });
                
                resultsObserver.observe(document.querySelector('.results-section'));
            }
        });

        // Smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>
</html>
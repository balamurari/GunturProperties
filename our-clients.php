<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Clients - Guntur Properties</title>
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
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 32px;
            max-width: 600px;
            margin: 0 auto;
        }

        .stat {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .stat:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        .stat-number {
            display: block;
            font-size: 2rem;
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

        /* Client Categories Grid */
        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-bottom: 80px;
        }

        .client-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 40px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            group: hover;
        }

        .client-card::before {
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

        .client-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(63, 81, 181, 0.02) 0%, transparent 70%);
            transform: scale(0);
            transition: var(--transition);
        }

        .client-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .client-card:hover::before {
            transform: scaleX(1);
        }

        .client-card:hover::after {
            transform: scale(1);
        }

        .client-header {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .client-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.75rem;
            color: var(--white);
            transition: var(--transition);
            flex-shrink: 0;
        }

        .client-card:hover .client-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .client-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .client-info .client-type {
            color: var(--gray-600);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }

        .client-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 24px;
        }

        .client-services {
            list-style: none;
        }

        .client-services li {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .client-services li::before {
            content: '✓';
            color: var(--secondary);
            font-weight: bold;
            margin-right: 12px;
            font-size: 1rem;
        }

        .client-services li:last-child {
            margin-bottom: 0;
        }

        /* Benefits Section */
        .benefits-section {
            background: var(--gray-50);
            padding: 80px 0;
            border-radius: 24px;
            margin: 80px 0;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .benefit-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            transition: var(--transition);
        }

        .benefit-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .benefit-icon {
            width: 64px;
            height: 64px;
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .benefit-card:hover .benefit-icon {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
            transform: scale(1.1);
        }

        .benefit-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .benefit-text {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Testimonials Section */
        .testimonials-section {
            background: var(--gray-900);
            padding: 80px 0;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .testimonials-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .testimonials-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .testimonials-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 32px;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .testimonial-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .testimonial-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 24px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 1.25rem;
            color: var(--white);
            font-weight: 600;
        }

        .author-info h4 {
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .author-info p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 80px 0;
        }

        .cta-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .cta-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
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
            background: var(--primary);
            color: var(--white);
        }

        .cta-button.secondary {
            background: var(--white);
            color: var(--gray-900);
            border: 2px solid var(--gray-200);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .cta-button.primary:hover {
            background: var(--primary);
            filter: brightness(1.1);
        }

        .cta-button.secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
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

            .clients-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .client-card {
                padding: 32px 24px;
            }

            .client-header {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .client-icon {
                margin-right: 0;
                margin-bottom: 16px;
            }

            .benefits-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .hero-stats {
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
            .hero-stats {
                grid-template-columns: 1fr;
            }

            .client-card,
            .benefit-card,
            .testimonial-card {
                padding: 24px 20px;
            }

            .container {
                padding: 0 16px;
            }
        }

        /* Scroll Animation */
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
                <h1 class="hero-title">Our Valued Clients</h1>
                <p class="hero-subtitle">We proudly serve diverse property owners across Guntur, from individual land owners to large commercial enterprises, providing tailored real estate solutions for every need.</p>
                
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number" id="counter1">500</span>
                        <span class="stat-label">Property Owners</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="counter2">6</span>
                        <span class="stat-label">Client Types</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="counter3">95</span>
                        <span class="stat-label">Satisfaction %</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include 'header.php';?>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Client Categories Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Who We Serve</h2>
                    <p class="section-subtitle">Our diverse clientele represents every segment of the real estate market, each receiving personalized service tailored to their unique requirements.</p>
                </div>

                <div class="clients-grid">
                    <!-- Venture Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="client-info">
                                <h3>Venture Owners</h3>
                                <span class="client-type">Business Developers</span>
                            </div>
                        </div>
                        <p class="client-description">Entrepreneurs and business ventures looking to expand their real estate portfolio or establish new commercial locations. We help them find strategic properties that align with their business objectives.</p>
                        <ul class="client-services">
                            <li>Strategic location analysis</li>
                            <li>Investment opportunity assessment</li>
                            <li>Portfolio expansion planning</li>
                            <li>Market trend insights</li>
                        </ul>
                    </div>

                    <!-- Real Estate Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="client-info">
                                <h3>Real Estate Owners</h3>
                                <span class="client-type">Property Investors</span>
                            </div>
                        </div>
                        <p class="client-description">Individual and institutional property owners seeking to maximize their real estate investments through strategic buying, selling, and rental opportunities.</p>
                        <ul class="client-services">
                            <li>Property valuation services</li>
                            <li>Investment optimization</li>
                            <li>Market positioning strategy</li>
                            <li>Rental yield analysis</li>
                        </ul>
                    </div>

                    <!-- Land Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-map"></i>
                            </div>
                            <div class="client-info">
                                <h3>Land Owners</h3>
                                <span class="client-type">Agricultural & Development</span>
                            </div>
                        </div>
                        <p class="client-description">Owners of agricultural land, vacant plots, and development sites looking to monetize their land assets through sale or development partnerships.</p>
                        <ul class="client-services">
                            <li>Land survey and documentation</li>
                            <li>Development potential assessment</li>
                            <li>Zoning and approval guidance</li>
                            <li>Joint venture facilitation</li>
                        </ul>
                    </div>

                    <!-- Apartment Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="client-info">
                                <h3>Apartment Owners</h3>
                                <span class="client-type">Residential Properties</span>
                            </div>
                        </div>
                        <p class="client-description">Individual apartment owners and residential complex developers seeking to sell or rent their residential properties in prime locations across Guntur.</p>
                        <ul class="client-services">
                            <li>Residential market analysis</li>
                            <li>Tenant screening services</li>
                            <li>Property maintenance coordination</li>
                            <li>Rental management support</li>
                        </ul>
                    </div>

                    <!-- Office Space Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="client-info">
                                <h3>Office Space Owners</h3>
                                <span class="client-type">Commercial Properties</span>
                            </div>
                        </div>
                        <p class="client-description">Owners of office buildings, co-working spaces, and business centers looking to lease or sell their commercial properties to suitable tenants and buyers.</p>
                        <ul class="client-services">
                            <li>Commercial space evaluation</li>
                            <li>Corporate client matching</li>
                            <li>Lease negotiation support</li>
                            <li>Space optimization consulting</li>
                        </ul>
                    </div>

                    <!-- Commercial Property Owners -->
                    <div class="client-card fade-in">
                        <div class="client-header">
                            <div class="client-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="client-info">
                                <h3>Commercial Owners</h3>
                                <span class="client-type">Retail & Industrial</span>
                            </div>
                        </div>
                        <p class="client-description">Owners of retail spaces, warehouses, industrial properties, shopping complexes, and other commercial real estate seeking strategic transactions.</p>
                        <ul class="client-services">
                            <li>Commercial property marketing</li>
                            <li>Industrial site analysis</li>
                            <li>Retail location optimization</li>
                            <li>Investment return calculation</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Benefits Section -->
            <section class="benefits-section">
                <div class="container">
                    <div class="section-header fade-in">
                        <h2 class="section-title">Why Clients Choose Us</h2>
                        <p class="section-subtitle">We provide comprehensive real estate solutions that deliver measurable results for all our client categories.</p>
                    </div>

                    <div class="benefits-grid">
                        <div class="benefit-card fade-in">
                            <div class="benefit-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3 class="benefit-title">Complete Transparency</h3>
                            <p class="benefit-text">All processes, pricing, and transactions are fully transparent with no hidden costs or surprise fees.</p>
                        </div>

                        <div class="benefit-card fade-in">
                            <div class="benefit-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3 class="benefit-title">Market Expertise</h3>
                            <p class="benefit-text">Deep understanding of Guntur's real estate market trends and property values across all segments.</p>
                        </div>

                        <div class="benefit-card fade-in">
                            <div class="benefit-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="benefit-title">Personalized Service</h3>
                            <p class="benefit-text">Tailored approach for each client type with dedicated account management and custom solutions.</p>
                        </div>

                        <div class="benefit-card fade-in">
                            <div class="benefit-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="benefit-title">Quick Turnaround</h3>
                            <p class="benefit-text">Efficient processes that minimize time to market and accelerate deal closure for all property types.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials Section -->
            <section class="testimonials-section">
                <div class="container">
                    <div class="testimonials-content">
                        <h2 class="testimonials-title">What Our Clients Say</h2>
                        <p class="testimonials-subtitle">Real feedback from property owners who have experienced our professional service.</p>

                        <div class="testimonials-grid">
                            <div class="testimonial-card fade-in">
                                <p class="testimonial-text">"Guntur Properties helped us sell our commercial complex within 3 weeks. Their professional approach and transparent process made everything smooth and hassle-free."</p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">R</div>
                                    <div class="author-info">
                                        <h4>Rajesh Kumar</h4>
                                        <p>Commercial Property Owner</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card fade-in">
                                <p class="testimonial-text">"As a land owner, I was impressed by their detailed market analysis and guidance. They secured the best possible price for my agricultural land."</p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">S</div>
                                    <div class="author-info">
                                        <h4>Srinivas Reddy</h4>
                                        <p>Land Owner</p>
                                    </div>
                                </div>
                            </div>

                            <div class="testimonial-card fade-in">
                                <p class="testimonial-text">"Their team found the perfect tenant for our office space quickly. The entire process was transparent and professionally managed from start to finish."</p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">P</div>
                                    <div class="author-info">
                                        <h4>Priya Sharma</h4>
                                        <p>Office Space Owner</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section">
                <h2 class="cta-title">Join Our Growing Client Family</h2>
                <p class="cta-subtitle">Whether you own land, apartments, commercial spaces, or any other real estate, we have the expertise to help you achieve your goals.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button primary">
                        <i class="fas fa-phone"></i>
                        Become Our Client
                    </a>
                    <a href="our-work.php" class="cta-button secondary">
                        <i class="fas fa-eye"></i>
                        See Our Process
                    </a>
                </div>
            </section>
        </div>
    </main>
    <?php include 'footer.php';?>

    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Continuous counter animation
        const counters = [
            { element: document.getElementById('counter1'), base: 500, suffix: '+' },
            { element: document.getElementById('counter2'), base: 6, suffix: '' },
            { element: document.getElementById('counter3'), base: 95, suffix: '%' }
        ];

        counters.forEach(counter => {
            if (counter.element) {
                const animateCounter = () => {
                    let startTime = Date.now();
                    const duration = 4000;
                    
                    const updateNumber = () => {
                        const elapsed = Date.now() - startTime;
                        const progress = (elapsed % duration) / duration;
                        
                        const wave = Math.sin(progress * Math.PI * 2);
                        const fluctuation = Math.floor(wave * (counter.base * 0.08));
                        const currentNumber = counter.base + fluctuation;
                        const finalNumber = Math.max(currentNumber, Math.floor(counter.base * 0.92));
                        
                        counter.element.textContent = finalNumber + counter.suffix;
                        requestAnimationFrame(updateNumber);
                    };
                    
                    updateNumber();
                };

                const heroObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(animateCounter, 500);
                            heroObserver.unobserve(entry.target);
                        }
                    });
                });
                
                heroObserver.observe(document.querySelector('.hero'));
            }
        });
    </script>

</body>
</html>
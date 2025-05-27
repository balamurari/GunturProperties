<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negotiation Services - Guntur Properties</title>
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
            gap: 24px;
            max-width: 700px;
            margin: 0 auto;
        }

        .hero-stat {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .hero-stat:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        .hero-stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1;
        }

        .hero-stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 8px;
        }

        /* Main Content */
        .main {
            padding: 120px 0;
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

        /* Expertise Cards */
        .expertise-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-bottom: 80px;
        }

        .expertise-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 40px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .expertise-card::before {
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

        .expertise-card::after {
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

        .expertise-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .expertise-card:hover::before {
            transform: scaleX(1);
        }

        .expertise-card:hover::after {
            transform: scale(1);
        }

        .expertise-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 1.75rem;
            color: var(--white);
            transition: var(--transition);
        }

        .expertise-card:hover .expertise-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .expertise-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.01em;
        }

        .expertise-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .expertise-features {
            list-style: none;
        }

        .expertise-features li {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .expertise-features li::before {
            content: '✓';
            color: var(--secondary);
            font-weight: bold;
            margin-right: 12px;
            font-size: 1rem;
        }

        /* Process Section */
        .process-section {
            background: var(--gray-50);
            padding: 80px 0;
            border-radius: 24px;
            margin: 80px 0;
        }

        .process-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .process-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            transition: var(--transition);
            position: relative;
        }

        .process-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .process-number {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--white);
        }

        .process-icon {
            width: 60px;
            height: 60px;
            background: var(--gray-100);
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto 20px;
            font-size: 1-5rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .process-card:hover .process-icon {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
            transform: scale(1.1);
        }

        .process-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .process-description {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Benefits Section */
        .benefits-section {
            background: var(--gray-900);
            padding: 80px 0;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            margin: 80px 0;
        }

        .benefits-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .benefits-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .benefits-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .benefits-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
        }

        .benefit-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .benefit-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .benefit-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            color: var(--white);
            transition: var(--transition);
        }

        .benefit-card:hover .benefit-icon {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--accent), #f57c00);
        }

        .benefit-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 12px;
        }

        .benefit-text {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Success Stories */
        .success-section {
            background: var(--white);
        }

        .success-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .success-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 32px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            transform: scaleY(0);
            transition: var(--transition);
        }

        .success-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            background: var(--white);
        }

        .success-card:hover::before {
            transform: scaleY(1);
        }

        .success-stats {
            display: flex;
            gap: 24px;
            margin-bottom: 20px;
        }

        .success-stat {
            text-align: center;
        }

        .success-stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        .success-stat-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 4px;
        }

        .success-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .success-description {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
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

            .expertise-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .expertise-card {
                padding: 32px 24px;
            }

            .process-grid,
            .benefits-grid,
            .success-grid {
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

            .success-stats {
                flex-direction: column;
                gap: 16px;
            }

            .expertise-card,
            .process-card,
            .benefit-card,
            .success-card {
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
                <h1 class="hero-title">Expert Negotiation</h1>
                <p class="hero-subtitle">Master negotiators who secure the best deals for both buyers and sellers through transparent, skillful, and strategic negotiation techniques that maximize value for all parties.</p>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number" id="counter1">95</span>
                        <span class="hero-stat-label">Success Rate %</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number" id="counter2">250</span>
                        <span class="hero-stat-label">Deals Closed</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number" id="counter3">15</span>
                        <span class="hero-stat-label">Avg Days</span>
                    </div>
                   
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Expertise Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Negotiation Expertise</h2>
                    <p class="section-subtitle">Our skilled negotiation team combines market knowledge, communication skills, and strategic thinking to achieve optimal outcomes for every deal.</p>
                </div>

                <div class="expertise-grid">
                    <!-- Buyer Negotiation -->
                    <div class="expertise-card fade-in">
                        <div class="expertise-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="expertise-title">Buyer Representation</h3>
                        <p class="expertise-description">We negotiate on behalf of buyers to secure properties at the best possible prices while ensuring all terms favor our clients' interests.</p>
                        <ul class="expertise-features">
                            <li>Price reduction strategies</li>
                            <li>Favorable payment terms</li>
                            <li>Inspection contingencies</li>
                            <li>Closing cost negotiations</li>
                            <li>Timeline flexibility</li>
                        </ul>
                    </div>

                    <!-- Seller Negotiation -->
                    <div class="expertise-card fade-in">
                        <div class="expertise-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="expertise-title">Seller Advocacy</h3>
                        <p class="expertise-description">For sellers, we maximize property value and negotiate terms that protect your interests while ensuring quick and profitable transactions.</p>
                        <ul class="expertise-features">
                            <li>Maximum price achievement</li>
                            <li>Quick sale strategies</li>
                            <li>Terms and conditions</li>
                            <li>Multiple offer handling</li>
                            <li>Risk mitigation</li>
                        </ul>
                    </div>

                    <!-- Win-Win Solutions -->
                    <div class="expertise-card fade-in">
                        <div class="expertise-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3 class="expertise-title">Win-Win Solutions</h3>
                        <p class="expertise-description">Our transparent approach ensures both parties feel satisfied with the deal, creating long-term relationships and referral opportunities.</p>
                        <ul class="expertise-features">
                            <li>Fair market valuations</li>
                            <li>Transparent communication</li>
                            <li>Creative deal structuring</li>
                            <li>Conflict resolution</li>
                            <li>Long-term relationship building</li>
                        </ul>
                    </div>

                    <!-- Market Intelligence -->
                    <div class="expertise-card fade-in">
                        <div class="expertise-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="expertise-title">Market Intelligence</h3>
                        <p class="expertise-description">Armed with comprehensive market data and trends, we negotiate from a position of knowledge and strength in every transaction.</p>
                        <ul class="expertise-features">
                            <li>Comparative market analysis</li>
                            <li>Price trend insights</li>
                            <li>Area development plans</li>
                            <li>Investment potential</li>
                            <li>Future value projections</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Negotiation Process -->
            <section class="process-section">
                <div class="container">
                    <div class="section-header fade-in">
                        <h2 class="section-title">Our Negotiation Process</h2>
                        <p class="section-subtitle">A systematic approach that maximizes value and minimizes risk for all parties involved.</p>
                    </div>

                    <div class="process-grid">
                        <div class="process-card fade-in">
                            <div class="process-number">1</div>
                            <div class="process-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3 class="process-title">Market Research</h3>
                            <p class="process-description">Comprehensive analysis of comparable properties, market trends, and pricing strategies.</p>
                        </div>

                        <div class="process-card fade-in">
                            <div class="process-number">2</div>
                            <div class="process-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3 class="process-title">Strategy Development</h3>
                            <p class="process-description">Create customized negotiation strategies based on client goals and market conditions.</p>
                        </div>

                        <div class="process-card fade-in">
                            <div class="process-number">3</div>
                            <div class="process-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3 class="process-title">Initial Discussions</h3>
                            <p class="process-description">Open communication with all parties to establish expectations and identify key concerns.</p>
                        </div>

                        <div class="process-card fade-in">
                            <div class="process-number">4</div>
                            <div class="process-icon">
                                <i class="fas fa-chess"></i>
                            </div>
                            <h3 class="process-title">Strategic Negotiation</h3>
                            <p class="process-description">Execute negotiation strategy with flexibility to adapt based on counteroffers and responses.</p>
                        </div>

                        <div class="process-card fade-in">
                            <div class="process-number">5</div>
                            <div class="process-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h3 class="process-title">Deal Finalization</h3>
                            <p class="process-description">Secure final agreements with all terms clearly documented and legally binding.</p>
                        </div>

                        <div class="process-card fade-in">
                            <div class="process-number">6</div>
                            <div class="process-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="process-title">Closure Support</h3>
                            <p class="process-description">Ensure smooth transaction closure with all negotiated terms fulfilled successfully.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Benefits Section -->
            <section class="benefits-section">
                <div class="container">
                    <div class="benefits-content">
                        <h2 class="benefits-title">Why Choose Our Negotiation Services</h2>
                        <p class="benefits-subtitle">Experience the difference that professional negotiation expertise makes in your real estate transactions.</p>

                        <div class="benefits-grid">
                            <div class="benefit-card fade-in">
                                <div class="benefit-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h3 class="benefit-title">Save Money</h3>
                                <p class="benefit-text">Our negotiation skills typically save clients 3-8% on purchase prices and increase sale prices by similar margins.</p>
                            </div>

                            <div class="benefit-card fade-in">
                                <div class="benefit-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3 class="benefit-title">Faster Deals</h3>
                                <p class="benefit-text">Professional negotiation reduces deal time by resolving issues quickly and efficiently.</p>
                            </div>

                            <div class="benefit-card fade-in">
                                <div class="benefit-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3 class="benefit-title">Risk Protection</h3>
                                <p class="benefit-text">Expert negotiation includes protective clauses and terms that safeguard your interests.</p>
                            </div>

                            <div class="benefit-card fade-in">
                                <div class="benefit-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h3 class="benefit-title">Stress Reduction</h3>
                                <p class="benefit-text">Let professionals handle the pressure while you focus on your next steps with confidence.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Success Stories -->
            <section class="success-section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Negotiation Success Stories</h2>
                    <p class="section-subtitle">Real results from our expert negotiation services that delivered exceptional value for our clients.</p>
                </div>

                <div class="success-grid">
                    <div class="success-card fade-in">
                        <div class="success-stats">
                            <div class="success-stat">
                                <span class="success-stat-number">₹25L</span>
                                <span class="success-stat-label">Saved</span>
                            </div>
                            <div class="success-stat">
                                <span class="success-stat-number">7</span>
                                <span class="success-stat-label">Days</span>
                            </div>
                        </div>
                        <h3 class="success-title">Villa Purchase Negotiation</h3>
                        <p class="success-description">Successfully negotiated a ₹25 lakh reduction on a luxury villa purchase, saving the buyer significant money while securing favorable payment terms and inspection rights.</p>
                    </div>

                    <div class="success-card fade-in">
                        <div class="success-stats">
                            <div class="success-stat">
                                <span class="success-stat-number">₹40L</span>
                                <span class="success-stat-label">Extra</span>
                            </div>
                            <div class="success-stat">
                                <span class="success-stat-number">12</span>
                                <span class="success-stat-label">Days</span>
                            </div>
                        </div>
                        <h3 class="success-title">Commercial Property Sale</h3>
                        <p class="success-description">Negotiated ₹40 lakh above asking price for a commercial property by leveraging multiple offers and highlighting unique location advantages.</p>
                    </div>

                    <div class="success-card fade-in">
                        <div class="success-stats">
                            <div class="success-stat">
                                <span class="success-stat-number">5</span>
                                <span class="success-stat-label">Offers</span>
                            </div>
                            <div class="success-stat">
                                <span class="success-stat-number">3</span>
                                <span class="success-stat-label">Days</span>
                            </div>
                        </div>
                        <h3 class="success-title">Apartment Complex Deal</h3>
                        <p class="success-description">Managed multiple competing offers for an apartment complex, creating a bidding situation that resulted in the best possible price for the seller.</p>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section">
                <h2 class="cta-title">Ready for Expert Negotiation?</h2>
                <p class="cta-subtitle">Let our experienced negotiation team secure the best possible deal for your real estate transaction.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button primary">
                        <i class="fas fa-handshake"></i>
                        Start Negotiation
                    </a>
                    <a href="our-work.php" class="cta-button secondary">
                        <i class="fas fa-eye"></i>
                        See Our Process
                    </a>
                </div>
            </section>
        </div>
    </main>

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
            { element: document.getElementById('counter1'), base: 95, suffix: '%' },
            { element: document.getElementById('counter2'), base: 250, suffix: '+' },
            { element: document.getElementById('counter3'), base: 15, suffix: '' },
            { element: document.getElementById('counter4'), base: 5, suffix: '' }
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
                        
                        if (counter.element.id === 'counter4') {
                            counter.element.textContent = '₹' + finalNumber;
                        } else {
                            counter.element.textContent = finalNumber + counter.suffix;
                        }
                        
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
                <?php include 'footer.php';?>

</body>
</html>
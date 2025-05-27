<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing Success - Guntur Properties</title>
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

        .hero-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-highlight {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white);
            font-weight: 500;
            transition: var(--transition);
        }

        .hero-highlight:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .hero-highlight i {
            font-size: 1.25rem;
            color: var(--accent);
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

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-bottom: 80px;
        }

        .service-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 40px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
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

        .service-card::after {
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

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-card:hover::after {
            transform: scale(1);
        }

        .service-icon {
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

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.01em;
        }

        .service-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .service-features {
            list-style: none;
        }

        .service-features li {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .service-features li::before {
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
        }

        .process-step:hover .process-card {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

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
        }

        .process-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .process-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* Excellence Section */
        .excellence-section {
            background: var(--gray-900);
            padding: 80px 0;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            margin: 80px 0;
        }

        .excellence-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .excellence-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .excellence-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .excellence-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .excellence-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
        }

        .excellence-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .excellence-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .excellence-icon {
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

        .excellence-card:hover .excellence-icon {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--accent), #f57c00);
        }

        .excellence-title-card {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 12px;
        }

        .excellence-text {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Success Stories */
        .stories-section {
            background: var(--white);
        }

        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .story-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 32px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .story-card::before {
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

        .story-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            background: var(--white);
        }

        .story-card:hover::before {
            transform: scaleY(1);
        }

        .story-type {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 6px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .story-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .story-description {
            color: var(--gray-600);
            line-height: 1.6;
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .story-outcome {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .story-outcome i {
            color: var(--secondary);
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

            .services-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .service-card {
                padding: 32px 24px;
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
                width: 100%;
            }

            .hero-highlights {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .excellence-grid,
            .stories-grid {
                grid-template-columns: 1fr;
                gap: 24px;
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
            .service-card,
            .excellence-card,
            .story-card {
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
                <h1 class="hero-title">Closing Success</h1>
                <p class="hero-subtitle">Ensuring every real estate transaction reaches successful completion through meticulous attention to detail, comprehensive documentation, and expert guidance from contract to keys.</p>
                
                <div class="hero-highlights">
                    <div class="hero-highlight">
                        <i class="fas fa-shield-check"></i>
                        <span>Guaranteed Process</span>
                    </div>
                    <div class="hero-highlight">
                        <i class="fas fa-clock"></i>
                        <span>Timely Completion</span>
                    </div>
                    <div class="hero-highlight">
                        <i class="fas fa-file-contract"></i>
                        <span>Legal Compliance</span>
                    </div>
                    <div class="hero-highlight">
                        <i class="fas fa-handshake"></i>
                        <span>Smooth Transactions</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Services Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Comprehensive Closing Services</h2>
                    <p class="section-subtitle">Every aspect of the closing process handled with precision and care to ensure successful transaction completion.</p>
                </div>

                <div class="services-grid">
                    <!-- Documentation Management -->
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="service-title">Documentation Management</h3>
                        <p class="service-description">Complete handling of all legal documents, contracts, and paperwork required for successful property transactions.</p>
                        <ul class="service-features">
                            <li>Legal document preparation</li>
                            <li>Contract review and verification</li>
                            <li>Title document processing</li>
                            <li>Registration paperwork</li>
                            <li>Compliance documentation</li>
                            <li>Record maintenance</li>
                        </ul>
                    </div>

                    <!-- Legal Compliance -->
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3 class="service-title">Legal Compliance</h3>
                        <p class="service-description">Ensuring all transactions meet legal requirements and regulatory standards for complete peace of mind.</p>
                        <ul class="service-features">
                            <li>Regulatory compliance check</li>
                            <li>Legal requirement verification</li>
                            <li>Government approval coordination</li>
                            <li>Tax calculation and processing</li>
                            <li>Clearance certificate management</li>
                            <li>Due diligence completion</li>
                        </ul>
                    </div>

                    <!-- Financial Coordination -->
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h3 class="service-title">Financial Coordination</h3>
                        <p class="service-description">Managing all financial aspects including payments, taxes, and fee calculations for transparent transactions.</p>
                        <ul class="service-features">
                            <li>Payment scheduling</li>
                            <li>Tax calculation assistance</li>
                            <li>Fee structure transparency</li>
                            <li>Bank coordination</li>
                            <li>Loan processing support</li>
                            <li>Financial documentation</li>
                        </ul>
                    </div>

                    <!-- Timeline Management -->
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="service-title">Timeline Management</h3>
                        <p class="service-description">Careful coordination of all closing activities to ensure timely completion within agreed schedules.</p>
                        <ul class="service-features">
                            <li>Milestone tracking</li>
                            <li>Deadline management</li>
                            <li>Schedule coordination</li>
                            <li>Progress monitoring</li>
                            <li>Contingency planning</li>
                            <li>Regular status updates</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Closing Process -->
            <section class="process-section">
                <div class="container">
                    <div class="section-header fade-in">
                        <h2 class="section-title">Our Closing Process</h2>
                        <p class="section-subtitle">A systematic approach that ensures every transaction closes successfully and on time.</p>
                    </div>

                    <div class="process-timeline">
                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Initial Review</h3>
                                    <p class="process-description">Comprehensive review of all contract terms, conditions, and requirements to establish clear closing timeline and requirements.</p>
                                </div>
                            </div>
                            <div class="process-number">1</div>
                        </div>

                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Document Collection</h3>
                                    <p class="process-description">Systematic gathering of all required documents, certificates, and clearances from relevant parties and authorities.</p>
                                </div>
                            </div>
                            <div class="process-number">2</div>
                        </div>

                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Verification Process</h3>
                                    <p class="process-description">Thorough verification of all documents, legal compliance, and financial arrangements to prevent any closing delays.</p>
                                </div>
                            </div>
                            <div class="process-number">3</div>
                        </div>

                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Coordination Meeting</h3>
                                    <p class="process-description">Pre-closing meeting with all parties to review final terms, address any concerns, and schedule the closing ceremony.</p>
                                </div>
                            </div>
                            <div class="process-number">4</div>
                        </div>

                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Final Execution</h3>
                                    <p class="process-description">Supervised execution of all final documents, fund transfers, and official registrations to complete the transaction.</p>
                                </div>
                            </div>
                            <div class="process-number">5</div>
                        </div>

                        <div class="process-step">
                            <div class="process-content">
                                <div class="process-card">
                                    <h3 class="process-title">Post-Closing Support</h3>
                                    <p class="process-description">Continued support after closing to handle any remaining paperwork, transfer utilities, and ensure smooth transition.</p>
                                </div>
                            </div>
                            <div class="process-number">6</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Excellence Section -->
            <section class="excellence-section">
                <div class="container">
                    <div class="excellence-content">
                        <h2 class="excellence-title">Excellence in Every Closing</h2>
                        <p class="excellence-subtitle">What sets our closing services apart and ensures consistent success for all our clients.</p>

                        <div class="excellence-grid">
                            <div class="excellence-card fade-in">
                                <div class="excellence-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h3 class="excellence-title-card">Attention to Detail</h3>
                                <p class="excellence-text">Meticulous review of every document and requirement to prevent issues and ensure smooth closings.</p>
                            </div>

                            <div class="excellence-card fade-in">
                                <div class="excellence-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="excellence-title-card">Expert Team</h3>
                                <p class="excellence-text">Experienced professionals who understand legal requirements and market practices thoroughly.</p>
                            </div>

                            <div class="excellence-card fade-in">
                                <div class="excellence-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3 class="excellence-title-card">Clear Communication</h3>
                                <p class="excellence-text">Regular updates and transparent communication throughout the entire closing process.</p>
                            </div>

                            <div class="excellence-card fade-in">
                                <div class="excellence-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3 class="excellence-title-card">Risk Management</h3>
                                <p class="excellence-text">Proactive identification and mitigation of potential issues before they become problems.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Success Stories -->
            <section class="stories-section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Closing Success Stories</h2>
                    <p class="section-subtitle">Real examples of how our comprehensive closing services have helped clients achieve their property goals.</p>
                </div>

                <div class="stories-grid">
                    <div class="story-card fade-in">
                        <div class="story-type">Residential</div>
                        <h3 class="story-title">Family Villa Purchase</h3>
                        <p class="story-description">Successfully navigated complex documentation and multiple clearances for a family purchasing their dream villa, ensuring all legal requirements were met efficiently.</p>
                        <div class="story-outcome">
                            <i class="fas fa-check-circle"></i>
                            <span>Closed ahead of schedule with all approvals</span>
                        </div>
                    </div>

                    <div class="story-card fade-in">
                        <div class="story-type">Commercial</div>
                        <h3 class="story-title">Office Complex Transaction</h3>
                        <p class="story-description">Managed the closing of a large commercial office complex involving multiple stakeholders, complex financing, and regulatory approvals.</p>
                        <div class="story-outcome">
                            <i class="fas fa-check-circle"></i>
                            <span>Seamless multi-party coordination</span>
                        </div>
                    </div>

                    <div class="story-card fade-in">
                        <div class="story-type">Investment</div>
                        <h3 class="story-title">Portfolio Acquisition</h3>
                        <p class="story-description">Facilitated the closing of multiple properties for an investment portfolio, coordinating simultaneous closings and financing arrangements.</p>
                        <div class="story-outcome">
                            <i class="fas fa-check-circle"></i>
                            <span>Multiple properties closed simultaneously</span>
                        </div>
                    </div>

                    <div class="story-card fade-in">
                        <div class="story-type">Development</div>
                        <h3 class="story-title">Land Development Deal</h3>
                        <p class="story-description">Managed complex land acquisition closing involving environmental clearances, zoning approvals, and development rights transfers.</p>
                        <div class="story-outcome">
                            <i class="fas fa-check-circle"></i>
                            <span>All regulatory approvals secured</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section">
                <h2 class="cta-title">Ready for a Successful Closing?</h2>
                <p class="cta-subtitle">Trust our experienced team to handle every detail of your property closing with precision and care.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button primary">
                        <i class="fas fa-file-contract"></i>
                        Start Your Closing
                    </a>
                    <a href="our-work.php" class="cta-button secondary">
                        <i class="fas fa-eye"></i>
                        Learn Our Process
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

        document.querySelectorAll('.fade-in, .process-step').forEach(el => {
            observer.observe(el);
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
                <?php include 'footer.php';?>

</body>
</html>
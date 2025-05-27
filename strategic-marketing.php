<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Marketing - Guntur Properties</title>
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

        .hero-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white);
            font-weight: 500;
            transition: var(--transition);
        }

        .hero-feature:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .hero-feature i {
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
            gap: 40px;
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
            transform: translateY(-12px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-card:hover::after {
            transform: scale(1);
        }

        .service-header {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .service-icon {
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

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(-10deg);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .service-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .service-info .service-type {
            color: var(--gray-600);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }

        .service-description {
            color: var(--gray-600);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 24px;
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
            content: '→';
            color: var(--secondary);
            font-weight: bold;
            margin-right: 12px;
            font-size: 1rem;
        }

        .service-features li:last-child {
            margin-bottom: 0;
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
            max-width: 800px;
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
            margin-bottom: 60px;
            display: flex;
            align-items: center;
        }

        .process-step:nth-child(even) {
            flex-direction: row-reverse;
        }

        .process-step:nth-child(even) .process-content {
            text-align: right;
        }

        .process-content {
            flex: 1;
            max-width: 300px;
            background: var(--white);
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .process-step:hover .process-content {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .process-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: 4px solid var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--white);
            position: relative;
            z-index: 2;
            flex-shrink: 0;
            margin: 0 30px;
            transition: var(--transition);
        }

        .process-step:hover .process-number {
            transform: scale(1.2);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }

        .process-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .process-text {
            color: var(--gray-600);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats-section {
            background: var(--gray-900);
            padding: 80px 0;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            margin: 80px 0;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .stats-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .stats-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .stats-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 32px;
        }

        .stat-card {
            text-align: center;
            padding: 32px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .stat-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Portfolio Section */
        .portfolio-section {
            background: var(--white);
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 48px;
        }

        .portfolio-item {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
        }

        .portfolio-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .portfolio-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .portfolio-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: var(--transition);
        }

        .portfolio-item:hover .portfolio-image::before {
            transform: translateX(100%);
        }

        .portfolio-content {
            padding: 24px;
        }

        .portfolio-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .portfolio-description {
            color: var(--gray-600);
            font-size: 0.9rem;
            line-height: 1.6;
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
                gap: 32px;
            }

            .service-card {
                padding: 32px 24px;
            }

            .service-header {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .service-icon {
                margin-right: 0;
                margin-bottom: 16px;
            }

            .process-timeline::before {
                left: 20px;
            }

            .process-step {
                flex-direction: column !important;
                align-items: flex-start;
                margin-bottom: 40px;
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
                width: 50px;
                height: 50px;
                font-size: 1rem;
            }

            .process-content {
                width: 100%;
                max-width: none;
            }

            .hero-features {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .stats-grid,
            .portfolio-grid {
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
            .stats-grid,
            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .service-card,
            .stat-card,
            .portfolio-content {
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
                <h1 class="hero-title">Strategic Marketing</h1>
                <p class="hero-subtitle">Transform your property listings with our comprehensive marketing approach featuring unique shooting, professional voice-overs, expert editing, and strategic influencer partnerships.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="fas fa-camera"></i>
                        <span>Unique Shooting</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-microphone"></i>
                        <span>Professional Voice-Over</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-cut"></i>
                        <span>Expert Editing</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-users"></i>
                        <span>Influencer Marketing</span>
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
                    <h2 class="section-title">Our Marketing Services</h2>
                    <p class="section-subtitle">Comprehensive marketing solutions that showcase your properties in the most compelling and professional way possible.</p>
                </div>

                <div class="services-grid">
                    <!-- Unique Property Shooting -->
                    <div class="service-card fade-in">
                        <div class="service-header">
                            <div class="service-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="service-info">
                                <h3>Unique Property Shooting</h3>
                                <span class="service-type">Creative Photography</span>
                            </div>
                        </div>
                        <p class="service-description">We capture your properties with unique angles, creative compositions, and professional techniques that make them stand out from the competition.</p>
                        <ul class="service-features">
                            <li>Drone photography for aerial views</li>
                            <li>Golden hour and blue hour shoots</li>
                            <li>Interior staging and lighting</li>
                            <li>360-degree virtual tours</li>
                            <li>Creative architectural perspectives</li>
                            <li>High-resolution professional equipment</li>
                        </ul>
                    </div>

                    <!-- Professional Voice-Over -->
                    <div class="service-card fade-in">
                        <div class="service-header">
                            <div class="service-icon">
                                <i class="fas fa-microphone"></i>
                            </div>
                            <div class="service-info">
                                <h3>Professional Voice-Over</h3>
                                <span class="service-type">Audio Production</span>
                            </div>
                        </div>
                        <p class="service-description">Compelling voice-over narrations in multiple languages that bring your property stories to life with professional quality and emotional appeal.</p>
                        <ul class="service-features">
                            <li>Native Telugu and Hindi voices</li>
                            <li>English voice-over options</li>
                            <li>Script writing and optimization</li>
                            <li>Studio-quality recording</li>
                            <li>Multiple voice talent options</li>
                            <li>Background music integration</li>
                        </ul>
                    </div>

                    <!-- Expert Video Editing -->
                    <div class="service-card fade-in">
                        <div class="service-header">
                            <div class="service-icon">
                                <i class="fas fa-cut"></i>
                            </div>
                            <div class="service-info">
                                <h3>Expert Video Editing</h3>
                                <span class="service-type">Post-Production</span>
                            </div>
                        </div>
                        <p class="service-description">Professional video editing that transforms raw footage into cinematic property presentations with smooth transitions, color grading, and engaging storytelling.</p>
                        <ul class="service-features">
                            <li>Cinematic color grading</li>
                            <li>Smooth transition effects</li>
                            <li>Motion graphics and titles</li>
                            <li>Multi-format optimization</li>
                            <li>Sound design and mixing</li>
                            <li>Brand-consistent styling</li>
                        </ul>
                    </div>

                    <!-- Influencer Marketing -->
                    <div class="service-card fade-in">
                        <div class="service-header">
                            <div class="service-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="service-info">
                                <h3>Influencer Marketing</h3>
                                <span class="service-type">Social Media Strategy</span>
                            </div>
                        </div>
                        <p class="service-description">Strategic partnerships with local influencers and content creators to amplify your property reach and connect with targeted audiences effectively.</p>
                        <ul class="service-features">
                            <li>Local influencer partnerships</li>
                            <li>Targeted audience matching</li>
                            <li>Content collaboration strategies</li>
                            <li>Campaign performance tracking</li>
                            <li>Multi-platform promotion</li>
                            <li>ROI-focused approach</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Marketing Process -->
            <section class="process-section">
                <div class="container">
                    <div class="section-header fade-in">
                        <h2 class="section-title">Our Marketing Process</h2>
                        <p class="section-subtitle">A systematic approach that ensures every property gets maximum exposure and engagement.</p>
                    </div>

                    <div class="process-timeline">
                        <div class="process-step fade-in">
                            <div class="process-content">
                                <h3 class="process-title">Property Analysis</h3>
                                <p class="process-text">We analyze your property's unique features and target audience to create a tailored marketing strategy.</p>
                            </div>
                            <div class="process-number">1</div>
                        </div>

                        <div class="process-step fade-in">
                            <div class="process-content">
                                <h3 class="process-title">Creative Shooting</h3>
                                <p class="process-text">Professional photography and videography using unique angles and creative techniques.</p>
                            </div>
                            <div class="process-number">2</div>
                        </div>

                        <div class="process-step fade-in">
                            <div class="process-content">
                                <h3 class="process-title">Content Creation</h3>
                                <p class="process-text">Expert editing, voice-over recording, and content optimization for maximum impact.</p>
                            </div>
                            <div class="process-number">3</div>
                        </div>

                        <div class="process-step fade-in">
                            <div class="process-content">
                                <h3 class="process-title">Strategic Promotion</h3>
                                <p class="process-text">Multi-channel marketing including social media, influencers, and targeted advertising.</p>
                            </div>
                            <div class="process-number">4</div>
                        </div>

                        <div class="process-step fade-in">
                            <div class="process-content">
                                <h3 class="process-title">Performance Tracking</h3>
                                <p class="process-text">Continuous monitoring and optimization based on engagement metrics and lead generation.</p>
                            </div>
                            <div class="process-number">5</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Marketing Stats -->
            <section class="stats-section">
                <div class="container">
                    <div class="stats-content">
                        <h2 class="stats-title">Marketing Performance</h2>
                        <p class="stats-subtitle">Our strategic marketing approach delivers measurable results that accelerate property sales and rentals.</p>

                        <div class="stats-grid">
                            <div class="stat-card fade-in">
                                <span class="stat-number" id="counter1">300</span>
                                <span class="stat-label">Properties Marketed</span>
                            </div>
                            <div class="stat-card fade-in">
                                <span class="stat-number" id="counter2">85</span>
                                <span class="stat-label">Success Rate %</span>
                            </div>
                            <div class="stat-card fade-in">
                                <span class="stat-number" id="counter3">50</span>
                                <span class="stat-label">Influencer Partners</span>
                            </div>
                            <div class="stat-card fade-in">
                                <span class="stat-number" id="counter4">25</span>
                                <span class="stat-label">Days Avg Marketing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Portfolio Section -->
            <section class="portfolio-section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Marketing Portfolio</h2>
                    <p class="section-subtitle">Examples of our creative marketing campaigns and their outstanding results.</p>
                </div>

                <div class="portfolio-grid">
                    <div class="portfolio-item fade-in">
                        <div class="portfolio-image">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="portfolio-content">
                            <h3 class="portfolio-title">Luxury Villa Campaign</h3>
                            <p class="portfolio-description">Cinematic drone footage with professional voice-over resulted in 200% more inquiries.</p>
                        </div>
                    </div>

                    <div class="portfolio-item fade-in">
                        <div class="portfolio-image">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="portfolio-content">
                            <h3 class="portfolio-title">Apartment Complex Shoot</h3>
                            <p class="portfolio-description">Creative interior and exterior photography that sold 90% of units within 3 weeks.</p>
                        </div>
                    </div>

                    <div class="portfolio-item fade-in">
                        <div class="portfolio-image">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="portfolio-content">
                            <h3 class="portfolio-title">Influencer Collaboration</h3>
                            <p class="portfolio-description">Local influencer partnerships generated 500K+ impressions and 50+ quality leads.</p>
                        </div>
                    </div>

                    <div class="portfolio-item fade-in">
                        <div class="portfolio-image">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <div class="portfolio-content">
                            <h3 class="portfolio-title">Voice-Over Success Story</h3>
                            <p class="portfolio-description">Emotional Telugu narration increased engagement by 300% for rural property listings.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section">
                <h2 class="cta-title">Ready to Market Your Property?</h2>
                <p class="cta-subtitle">Let our strategic marketing expertise showcase your property with unique creativity and professional excellence.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button primary">
                        <i class="fas fa-rocket"></i>
                        Start Marketing Campaign
                    </a>
                    <a href="portfolio.php" class="cta-button secondary">
                        <i class="fas fa-eye"></i>
                        View Full Portfolio
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
            { element: document.getElementById('counter1'), base: 300, suffix: '+' },
            { element: document.getElementById('counter2'), base: 85, suffix: '%' },
            { element: document.getElementById('counter3'), base: 50, suffix: '+' },
            { element: document.getElementById('counter4'), base: 25, suffix: '' }
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

                const statsObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(animateCounter, 500);
                            statsObserver.unobserve(entry.target);
                        }
                    });
                });
                
                statsObserver.observe(document.querySelector('.stats-section'));
            }
        });
    </script>
                <?php include 'footer.php';?>

</body>
</html>
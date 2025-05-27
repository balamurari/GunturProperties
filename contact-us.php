<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Guntur Properties</title>
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

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 80px;
        }

        /* Contact Form */
        .contact-form {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: var(--shadow-lg);
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            color: var(--gray-900);
            background: var(--white);
            transition: var(--transition);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .form-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: var(--transition);
        }

        .form-submit:hover::before {
            left: 100%;
        }

        .form-submit:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Contact Info */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .contact-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 32px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .contact-card::before {
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

        .contact-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .contact-card:hover::before {
            transform: scaleX(1);
        }

        .contact-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 1.25rem;
            color: var(--white);
            flex-shrink: 0;
        }

        .contact-card:hover .contact-icon {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            transform: scale(1.1);
        }

        .contact-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .contact-subtitle {
            font-size: 0.875rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .contact-details {
            color: var(--gray-700);
            line-height: 1.6;
        }

        .contact-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .contact-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Social Media Section */
        .social-section {
            background: var(--gray-50);
            padding: 80px 0;
            border-radius: 24px;
            margin: 80px 0;
        }

        .social-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .social-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 32px;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .social-card::after {
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

        .social-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .social-card:hover::after {
            transform: scale(1);
        }

        .social-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: var(--white);
            transition: var(--transition);
            position: relative;
            z-index: 2;
        }

        .social-card.instagram .social-icon {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
        }

        .social-card.youtube .social-icon {
            background: #ff0000;
        }

        .social-card.facebook .social-icon {
            background: #1877f2;
        }

        .social-card:hover .social-icon {
            transform: scale(1.2) rotate(5deg);
        }

        .social-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .social-description {
            color: var(--gray-600);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
            position: relative;
            z-index: 2;
        }

        .social-link:hover {
            color: var(--secondary);
            transform: translateX(4px);
        }

        /* Map Section */
        .map-section {
            background: var(--gray-900);
            padding: 80px 0;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
        }

        .map-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .map-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .map-title {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .map-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 48px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .map-placeholder {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-lg);
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.125rem;
        }

        .map-placeholder i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 100px 0 60px;
            }

            .main {
                padding: 80px 0;
            }

            .section {
                margin-bottom: 80px;
            }

            .section-header {
                margin-bottom: 60px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .contact-form,
            .contact-card {
                padding: 32px 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .hero-features {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .social-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .container {
                padding: 0 20px;
            }
        }

        @media (max-width: 480px) {
            .contact-form,
            .contact-card,
            .social-card {
                padding: 24px 20px;
            }

            .container {
                padding: 0 16px;
            }

            .contact-header {
                flex-direction: column;
                text-align: center;
            }

            .contact-icon {
                margin-right: 0;
                margin-bottom: 16px;
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
        .form-submit:focus {
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
                <h1 class="hero-title">Contact Us</h1>
                <p class="hero-subtitle">Ready to start your real estate journey? Get in touch with our expert team for personalized service and transparent guidance.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="fas fa-phone"></i>
                        <span>Direct Communication</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-clock"></i>
                        <span>Quick Response</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-users"></i>
                        <span>Expert Consultation</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-shield-check"></i>
                        <span>Trusted Service</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Contact Section -->
            <section class="section">
                <div class="section-header fade-in">
                    <h2 class="section-title">Get In Touch</h2>
                    <p class="section-subtitle">We're here to help you with all your real estate needs. Reach out to us through any of the following channels.</p>
                </div>

                <div class="contact-grid">
                    <!-- Contact Form -->
                    <div class="contact-form fade-in">
                        <h3 class="form-title">Send Us a Message</h3>
                        <form id="contactForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="form-input" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-input" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="service" class="form-label">Service Interest</label>
                                <select id="service" name="service" class="form-select" required>
                                    <option value="">Select a service</option>
                                    <option value="buying">Property Buying</option>
                                    <option value="selling">Property Selling</option>
                                    <option value="renting">Property Renting</option>
                                    <option value="investment">Investment Consultation</option>
                                    <option value="marketing">Marketing Services</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">Message</label>
                                <textarea id="message" name="message" class="form-textarea" placeholder="Tell us about your property requirements or questions..." required></textarea>
                            </div>
                            
                            <button type="submit" class="form-submit">
                                Send Message
                            </button>
                        </form>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-info">
                        <!-- Phone -->
                        <div class="contact-card fade-in">
                            <div class="contact-header">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h3 class="contact-title">Call Us</h3>
                                    <p class="contact-subtitle">Direct Phone Line</p>
                                </div>
                            </div>
                            <div class="contact-details">
                                <p><a href="tel:+918500721069" class="contact-link">+91 85007 21069</a></p>
                                <p>Available during business hours for immediate assistance</p>
                            </div>
                        </div>

                        <!-- WhatsApp -->
                        <div class="contact-card fade-in">
                            <div class="contact-header">
                                <div class="contact-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div>
                                    <h3 class="contact-title">WhatsApp</h3>
                                    <p class="contact-subtitle">Quick Messaging</p>
                                </div>
                            </div>
                            <div class="contact-details">
                                <p><a href="https://wa.me/918332852189" class="contact-link" target="_blank">+91 83328 52189</a></p>
                                <p>Send us a message for quick responses and property updates</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="contact-card fade-in">
                            <div class="contact-header">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h3 class="contact-title">Email Us</h3>
                                    <p class="contact-subtitle">Detailed Inquiries</p>
                                </div>
                            </div>
                            <div class="contact-details">
                                <p><a href="mailto:gunturproperinfogunturproperties@gmail.com" class="contact-link">gunturproperinfogunturproperties@gmail.com</a></p>
                                <p>Send detailed property requirements and we'll get back to you</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="contact-card fade-in">
                            <div class="contact-header">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="contact-title">Visit Us</h3>
                                    <p class="contact-subtitle">Office Location</p>
                                </div>
                            </div>
                            <div class="contact-details">
                                <p>Guntur, Andhra Pradesh</p>
                                <p>Visit our office for in-person consultations and property viewings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Social Media Section -->
            <section class="social-section">
                <div class="container">
                    <div class="section-header fade-in">
                        <h2 class="section-title">Follow Us</h2>
                        <p class="section-subtitle">Stay connected with us on social media for the latest property listings and real estate insights.</p>
                    </div>

                    <div class="social-grid">
                        <!-- Instagram -->
                        <div class="social-card instagram fade-in">
                            <div class="social-icon">
                                <i class="fab fa-instagram"></i>
                            </div>
                            <h3 class="social-title">Instagram</h3>
                            <p class="social-description">Follow us for beautiful property photos, behind-the-scenes content, and real estate tips.</p>
                            <a href="https://www.instagram.com/gunturpropertiess?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" class="social-link">
                                @gunturpropertiess <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>

                        <!-- YouTube -->
                        <div class="social-card youtube fade-in">
                            <div class="social-icon">
                                <i class="fab fa-youtube"></i>
                            </div>
                            <h3 class="social-title">YouTube</h3>
                            <p class="social-description">Watch property tours, client testimonials, and educational content about real estate.</p>
                            <a href="http://www.youtube.com/@Gunturproperties2025" target="_blank" class="social-link">
                                @Gunturproperties2025 <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>

                        <!-- Facebook -->
                        <div class="social-card facebook fade-in">
                            <div class="social-icon">
                                <i class="fab fa-facebook-f"></i>
                            </div>
                            <h3 class="social-title">Facebook</h3>
                            <p class="social-description">Join our community for property updates, market news, and client success stories.</p>
                            <a href="https://www.facebook.com/share/1Be885ZdP7/" target="_blank" class="social-link">
                                Visit our Page <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Map Section -->
            <section class="map-section">
                <div class="container">
                    <div class="map-content">
                        <h2 class="map-title">Find Us in Guntur</h2>
                        <p class="map-subtitle">Located in the heart of Guntur, we're easily accessible for all your real estate needs.</p>
                        
                        <div class="map-placeholder">
                            <div style="text-align: center;">
                                <i class="fas fa-map-marker-alt" style="display: block; margin-bottom: 16px;"></i>
                                <p>Interactive Map Coming Soon</p>
                                <p style="font-size: 0.9rem; opacity: 0.7;">Guntur, Andhra Pradesh</p>
                            </div>
                        </div>
                    </div>
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

        // Form handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Show success message (you can integrate with your backend here)
            alert('Thank you for your message! We will get back to you soon.');
            
            // Reset form
            this.reset();
        });

        // Enhanced form validation
        const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });
            
            input.addEventListener('input', function() {
                if (this.style.borderColor === 'rgb(239, 68, 68)') {
                    this.style.borderColor = '#3f51b5';
                }
            });
        });

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            e.target.value = value;
        });

        // WhatsApp click tracking
        document.querySelector('a[href*="wa.me"]').addEventListener('click', function() {
            console.log('WhatsApp contact initiated');
        });

        // Email click tracking
        document.querySelector('a[href^="mailto:"]').addEventListener('click', function() {
            console.log('Email contact initiated');
        });

        // Phone click tracking
        document.querySelector('a[href^="tel:"]').addEventListener('click', function() {
            console.log('Phone contact initiated');
        });
    </script>
                <?php include 'footer.php';?>

</body>
</html>
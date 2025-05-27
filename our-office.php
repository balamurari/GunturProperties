<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Office - Guntur Properties</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Main Container */
        .main-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, #5c6bc0 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: backgroundMove 20s linear infinite;
        }

        @keyframes backgroundMove {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-60px) translateY(-60px); }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 2;
        }

        /* Coming Soon Content */
        .coming-soon-content {
            text-align: center;
            color: var(--white);
        }

        .office-icon {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 40px;
            font-size: 3rem;
            color: var(--accent);
            backdrop-filter: blur(10px);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .main-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--white) 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 20px rgba(255,255,255,0.3);
            animation: titleSlide 1s ease-out;
        }

        @keyframes titleSlide {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .coming-soon-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent) 0%, #f57c00 100%);
            color: var(--white);
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
            animation: badgeSlide 1s ease-out 0.3s both;
        }

        @keyframes badgeSlide {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .main-description {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 48px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
            font-weight: 300;
            animation: descSlide 1s ease-out 0.5s both;
        }

        @keyframes descSlide {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin: 48px 0;
            animation: featuresSlide 1s ease-out 0.7s both;
        }

        @keyframes featuresSlide {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-lg);
            padding: 24px;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 1.25rem;
            color: var(--white);
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--white);
        }

        .feature-text {
            font-size: 0.9rem;
            opacity: 0.8;
            color: var(--white);
        }

        /* Contact Section */
        .contact-section {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-xl);
            padding: 40px;
            backdrop-filter: blur(20px);
            margin: 48px 0;
            animation: contactSlide 1s ease-out 0.9s both;
        }

        @keyframes contactSlide {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contact-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 24px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--white);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--white);
            flex-shrink: 0;
        }

        .contact-info a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .contact-info a:hover {
            color: var(--accent);
        }

        .contact-text {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 4px;
        }

        /* CTA Buttons */
        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            animation: ctaSlide 1s ease-out 1.1s both;
        }

        @keyframes ctaSlide {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: var(--transition);
        }

        .cta-button:hover::before {
            left: 100%;
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

        /* Newsletter Section */
        .newsletter-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 32px;
            backdrop-filter: blur(10px);
            margin-top: 40px;
            animation: newsletterSlide 1s ease-out 1.3s both;
        }

        @keyframes newsletterSlide {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .newsletter-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 12px;
        }

        .newsletter-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 24px;
        }

        .newsletter-form {
            display: flex;
            gap: 12px;
            max-width: 400px;
            margin: 0 auto;
        }

        .newsletter-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .newsletter-input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.15);
        }

        .newsletter-submit {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .newsletter-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .main-title {
                font-size: 2.5rem;
            }

            .main-description {
                font-size: 1.125rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .contact-section,
            .newsletter-section {
                padding: 32px 24px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
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

            .newsletter-form {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 16px;
            }

            .office-icon {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .main-title {
                font-size: 2rem;
            }

            .contact-section,
            .newsletter-section {
                padding: 24px 20px;
            }
        }

        /* Focus States */
        .cta-button:focus,
        .newsletter-input:focus,
        .newsletter-submit:focus {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        /* Selection */
        ::selection {
            background: var(--accent);
            color: var(--white);
        }
    </style>
</head>
<body>
            <?php include 'header.php';?>

    <main class="main-container">
        <div class="container">
            <div class="coming-soon-content">
                <!-- Office Icon -->
                <div class="office-icon">
                    <i class="fas fa-building"></i>
                </div>

                <!-- Main Title -->
                <h1 class="main-title">Our Office</h1>

                <!-- Coming Soon Badge -->
                <div class="coming-soon-badge">
                    Coming Soon
                </div>

                <!-- Description -->
                <p class="main-description">
                    We're working on something special! Our dedicated office space is being designed to provide you with the best real estate consultation experience. Stay tuned for updates on our new location.
                </p>

                <!-- Features Grid -->
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Expert Consultations</h3>
                        <p class="feature-text">In-person meetings with our real estate professionals</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="feature-title">Property Viewing</h3>
                        <p class="feature-text">Comfortable space to review property portfolios</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="feature-title">Deal Closure</h3>
                        <p class="feature-text">Professional environment for finalizing transactions</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="feature-title">Prime Location</h3>
                        <p class="feature-text">Easily accessible location in Guntur</p>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="contact-section">
                    <h2 class="contact-title">Get In Touch Meanwhile</h2>
                    <div class="contact-grid">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-info">
                                <a href="tel:+918500721069">+91 85007 21069</a>
                                <div class="contact-text">Call us directly</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="contact-info">
                                <a href="https://wa.me/918332852189" target="_blank">+91 83328 52189</a>
                                <div class="contact-text">WhatsApp us</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <a href="mailto:gunturproperinfogunturproperties@gmail.com">Email Us</a>
                                <div class="contact-text">Send us a message</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info">
                                <span>Guntur, AP</span>
                                <div class="contact-text">Our service area</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="cta-buttons">
                    <a href="contact.php" class="cta-button primary">
                        <i class="fas fa-phone"></i>
                        Contact Us Now
                    </a>
                    <a href="properties.php" class="cta-button secondary">
                        <i class="fas fa-eye"></i>
                        Browse Properties
                    </a>
                </div>

                <!-- Newsletter Section -->
                <div class="newsletter-section">
                    <h3 class="newsletter-title">Stay Updated</h3>
                    <p class="newsletter-text">Be the first to know when our office opens and get exclusive property updates.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                        <button type="submit" class="newsletter-submit">
                            <i class="fas fa-bell"></i> Notify Me
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Newsletter form handling
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('.newsletter-input').value;
            
            // Here you can integrate with your email service
            alert('Thank you! We\'ll notify you when our office opens.');
            this.reset();
        });

        // Enhanced animations on scroll (for mobile)
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Floating animation for cards
        const cards = document.querySelectorAll('.feature-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${0.1 * index}s`;
        });

        // Contact link interactions
        document.querySelectorAll('a[href^="tel:"], a[href^="mailto:"], a[href*="wa.me"]').forEach(link => {
            link.addEventListener('click', function() {
                console.log(`Contact initiated: ${this.href}`);
            });
        });

        // Smooth reveal animation for elements
        const revealElements = document.querySelectorAll('.feature-card, .contact-section, .newsletter-section');
        revealElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    </script>
                <?php include 'footer.php';?>

</body>
</html>
<?php include "header.php"?>
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Have questions? We're here to help you find your dream property</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="section-tag">
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                    <h2>Get in Touch</h2>
                    <p>We're always eager to hear from you, whether you have a question about properties, need advice, or want to list with us.</p>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-details">
                            <h3>Office Address</h3>
                            <p>123 Real Estate Avenue, <br>Guntur City, 522002</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-details">
                            <h3>Phone Number</h3>
                            <p>+91 123 456 7890<br>+91 987 654 3210</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-details">
                            <h3>Email Address</h3>
                            <p>info@gunturproperties.com<br>support@gunturproperties.com</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-details">
                            <h3>Working Hours</h3>
                            <p>Monday - Saturday: 9AM - 7PM<br>Sunday: Closed</p>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h3>Follow Us</h3>
                        <div class="links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <h2>Send Us a Message</h2>
                    <form id="contactForm" class="contact-form">
                        <div class="form-group">
                            <input type="text" id="name" name="name" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="Your Phone Number" required>
                        </div>
                        
                        <div class="form-group">
                            <select id="subject" name="subject" required>
                                <option value="" disabled selected>Select Subject</option>
                                <option value="property-inquiry">Property Inquiry</option>
                                <option value="property-listing">List Your Property</option>
                                <option value="career">Career Inquiry</option>
                                <option value="feedback">Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <textarea id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <label for="privacy">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <!-- Google Maps iframe would go here in a real implementation -->
                <div class="map-placeholder">
                    <p>Google Maps Integration</p>
                    <p><small>Map showing our office location in Guntur city</small></p>
                </div>
            </div>
        </div>
    </section>
    <?php include "footer.php"?>

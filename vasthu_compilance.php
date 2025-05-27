<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vastu Compliance Checker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #f7931e;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #ecf0f1;
            --border-color: #e9ecef;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
            --shadow-xl: 0 12px 24px rgba(0,0,0,0.18);
            --gradient-primary: linear-gradient(135deg, #ff6b35, #f7931e);
            --gradient-success: linear-gradient(135deg, #4CAF50, #45a049);
            --gradient-warning: linear-gradient(135deg, #ff9800, #f57c00);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Container and Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
        }

        .section-title {
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .section-title i {
            color: var(--secondary-color);
            font-size: clamp(1.5rem, 4vw, 2.5rem);
        }

        .section-header p {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        /* Vastu Wrapper */
        .vastu-wrapper {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
        }

        /* Property Card */
        .property-card {
            background: var(--bg-primary);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .property-card-img {
            position: relative;
            height: 200px;
            background: linear-gradient(45deg, #f0f2f5, #e4e6ea);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .property-card-img::before {
            content: '🏠';
            font-size: 4rem;
            opacity: 0.5;
        }

        .property-card-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .property-card-favorite {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .property-card-favorite:hover {
            background: var(--danger-color);
            color: white;
        }

        .property-card-media {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .property-card-body {
            padding: 1.5rem;
        }

        .property-card-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .property-card-price h4 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 800;
            color: var(--primary-color);
        }

        .property-card-title {
            font-size: clamp(1rem, 3vw, 1.2rem);
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .property-card-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .property-card-features {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .property-card-feature {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            color: var(--text-secondary);
        }

        /* Badge */
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .bg-primary { background: var(--gradient-primary); color: white; }
        .bg-success { background: var(--gradient-success); color: white; }
        .bg-warning { background: var(--gradient-warning); color: white; }
        .bg-danger { background: var(--danger-color); color: white; }

        /* Form Styles */
        .vastu-form-container {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .vastu-form h3 {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .vastu-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .vastu-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-action {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        /* Results Section */
        .vastu-results-container {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            display: none;
        }

        .vastu-results-container.show {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

        .vastu-indicator h4 {
            text-align: center;
            font-size: clamp(1.1rem, 3vw, 1.3rem);
            margin-bottom: 2rem;
            color: var(--text-primary);
        }

        /* Score Circle */
        .vastu-score {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .score-circle {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .score-circle svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .score-bg {
            stroke: #e9ecef;
        }

        .score-fill {
            stroke-linecap: round;
            transition: stroke-dasharray 1s ease-in-out;
        }

        .score-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 800;
            color: var(--text-primary);
        }

        .compliance-rating {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Vastu Details */
        .vastu-details {
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            border-left: 4px solid transparent;
        }

        .detail-item.excellent { border-left-color: var(--success-color); }
        .detail-item.good { border-left-color: var(--info-color); }
        .detail-item.average { border-left-color: var(--warning-color); }
        .detail-item.poor { border-left-color: var(--danger-color); }

        .detail-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: clamp(0.85rem, 2vw, 0.95rem);
        }

        .detail-value {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        /* Recommendations */
        .vastu-recommendations {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .vastu-recommendations h4 {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .recommendation-item {
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--bg-primary);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .recommendation-item:last-child {
            margin-bottom: 0;
        }

        .recommendation-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .recommendation-desc {
            color: var(--text-secondary);
            font-size: clamp(0.85rem, 2vw, 0.9rem);
            line-height: 1.5;
        }

        /* Tips Section */
        .vastu-tips {
            margin-top: 2rem;
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .vastu-tips h3 {
            text-align: center;
            font-size: clamp(1.3rem, 3vw, 1.8rem);
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }

        .tips-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .tip-card {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .tip-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .tip-card h4 {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }

        .tip-card p {
            color: var(--text-secondary);
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (min-width: 640px) {
            .container {
                padding: 1.5rem;
            }

            .vastu-wrapper {
                padding: 2rem;
                gap: 2rem;
            }

            .property-card-img {
                height: 250px;
            }

            .tips-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-action {
                justify-content: space-between;
            }
        }

        @media (min-width: 768px) {
            .vastu-wrapper {
                grid-template-columns: 1fr 1fr;
            }

            .vastu-results-container {
                grid-column: 1 / -1;
            }

            .tips-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .container {
                padding: 2rem 1rem;
            }

            .vastu-wrapper {
                grid-template-columns: 350px 1fr 350px;
                padding: 2.5rem;
            }

            .vastu-results-container {
                grid-column: auto;
            }

            .tips-container {
                grid-template-columns: repeat(4, 1fr);
            }

            .score-circle {
                width: 180px;
                height: 180px;
            }
        }

        @media (min-width: 1200px) {
            .vastu-wrapper {
                grid-template-columns: 400px 1fr 400px;
            }
        }

        /* Touch Optimizations */
        @media (hover: none) and (pointer: coarse) {
            .vastu-select, .btn-primary, .btn-secondary {
                font-size: 16px;
                padding: 1rem;
            }

            .tip-card:hover {
                transform: none;
            }

            .property-card:hover {
                transform: none;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pulse {
            animation: pulse 0.3s ease-in-out;
        }

        /* Loading State */
        .loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f8f9fa;
                --text-secondary: #adb5bd;
                --bg-primary: #2c3e50;
                --bg-secondary: #34495e;
                --bg-tertiary: #3a4a5c;
                --border-color: #4a5f7a;
            }
        }
    </style>
</head>
<body>
        <?php include 'header.php';?>

    <div class="container">
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-compass"></i>
                    <span>Vastu Compliance Checker</span>
                </h2>
                <p>Check your property's Vastu compliance with our interactive analysis tool</p>
            </div>
            
            <div class="vastu-wrapper">
                <!-- Property Details Card -->
                <div class="property-card">
                    <div class="property-card-img">
                        <span class="property-card-badge sale">For Sale</span>
                        <button class="property-card-favorite">
                            <i class="far fa-heart"></i>
                        </button>
                        <div class="property-card-media">
                            <i class="fas fa-camera"></i> 12
                        </div>
                    </div>
                    <div class="property-card-body">
                        <div class="property-card-price">
                            <h4>₹95 Lac</h4>
                            <span class="badge bg-primary">Premium</span>
                        </div>
                        <h5 class="property-card-title">Vastu Compliant Villa in Amaravathi Road</h5>
                        <div class="property-card-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Amaravathi Road, Guntur</span>
                        </div>
                        <div class="property-card-features">
                            <span class="property-card-feature">
                                <i class="fas fa-ruler-combined"></i> 2200 sq.ft
                            </span>
                            <span class="property-card-feature">
                                <i class="fas fa-bed"></i> 4
                            </span>
                            <span class="property-card-feature">
                                <i class="fas fa-bath"></i> 3
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Vastu Selection Form -->
                <div class="vastu-form-container">
                    <div class="vastu-form">
                        <h3>Select Property Features</h3>
                        
                        <div class="form-group">
                            <label for="main-door">Main Door Facing</label>
                            <select id="main-door" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East (Highly Auspicious)</option>
                                <option value="east">East (Auspicious)</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West</option>
                                <option value="west">West</option>
                                <option value="northwest">North-West</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="kitchen">Kitchen Location</label>
                            <select id="kitchen" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East</option>
                                <option value="east">East</option>
                                <option value="southeast">South-East (Ideal)</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West</option>
                                <option value="west">West</option>
                                <option value="northwest">North-West (Good)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="master-bedroom">Master Bedroom Location</label>
                            <select id="master-bedroom" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East</option>
                                <option value="east">East</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West (Ideal)</option>
                                <option value="west">West (Good)</option>
                                <option value="northwest">North-West</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="bathroom">Bathroom Location</label>
                            <select id="bathroom" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East</option>
                                <option value="east">East</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West</option>
                                <option value="west">West (Good)</option>
                                <option value="northwest">North-West (Ideal)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="living-room">Living Room Location</label>
                            <select id="living-room" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North (Good)</option>
                                <option value="northeast">North-East (Good)</option>
                                <option value="east">East (Ideal)</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West</option>
                                <option value="west">West</option>
                                <option value="northwest">North-West</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pooja-room">Pooja Room Location</label>
                            <select id="pooja-room" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East (Ideal)</option>
                                <option value="east">East (Good)</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South</option>
                                <option value="southwest">South-West</option>
                                <option value="west">West</option>
                                <option value="northwest">North-West</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="staircase">Staircase Location</label>
                            <select id="staircase" class="vastu-select">
                                <option value="">Select Direction</option>
                                <option value="north">North</option>
                                <option value="northeast">North-East</option>
                                <option value="east">East</option>
                                <option value="southeast">South-East</option>
                                <option value="south">South (Good)</option>
                                <option value="southwest">South-West (Good)</option>
                                <option value="west">West (Ideal)</option>
                                <option value="northwest">North-West</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="plot-shape">Plot Shape</label>
                            <select id="plot-shape" class="vastu-select">
                                <option value="">Select Shape</option>
                                <option value="square">Square (Ideal)</option>
                                <option value="rectangle">Rectangle (Good)</option>
                                <option value="irregular">Irregular Shape</option>
                                <option value="circular">Circular</option>
                                <option value="l-shaped">L-Shaped</option>
                                <option value="t-shaped">T-Shaped</option>
                            </select>
                        </div>
                        
                        <div class="form-action">
                            <button id="calculate-vastu" class="btn-primary">
                                <i class="fas fa-calculator"></i> Calculate Vastu Compliance
                            </button>
                            <button id="reset-vastu" class="btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Vastu Results -->
                <div class="vastu-results-container" id="vastu-results">
                    <div class="vastu-indicator">
                        <h4>Vastu Compliance Score</h4>
                        
                        <div class="vastu-score">
                            <div class="score-circle" data-score="0">
                                <svg viewBox="0 0 36 36">
                                    <path class="score-bg"
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="#eee" stroke-width="3" />
                                    <path class="score-fill"
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="#4CAF50" stroke-width="3" stroke-dasharray="0, 100" />
                                </svg>
                                <span class="score-value" id="compliance-score">0%</span>
                            </div>
                        </div>
                        
                        <div class="compliance-rating" id="compliance-rating">
                            <span class="badge bg-warning">Not Evaluated</span>
                        </div>
                        
                        <div class="vastu-details" id="vastu-details">
                            <!-- Vastu details will be populated by JavaScript -->
                        </div>
                        
                        <div class="vastu-recommendations" id="vastu-recommendations">
                            <h4><i class="fas fa-lightbulb"></i> Recommendations</h4>
                            <div class="recommendation-content">
                                <!-- Recommendations will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Vastu Tips Section -->
            <div class="vastu-tips">
                <h3><i class="fas fa-info-circle"></i> Essential Vastu Shastra Tips</h3>
                <div class="tips-container">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <h4>Main Entrance</h4>
                        <p>North, East, and North-East facing doors are considered auspicious as they allow positive energy into the house and promote prosperity.</p>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4>Kitchen Placement</h4>
                        <p>Southeast corner is the ideal location for the kitchen as it aligns with the fire element (Agni), which is associated with this direction in Vastu.</p>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h4>Master Bedroom</h4>
                        <p>The master bedroom is best placed in the Southwest corner as it provides stability, security, and helps in sound sleep for the head of the family.</p>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-om"></i>
                        </div>
                        <h4>Prayer Room</h4>
                        <p>Northeast corner is considered ideal for prayer rooms as it is associated with positive energy, spiritual growth, and divine blessings.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <script>
        class VastuComplianceChecker {
            constructor() {
                this.vastuRules = {
                    'main-door': {
                        'northeast': { score: 100, rating: 'excellent', message: 'Highly auspicious - brings prosperity and positive energy' },
                        'east': { score: 90, rating: 'excellent', message: 'Very auspicious - promotes growth and success' },
                        'north': { score: 85, rating: 'excellent', message: 'Good for wealth and opportunities' },
                        'northwest': { score: 70, rating: 'good', message: 'Acceptable but can be improved' },
                        'west': { score: 60, rating: 'average', message: 'Neutral - consider remedies' },
                        'southeast': { score: 40, rating: 'poor', message: 'Not ideal - may cause health issues' },
                        'south': { score: 30, rating: 'poor', message: 'Inauspicious - requires remedies' },
                        'southwest': { score: 20, rating: 'poor', message: 'Highly inauspicious - needs immediate attention' }
                    },
                    'kitchen': {
                        'southeast': { score: 100, rating: 'excellent', message: 'Perfect placement - aligns with fire element' },
                        'northwest': { score: 80, rating: 'good', message: 'Good alternative for kitchen placement' },
                        'south': { score: 60, rating: 'average', message: 'Acceptable but not ideal' },
                        'east': { score: 50, rating: 'average', message: 'Neutral placement' },
                        'west': { score: 40, rating: 'poor', message: 'Not recommended for kitchen' },
                        'north': { score: 30, rating: 'poor', message: 'Avoid placing kitchen here' },
                        'northeast': { score: 10, rating: 'poor', message: 'Highly inauspicious - avoid at all costs' },
                        'southwest': { score: 20, rating: 'poor', message: 'Not suitable for kitchen placement' }
                    },
                    'master-bedroom': {
                        'southwest': { score: 100, rating: 'excellent', message: 'Ideal for stability and peaceful sleep' },
                        'west': { score: 85, rating: 'good', message: 'Good placement for master bedroom' },
                        'south': { score: 75, rating: 'good', message: 'Acceptable for master bedroom' },
                        'northwest': { score: 60, rating: 'average', message: 'Not the best but manageable' },
                        'southeast': { score: 40, rating: 'poor', message: 'May cause health and relationship issues' },
                        'east': { score: 30, rating: 'poor', message: 'Not recommended for master bedroom' },
                        'north': { score: 25, rating: 'poor', message: 'Avoid for master bedroom' },
                        'northeast': { score: 10, rating: 'poor', message: 'Highly inauspicious for master bedroom' }
                    },
                    'bathroom': {
                        'northwest': { score: 100, rating: 'excellent', message: 'Perfect location for bathroom' },
                        'west': { score: 85, rating: 'good', message: 'Good placement for bathroom' },
                        'south': { score: 70, rating: 'good', message: 'Acceptable bathroom location' },
                        'southeast': { score: 60, rating: 'average', message: 'Not ideal but manageable' },
                        'southwest': { score: 40, rating: 'poor', message: 'Not recommended for bathroom' },
                        'east': { score: 30, rating: 'poor', message: 'Avoid placing bathroom here' },
                        'north': { score: 20, rating: 'poor', message: 'Not suitable for bathroom' },
                        'northeast': { score: 5, rating: 'poor', message: 'Extremely inauspicious - avoid completely' }
                    },
                    'living-room': {
                        'east': { score: 100, rating: 'excellent', message: 'Perfect for family gatherings and positivity' },
                        'north': { score: 90, rating: 'excellent', message: 'Great for social interactions' },
                        'northeast': { score: 85, rating: 'good', message: 'Good placement for living area' },
                        'northwest': { score: 70, rating: 'good', message: 'Acceptable for living room' },
                        'west': { score: 60, rating: 'average', message: 'Neutral placement' },
                        'southeast': { score: 50, rating: 'average', message: 'Not the best choice' },
                        'south': { score: 40, rating: 'poor', message: 'Not recommended for living room' },
                        'southwest': { score: 30, rating: 'poor', message: 'Avoid for main living area' }
                    },
                    'pooja-room': {
                        'northeast': { score: 100, rating: 'excellent', message: 'Most auspicious location for prayers' },
                        'east': { score: 85, rating: 'good', message: 'Good for spiritual practices' },
                        'north': { score: 80, rating: 'good', message: 'Acceptable for prayer room' },
                        'west': { score: 60, rating: 'average', message: 'Not ideal but manageable' },
                        'northwest': { score: 50, rating: 'average', message: 'Neutral placement' },
                        'southeast': { score: 30, rating: 'poor', message: 'Not recommended for prayers' },
                        'south': { score: 25, rating: 'poor', message: 'Avoid for spiritual activities' },
                        'southwest': { score: 20, rating: 'poor', message: 'Not suitable for pooja room' }
                    },
                    'staircase': {
                        'west': { score: 100, rating: 'excellent', message: 'Ideal location for staircase' },
                        'south': { score: 90, rating: 'excellent', message: 'Very good for staircase placement' },
                        'southwest': { score: 85, rating: 'good', message: 'Good placement for stairs' },
                        'southeast': { score: 70, rating: 'good', message: 'Acceptable for staircase' },
                        'northwest': { score: 60, rating: 'average', message: 'Not the best but manageable' },
                        'east': { score: 40, rating: 'poor', message: 'Not recommended for staircase' },
                        'north': { score: 30, rating: 'poor', message: 'Avoid placing stairs here' },
                        'northeast': { score: 10, rating: 'poor', message: 'Highly inauspicious for staircase' }
                    },
                    'plot-shape': {
                        'square': { score: 100, rating: 'excellent', message: 'Perfect shape - brings stability and prosperity' },
                        'rectangle': { score: 90, rating: 'excellent', message: 'Very good shape for construction' },
                        'circular': { score: 70, rating: 'good', message: 'Unique but acceptable shape' },
                        'irregular': { score: 40, rating: 'poor', message: 'Not ideal - may cause problems' },
                        'l-shaped': { score: 30, rating: 'poor', message: 'Avoid L-shaped plots if possible' },
                        't-shaped': { score: 25, rating: 'poor', message: 'Not recommended - brings instability' }
                    }
                };

                this.init();
            }

            init() {
                this.setupEventListeners();
            }

            setupEventListeners() {
                document.getElementById('calculate-vastu').addEventListener('click', () => {
                    this.calculateVastuCompliance();
                });

                document.getElementById('reset-vastu').addEventListener('click', () => {
                    this.resetForm();
                });

                // Add change listeners to all selects for real-time feedback
                document.querySelectorAll('.vastu-select').forEach(select => {
                    select.addEventListener('change', () => {
                        if (this.hasAnySelection()) {
                            this.calculateVastuCompliance();
                        }
                    });
                });
            }

            hasAnySelection() {
                const selects = document.querySelectorAll('.vastu-select');
                return Array.from(selects).some(select => select.value !== '');
            }

            calculateVastuCompliance() {
                const formData = this.getFormData();
                const results = this.analyzeVastu(formData);
                this.displayResults(results);
            }

            getFormData() {
                const elements = ['main-door', 'kitchen', 'master-bedroom', 'bathroom', 'living-room', 'pooja-room', 'staircase', 'plot-shape'];
                const data = {};
                
                elements.forEach(element => {
                    const select = document.getElementById(element);
                    data[element] = select.value;
                });

                return data;
            }

            analyzeVastu(formData) {
                let totalScore = 0;
                let maxScore = 0;
                const details = [];
                const recommendations = [];

                Object.keys(formData).forEach(key => {
                    if (formData[key] && this.vastuRules[key]) {
                        const rule = this.vastuRules[key][formData[key]];
                        if (rule) {
                            totalScore += rule.score;
                            maxScore += 100;

                            details.push({
                                label: this.formatLabel(key),
                                value: formData[key],
                                score: rule.score,
                                rating: rule.rating,
                                message: rule.message
                            });

                            if (rule.score < 70) {
                                recommendations.push({
                                    title: `Improve ${this.formatLabel(key)}`,
                                    description: rule.message,
                                    severity: rule.rating
                                });
                            }
                        }
                    }
                });

                const compliancePercentage = maxScore > 0 ? Math.round((totalScore / maxScore) * 100) : 0;

                return {
                    score: compliancePercentage,
                    details: details,
                    recommendations: recommendations,
                    totalElements: maxScore / 100
                };
            }

            formatLabel(key) {
                return key.split('-').map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1)
                ).join(' ');
            }

            displayResults(results) {
                const resultsContainer = document.getElementById('vastu-results');
                resultsContainer.classList.add('show');

                // Update score circle
                this.updateScoreCircle(results.score);

                // Update compliance rating
                this.updateComplianceRating(results.score);

                // Update details
                this.updateVastuDetails(results.details);

                // Update recommendations
                this.updateRecommendations(results.recommendations);

                // Scroll to results
                resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            updateScoreCircle(score) {
                const circle = document.querySelector('.score-circle');
                const fill = document.querySelector('.score-fill');
                const scoreValue = document.getElementById('compliance-score');

                // Animate score
                let currentScore = 0;
                const increment = score / 50;
                const timer = setInterval(() => {
                    currentScore += increment;
                    if (currentScore >= score) {
                        currentScore = score;
                        clearInterval(timer);
                    }
                    
                    scoreValue.textContent = `${Math.round(currentScore)}%`;
                    fill.style.strokeDasharray = `${currentScore}, 100`;
                }, 20);

                // Update color based on score
                let color = '#f44336'; // Red for poor
                if (score >= 80) color = '#4CAF50'; // Green for excellent
                else if (score >= 60) color = '#2196f3'; // Blue for good
                else if (score >= 40) color = '#ff9800'; // Orange for average

                fill.style.stroke = color;
                circle.setAttribute('data-score', score);
            }

            updateComplianceRating(score) {
                const ratingElement = document.getElementById('compliance-rating');
                let ratingText = '';
                let badgeClass = '';

                if (score >= 80) {
                    ratingText = 'Excellent Vastu Compliance';
                    badgeClass = 'bg-success';
                } else if (score >= 60) {
                    ratingText = 'Good Vastu Compliance';
                    badgeClass = 'bg-primary';
                } else if (score >= 40) {
                    ratingText = 'Average Vastu Compliance';
                    badgeClass = 'bg-warning';
                } else {
                    ratingText = 'Poor Vastu Compliance';
                    badgeClass = 'bg-danger';
                }

                ratingElement.innerHTML = `<span class="badge ${badgeClass}">${ratingText}</span>`;
            }

            updateVastuDetails(details) {
                const detailsContainer = document.getElementById('vastu-details');
                
                if (details.length === 0) {
                    detailsContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">Please select property features to see detailed analysis.</p>';
                    return;
                }

                const detailsHTML = details.map(detail => `
                    <div class="detail-item ${detail.rating}">
                        <div class="detail-label">${detail.label}</div>
                        <div class="detail-value">
                            <span>${this.formatDirection(detail.value)}</span>
                            <span class="badge ${this.getRatingBadgeClass(detail.rating)}">${detail.score}%</span>
                        </div>
                    </div>
                `).join('');

                detailsContainer.innerHTML = detailsHTML;
            }

            updateRecommendations(recommendations) {
                const recommendationsContainer = document.querySelector('.recommendation-content');
                
                if (recommendations.length === 0) {
                    recommendationsContainer.innerHTML = `
                        <div class="recommendation-item">
                            <div class="recommendation-title"><i class="fas fa-check-circle" style="color: var(--success-color);"></i> Excellent Vastu Compliance!</div>
                            <div class="recommendation-desc">Your property shows excellent Vastu compliance. Continue maintaining the positive energy flow.</div>
                        </div>
                    `;
                    return;
                }

                const recommendationsHTML = recommendations.map(rec => `
                    <div class="recommendation-item">
                        <div class="recommendation-title">
                            <i class="fas ${this.getRecommendationIcon(rec.severity)}"></i>
                            ${rec.title}
                        </div>
                        <div class="recommendation-desc">${rec.description}</div>
                    </div>
                `).join('');

                recommendationsContainer.innerHTML = recommendationsHTML;
            }

            formatDirection(direction) {
                return direction.split('-').map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1)
                ).join('-');
            }

            getRatingBadgeClass(rating) {
                const classes = {
                    'excellent': 'bg-success',
                    'good': 'bg-primary',
                    'average': 'bg-warning',
                    'poor': 'bg-danger'
                };
                return classes[rating] || 'bg-warning';
            }

            getRecommendationIcon(severity) {
                const icons = {
                    'excellent': 'fa-check-circle',
                    'good': 'fa-info-circle',
                    'average': 'fa-exclamation-triangle',
                    'poor': 'fa-exclamation-circle'
                };
                return icons[severity] || 'fa-lightbulb';
            }

            resetForm() {
                // Reset all form elements
                document.querySelectorAll('.vastu-select').forEach(select => {
                    select.value = '';
                });

                // Hide results
                const resultsContainer = document.getElementById('vastu-results');
                resultsContainer.classList.remove('show');

                // Reset score circle
                const fill = document.querySelector('.score-fill');
                const scoreValue = document.getElementById('compliance-score');
                fill.style.strokeDasharray = '0, 100';
                scoreValue.textContent = '0%';

                // Reset compliance rating
                document.getElementById('compliance-rating').innerHTML = '<span class="badge bg-warning">Not Evaluated</span>';

                // Clear details and recommendations
                document.getElementById('vastu-details').innerHTML = '';
                document.querySelector('.recommendation-content').innerHTML = '';

                // Add pulse animation to form
                document.querySelector('.vastu-form-container').classList.add('pulse');
                setTimeout(() => {
                    document.querySelector('.vastu-form-container').classList.remove('pulse');
                }, 300);
            }
        }

        // Initialize the Vastu Compliance Checker
        document.addEventListener('DOMContentLoaded', () => {
            new VastuComplianceChecker();

            // Add favorite button functionality
            document.querySelector('.property-card-favorite').addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon.classList.contains('far')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.style.background = 'var(--danger-color)';
                    this.style.color = 'white';
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.style.background = 'rgba(255, 255, 255, 0.9)';
                    this.style.color = 'var(--text-primary)';
                }
            });

            // Add loading animation on form submission
            document.getElementById('calculate-vastu').addEventListener('click', function() {
                this.classList.add('loading');
                setTimeout(() => {
                    this.classList.remove('loading');
                }, 1000);
            });
        });
    </script>
    <?php include 'footer.php';?>
</body>
</html>
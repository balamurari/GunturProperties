<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="7;url=index.php">
    
    <link rel="icon" href="assets/images/favicon.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">
    <title>Guntur Properties Welcome Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .logo-container {
            width: 100%;
            max-width: 600px;
            aspect-ratio: 3/2; /* Maintains the original 600x400 ratio */
            position: relative;
        }

        /* Key parts */
        .key-outer-circle {
            fill: none;
            stroke: #0057b8;
            stroke-width: 2;
            opacity: 0;
            animation: fadeIn 1.5s ease forwards;
        }

        .key-inner-circle {
            fill: #0057b8;
            opacity: 0;
            animation: fadeIn 1.5s ease 0.5s forwards;
        }

        .key-center {
            fill: white;
            opacity: 0;
            animation: fadeIn 1.5s ease 0.8s forwards;
        }

        .key-shaft {
            fill: #0057b8;
            opacity: 0;
            transform-origin: left center;
            animation: fadeIn 1.5s ease 1.2s forwards;
        }

        .key-teeth {
            fill: #0057b8;
            opacity: 0;
            animation: fadeIn 1.5s ease 1.5s forwards;
        }
        
        /* Animation to slide text across circle */
        @keyframes slideText {
            0% { transform: translateX(-50px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        
        .circular-text {
            animation: slideText 2s ease 4.2s forwards !important;
        }

        /* Buildings */
        .building {
            fill: #0057b8;
            opacity: 0;
            transform: translateY(-50px);
        }

        .building-window {
            fill: white;
            opacity: 0;
            transform: translateY(-50px);
        }

        #building1 {
            animation: dropDown 1s ease 2s forwards;
        }
        #windows1 {
            animation: dropDown 1s ease 2s forwards;
        }

        #building2 {
            animation: dropDown 1s ease 2.3s forwards;
        }
        #windows2 {
            animation: dropDown 1s ease 2.3s forwards;
        }

        #building3 {
            animation: dropDown 1s ease 2.6s forwards;
        }
        #windows3 {
            animation: dropDown 1s ease 2.6s forwards;
        }

        #building4 {
            animation: dropDown 1s ease 2.9s forwards;
        }
        #windows4 {
            animation: dropDown 1s ease 2.9s forwards;
        }

        #building5 {
            animation: dropDown 1s ease 3.2s forwards;
        }
        #windows5 {
            animation: dropDown 1s ease 3.2s forwards;
        }

        #building6 {
            animation: dropDown 1s ease 3.5s forwards;
        }
        #windows6 {
            animation: dropDown 1s ease 3.5s forwards;
        }

        #building7 {
            animation: dropDown 1s ease 3.8s forwards;
        }
        #windows7 {
            animation: dropDown 1s ease 3.8s forwards;
        }

        /* Text */
        .circular-text {
            fill: #0057b8;
            font-size: 22px;
            font-weight: bold;
            opacity: 0;
            animation: fadeIn 2s ease 4.2s forwards;
        }

        .tagline {
            font-size: 18px;
            fill: #0057b8;
            opacity: 0;
            animation: fadeIn 2s ease 4.5s forwards;
            font-family: Arial, sans-serif;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes dropDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Media Queries for Responsive Design */
        @media screen and (max-width: 768px) {
            .circular-text {
                font-size: 18px;
            }
            
            .tagline {
                font-size: 14px;
            }
        }

        @media screen and (max-width: 480px) {
            .circular-text {
                font-size: 14px;
            }
            
            .tagline {
                font-size: 12px;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <svg width="100%" height="100%" viewBox="0 0 600 400" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">
            <circle class="key-outer-circle" cx="180" cy="200" r="75" />
            <circle class="key-inner-circle" cx="180" cy="200" r="60" />
            <circle class="key-center" cx="180" cy="200" r="30" />
            
            <rect class="key-shaft" x="180" y="185" width="320" height="30" />
            <!-- Add two rectangles at the end to make it look more like a key tip -->
            <rect class="key-teeth" x="560" y="175" width="10" height="50" />
            
            <path class="key-teeth" d="M500,185 v30 h15 v-15 h15 v15 h15 v-30 h15 v30 h-60" />
            
            <g id="building1" class="building">
                <rect x="385" y="145" width="15" height="40" />
            </g>
            <g id="windows1" class="building-window">
                <rect x="388" y="150" width="4" height="4" />
                <rect x="388" y="158" width="4" height="4" />
                <rect x="388" y="166" width="4" height="4" />
                <rect x="388" y="174" width="4" height="4" />
            </g>
            
            <g id="building2" class="building">
                <rect x="405" y="130" width="18" height="55" />
            </g>
            <g id="windows2" class="building-window">
                <rect x="409" y="135" width="4" height="4" />
                <rect x="415" y="135" width="4" height="4" />
                <rect x="409" y="143" width="4" height="4" />
                <rect x="415" y="143" width="4" height="4" />
                <rect x="409" y="151" width="4" height="4" />
                <rect x="415" y="151" width="4" height="4" />
                <rect x="409" y="159" width="4" height="4" />
                <rect x="415" y="159" width="4" height="4" />
                <rect x="409" y="167" width="4" height="4" />
                <rect x="415" y="167" width="4" height="4" />
                <rect x="409" y="175" width="4" height="4" />
                <rect x="415" y="175" width="4" height="4" />
            </g>
            
            <g id="building3" class="building">
                <rect x="430" y="100" width="22" height="85" />
            </g>
            <g id="windows3" class="building-window">
                <rect x="434" y="108" width="4" height="4" />
                <rect x="442" y="108" width="4" height="4" />
                <rect x="434" y="116" width="4" height="4" />
                <rect x="442" y="116" width="4" height="4" />
                <rect x="434" y="124" width="4" height="4" />
                <rect x="442" y="124" width="4" height="4" />
                <rect x="434" y="132" width="4" height="4" />
                <rect x="442" y="132" width="4" height="4" />
                <rect x="434" y="140" width="4" height="4" />
                <rect x="442" y="140" width="4" height="4" />
                <rect x="434" y="148" width="4" height="4" />
                <rect x="442" y="148" width="4" height="4" />
                <rect x="434" y="156" width="4" height="4" />
                <rect x="442" y="156" width="4" height="4" />
                <rect x="434" y="164" width="4" height="4" />
                <rect x="442" y="164" width="4" height="4" />
                <rect x="434" y="172" width="4" height="4" />
                <rect x="442" y="172" width="4" height="4" />
            </g>
            
            <g id="building4" class="building">
                <rect x="458" y="150" width="12" height="35" />
            </g>
            <g id="windows4" class="building-window">
                <rect x="461" y="155" width="4" height="4" />
                <rect x="461" y="163" width="4" height="4" />
                <rect x="461" y="171" width="4" height="4" />
            </g>
            
            <g id="building5" class="building">
                <rect x="475" y="120" width="20" height="65" />
            </g>
            <g id="windows5" class="building-window">
                <rect x="479" y="125" width="4" height="4" />
                <rect x="487" y="125" width="4" height="4" />
                <rect x="479" y="133" width="4" height="4" />
                <rect x="487" y="133" width="4" height="4" />
                <rect x="479" y="141" width="4" height="4" />
                <rect x="487" y="141" width="4" height="4" />
                <rect x="479" y="149" width="4" height="4" />
                <rect x="487" y="149" width="4" height="4" />
                <rect x="479" y="157" width="4" height="4" />
                <rect x="487" y="157" width="4" height="4" />
                <rect x="479" y="165" width="4" height="4" />
                <rect x="487" y="165" width="4" height="4" />
                <rect x="479" y="173" width="4" height="4" />
                <rect x="487" y="173" width="4" height="4" />
            </g>
            
            <g id="building6" class="building">
                <rect x="350" y="150" width="15" height="35" />
            </g>
            <g id="windows6" class="building-window">
                <rect x="353" y="155" width="4" height="4" />
                <rect x="353" y="163" width="4" height="4" />
                <rect x="353" y="171" width="4" height="4" />
            </g>
            
            <g id="building7" class="building">
                <rect x="325" y="135" width="18" height="50" />
            </g>
            <g id="windows7" class="building-window">
                <rect x="329" y="140" width="4" height="4" />
                <rect x="335" y="140" width="4" height="4" />
                <rect x="329" y="148" width="4" height="4" />
                <rect x="335" y="148" width="4" height="4" />
                <rect x="329" y="156" width="4" height="4" />
                <rect x="335" y="156" width="4" height="4" />
                <rect x="329" y="164" width="4" height="4" />
                <rect x="335" y="164" width="4" height="4" />
                <rect x="329" y="172" width="4" height="4" />
                <rect x="335" y="172" width="4" height="4" />
            </g>
            
            <path id="circlePath" d="M180,125 A 70,70 0 1 1 179.99,125" fill="none" />
            <text class="circular-text" x="180" y="110" text-anchor="middle">GUNTUR PROPERTIES</text>
            
            <text class="tagline" x="300" y="300" text-anchor="middle" font-family="Arial, sans-serif">YOUR PROPERTY JOURNEY STARTS WITH ONE KEY</text>
        </svg>
    </div>
</body>
</html>
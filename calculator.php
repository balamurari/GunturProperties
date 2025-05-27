<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced EMI Calculator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .header .icon {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            padding: 1rem;
            border-radius: 50%;
            box-shadow: var(--shadow-lg);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .calculator-container {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 2rem;
            background: var(--bg-primary);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
        }

        .input-section {
            background: var(--bg-secondary);
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            height: fit-content;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-primary);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .currency-input {
            padding-left: 3rem;
        }

        .currency-symbol {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-weight: 600;
        }

        .range-group {
            margin-top: 1rem;
        }

        .range-slider {
            width: 100%;
            -webkit-appearance: none;
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) 50%, #ddd 50%, #ddd 100%);
            outline: none;
            transition: all 0.3s ease;
        }

        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .range-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .range-info {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .results-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .results-overview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .result-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .result-card h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .result-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .result-value.large {
            font-size: 2.2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .chart-container {
            background: var(--bg-primary);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .full-width-chart {
            grid-column: 1 / -1;
        }

        .full-width-chart .chart-wrapper {
            height: 400px;
        }

        .amortization-table {
            background: var(--bg-primary);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-top: 2rem;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-content {
            max-height: 400px;
            overflow-y: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 1rem;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
            position: sticky;
            top: 0;
        }

        .table td:first-child, .table th:first-child {
            text-align: center;
        }

        .table tbody tr:hover {
            background: var(--bg-secondary);
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-card h4 {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .comparison-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid #bae6fd;
            margin-top: 2rem;
        }

        .comparison-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .comparison-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .comparison-card h5 {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .comparison-card .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .comparison-card .savings {
            font-size: 0.85rem;
            color: var(--success-color);
            font-weight: 600;
        }

        @media (max-width: 1200px) {
            .calculator-container {
                grid-template-columns: 1fr;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .results-overview {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem 0.5rem;
            }

            .calculator-container {
                padding: 1.5rem;
            }

            .summary-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .comparison-grid {
                grid-template-columns: 1fr;
            }
        }

        .pulse {
            animation: pulse 0.3s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    </style>
</head>
<body>
                <?php include 'header.php';?>

    <div class="container">
        <header class="header">
            <h1>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
                Advanced EMI Calculator
            </h1>
            <p>Complete loan analysis with interactive visualizations and detailed insights</p>
        </header>

        <div class="calculator-container">
            <!-- Input Section -->
            <div class="input-section">
                <h2 class="section-title">
                    <i class="fas fa-sliders-h"></i>
                    Loan Parameters
                </h2>

                <form id="emi-form">
                    <div class="form-group">
                        <label for="loan-amount">Loan Amount</label>
                        <div class="input-wrapper">
                            <span class="currency-symbol">₹</span>
                            <input type="number" id="loan-amount" class="form-control currency-input" 
                                   value="2500000" min="100000" max="100000000" step="100000" required>
                        </div>
                        <div class="range-group">
                            <input type="range" id="loan-amount-range" class="range-slider" 
                                   min="100000" max="10000000" step="100000" value="2500000">
                            <div class="range-info">
                                <span>₹1L</span>
                                <span>₹1Cr</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="interest-rate">Annual Interest Rate (%)</label>
                        <input type="number" id="interest-rate" class="form-control" 
                               value="8.5" min="1" max="30" step="0.1" required>
                        <div class="range-group">
                            <input type="range" id="interest-rate-range" class="range-slider" 
                                   min="1" max="20" step="0.1" value="8.5">
                            <div class="range-info">
                                <span>1%</span>
                                <span>20%</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loan-tenure">Loan Tenure (Years)</label>
                        <input type="number" id="loan-tenure" class="form-control" 
                               value="20" min="1" max="30" step="1" required>
                        <div class="range-group">
                            <input type="range" id="loan-tenure-range" class="range-slider" 
                                   min="1" max="30" step="1" value="20">
                            <div class="range-info">
                                <span>1 Year</span>
                                <span>30 Years</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <div class="results-section">
                <div class="results-overview">
                    <div class="result-card">
                        <h3>Monthly EMI</h3>
                        <div class="result-value large" id="monthly-emi">₹21,545</div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Total Interest</h3>
                        <div class="result-value" id="total-interest">₹26,70,800</div>
                    </div>
                    
                    <div class="result-card">
                        <h3>Total Amount</h3>
                        <div class="result-value" id="total-amount">₹51,70,800</div>
                    </div>
                </div>

                <div class="charts-section">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            Principal vs Interest
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="pie-chart"></canvas>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fas fa-chart-bar"></i>
                            Yearly Payment Breakdown
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="bar-chart"></canvas>
                        </div>
                    </div>

                    <div class="chart-container full-width-chart">
                        <div class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            Outstanding Balance Over Time
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="line-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Section -->
        <div class="comparison-section">
            <div class="comparison-title">💡 Prepayment Impact Analysis</div>
            <div class="comparison-grid">
                <div class="comparison-card">
                    <h5>Regular Payment</h5>
                    <div class="amount" id="regular-total">₹51,70,800</div>
                    <div class="savings">Base scenario</div>
                </div>
                <div class="comparison-card">
                    <h5>10% Prepayment</h5>
                    <div class="amount" id="prepay-10-total">₹46,03,566</div>
                    <div class="savings" id="prepay-10-savings">Save ₹5,67,234</div>
                </div>
                <div class="comparison-card">
                    <h5>20% Prepayment</h5>
                    <div class="amount" id="prepay-20-total">₹42,15,320</div>
                    <div class="savings" id="prepay-20-savings">Save ₹9,55,480</div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <h4>Interest Ratio</h4>
                <div class="value" id="interest-ratio">1.07:1</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4>Total Payments</h4>
                <div class="value" id="total-payments">240</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
                <h4>Avg Monthly Interest</h4>
                <div class="value" id="avg-interest">₹11,128</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4>Break-even Point</h4>
                <div class="value" id="breakeven">Month 162</div>
            </div>
        </div>

        <!-- Amortization Table -->
        <div class="amortization-table">
            <div class="table-header">
                <i class="fas fa-table"></i>
                Payment Schedule (First 12 Months)
            </div>
            <div class="table-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>EMI</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="amortization-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        class AdvancedEMICalculator {
            constructor() {
                this.charts = {};
                this.calculationData = null;
                this.init();
            }

            init() {
                this.setupEventListeners();
                this.calculateEMI();
                this.initCharts();
            }

            setupEventListeners() {
                const inputs = ['loan-amount', 'interest-rate', 'loan-tenure'];
                inputs.forEach(id => {
                    const input = document.getElementById(id);
                    const range = document.getElementById(id + '-range');
                    
                    input.addEventListener('input', (e) => {
                        range.value = e.target.value;
                        this.updateRangeBackground(range);
                        this.debounceCalculation();
                    });
                    
                    range.addEventListener('input', (e) => {
                        input.value = e.target.value;
                        this.updateRangeBackground(e.target);
                        this.debounceCalculation();
                    });

                    // Initialize range backgrounds
                    this.updateRangeBackground(range);
                });
            }

            updateRangeBackground(range) {
                const min = parseFloat(range.min);
                const max = parseFloat(range.max);
                const value = parseFloat(range.value);
                const percentage = ((value - min) / (max - min)) * 100;
                range.style.background = `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) ${percentage}%, #ddd ${percentage}%, #ddd 100%)`;
            }

            debounceCalculation() {
                clearTimeout(this.calculationTimeout);
                this.calculationTimeout = setTimeout(() => {
                    this.calculateEMI();
                    this.updateCharts();
                    this.generateAmortizationSchedule();
                    this.updateComparisons();
                }, 300);
            }

            calculateEMI() {
                const loanAmount = parseFloat(document.getElementById('loan-amount').value);
                const interestRate = parseFloat(document.getElementById('interest-rate').value);
                const loanTenure = parseFloat(document.getElementById('loan-tenure').value);

                const monthlyRate = interestRate / (12 * 100);
                const numberOfPayments = loanTenure * 12;

                let monthlyEMI = 0;
                if (monthlyRate > 0) {
                    monthlyEMI = loanAmount * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments) / 
                               (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                } else {
                    monthlyEMI = loanAmount / numberOfPayments;
                }

                const totalAmount = monthlyEMI * numberOfPayments;
                const totalInterest = totalAmount - loanAmount;

                this.calculationData = {
                    loanAmount,
                    interestRate,
                    loanTenure,
                    monthlyEMI,
                    totalInterest,
                    totalAmount,
                    numberOfPayments,
                    monthlyRate
                };

                this.updateResults();
                this.updateSummaryStats();
            }

            updateResults() {
                const { monthlyEMI, totalInterest, totalAmount } = this.calculationData;
                
                document.getElementById('monthly-emi').textContent = this.formatCurrency(monthlyEMI);
                document.getElementById('total-interest').textContent = this.formatCurrency(totalInterest);
                document.getElementById('total-amount').textContent = this.formatCurrency(totalAmount);

                // Add animation
                document.querySelectorAll('.result-value').forEach(el => {
                    el.classList.add('pulse');
                    setTimeout(() => el.classList.remove('pulse'), 300);
                });
            }

            updateSummaryStats() {
                const { loanAmount, totalInterest, numberOfPayments, totalAmount } = this.calculationData;
                
                const interestRatio = (totalInterest / loanAmount).toFixed(2);
                const avgInterest = totalInterest / numberOfPayments;
                const breakeven = Math.floor(numberOfPayments * 0.675); // Approximate breakeven point
                
                document.getElementById('interest-ratio').textContent = `${interestRatio}:1`;
                document.getElementById('total-payments').textContent = numberOfPayments;
                document.getElementById('avg-interest').textContent = this.formatCurrency(avgInterest);
                document.getElementById('breakeven').textContent = `Month ${breakeven}`;
            }

            initCharts() {
                this.initPieChart();
                this.initBarChart();
                this.initLineChart();
            }

            initPieChart() {
                const ctx = document.getElementById('pie-chart').getContext('2d');
                this.charts.pie = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Principal Amount', 'Total Interest'],
                        datasets: [{
                            data: [2500000, 2670800],
                            backgroundColor: ['#3b82f6', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const value = this.formatCurrency(context.parsed);
                                        const percentage = ((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                                        return `${context.label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }

            initBarChart() {
                const ctx = document.getElementById('bar-chart').getContext('2d');
                this.charts.bar = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Principal',
                            data: [],
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }, {
                            label: 'Interest',
                            data: [],
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true },
                            y: { 
                                stacked: true,
                                ticks: {
                                    callback: (value) => this.formatCurrency(value)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        return `${context.dataset.label}: ${this.formatCurrency(context.parsed.y)}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            initLineChart() {
                const ctx = document.getElementById('line-chart').getContext('2d');
                this.charts.line = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Outstanding Balance',
                            data: [],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => this.formatCurrency(value)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        return `Balance: ${this.formatCurrency(context.parsed.y)}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            updateCharts() {
                if (!this.calculationData) return;

                this.updatePieChart();
                this.updateBarChart();
                this.updateLineChart();
            }

            updatePieChart() {
                const { loanAmount, totalInterest } = this.calculationData;
                this.charts.pie.data.datasets[0].data = [loanAmount, totalInterest];
                this.charts.pie.update('active');
            }

            updateBarChart() {
                const { loanAmount, monthlyEMI, monthlyRate, numberOfPayments } = this.calculationData;
                
                const yearlyData = [];
                let balance = loanAmount;
                
                for (let year = 1; year <= Math.min(10, Math.ceil(numberOfPayments / 12)); year++) {
                    let yearlyPrincipal = 0;
                    let yearlyInterest = 0;
                    
                    for (let month = 1; month <= 12 && balance > 0; month++) {
                        const interestPayment = balance * monthlyRate;
                        const principalPayment = Math.min(monthlyEMI - interestPayment, balance);
                        
                        yearlyPrincipal += principalPayment;
                        yearlyInterest += interestPayment;
                        balance -= principalPayment;
                    }
                    
                    yearlyData.push({
                        year: `Year ${year}`,
                        principal: yearlyPrincipal,
                        interest: yearlyInterest
                    });
                }
                
                this.charts.bar.data.labels = yearlyData.map(d => d.year);
                this.charts.bar.data.datasets[0].data = yearlyData.map(d => d.principal);
                this.charts.bar.data.datasets[1].data = yearlyData.map(d => d.interest);
                this.charts.bar.update('active');
            }

            updateLineChart() {
                const { loanAmount, monthlyEMI, monthlyRate, numberOfPayments } = this.calculationData;
                
                const balanceData = [];
                let balance = loanAmount;
                
                balanceData.push(balance);
                
                for (let month = 1; month <= Math.min(numberOfPayments, 240); month++) {
                    const interestPayment = balance * monthlyRate;
                    const principalPayment = Math.min(monthlyEMI - interestPayment, balance);
                    balance -= principalPayment;
                    
                    if (month % 6 === 0) { // Show every 6 months
                        balanceData.push(Math.max(0, balance));
                    }
                }
                
                const labels = balanceData.map((_, index) => `Month ${index * 6}`);
                
                this.charts.line.data.labels = labels;
                this.charts.line.data.datasets[0].data = balanceData;
                this.charts.line.update('active');
            }

            generateAmortizationSchedule() {
                if (!this.calculationData) return;

                const { loanAmount, monthlyEMI, monthlyRate } = this.calculationData;
                const tbody = document.getElementById('amortization-body');
                tbody.innerHTML = '';

                let balance = loanAmount;

                for (let month = 1; month <= 12; month++) {
                    const interestPayment = balance * monthlyRate;
                    const principalPayment = monthlyEMI - interestPayment;
                    balance -= principalPayment;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${month}</td>
                        <td>${this.formatCurrency(monthlyEMI)}</td>
                        <td>${this.formatCurrency(principalPayment)}</td>
                        <td>${this.formatCurrency(interestPayment)}</td>
                        <td>${this.formatCurrency(Math.max(0, balance))}</td>
                    `;
                    tbody.appendChild(row);
                }
            }

            updateComparisons() {
                if (!this.calculationData) return;

                const { totalAmount } = this.calculationData;
                
                document.getElementById('regular-total').textContent = this.formatCurrency(totalAmount);
                
                const prepay10 = this.calculatePrepaymentScenario(0.1);
                const prepay20 = this.calculatePrepaymentScenario(0.2);
                
                document.getElementById('prepay-10-total').textContent = this.formatCurrency(prepay10.totalAmount);
                document.getElementById('prepay-10-savings').textContent = `Save ${this.formatCurrency(totalAmount - prepay10.totalAmount)}`;
                
                document.getElementById('prepay-20-total').textContent = this.formatCurrency(prepay20.totalAmount);
                document.getElementById('prepay-20-savings').textContent = `Save ${this.formatCurrency(totalAmount - prepay20.totalAmount)}`;
            }

            calculatePrepaymentScenario(prepaymentPercent) {
                const { loanAmount, monthlyEMI, monthlyRate } = this.calculationData;
                const prepaymentAmount = monthlyEMI * prepaymentPercent;
                const newEMI = monthlyEMI + prepaymentAmount;
                
                let balance = loanAmount;
                let totalPaid = 0;
                let months = 0;
                
                while (balance > 0 && months < 360) {
                    const interestPayment = balance * monthlyRate;
                    const principalPayment = Math.min(newEMI - interestPayment, balance);
                    
                    totalPaid += interestPayment + principalPayment;
                    balance -= principalPayment;
                    months++;
                }
                
                return {
                    totalAmount: totalPaid,
                    months: months
                };
            }

            formatCurrency(amount) {
                if (amount >= 10000000) {
                    return `₹${(amount / 10000000).toFixed(2)}Cr`;
                } else if (amount >= 100000) {
                    return `₹${(amount / 100000).toFixed(2)}L`;
                } else if (amount >= 1000) {
                    return `₹${(amount / 1000).toFixed(0)}K`;
                } else {
                    return `₹${Math.round(amount).toLocaleString('en-IN')}`;
                }
            }
        }

        // Initialize calculator
        document.addEventListener('DOMContentLoaded', () => {
            new AdvancedEMICalculator();
        });
    </script>
                <?php include 'footer.php';?>

</body>
</html>
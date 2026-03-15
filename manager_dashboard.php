<?php
// manager_dashboard_simulation.php
session_start();
require_once "config.php";

if($_SESSION['role'] != 'manager'){
    header("Location: login.php");
}

// Set timezone for Philippines
date_default_timezone_set('Asia/Manila');

// Simulation data - Manager info
$manager_name = "Maria Santos";
$current_datetime = date("Y-m-d H:i:s");

// Dummy staff assignments with current timestamps
$staff_assignments = [
    [
        'id' => 1,
        'name' => 'Pedro Reyes',
        'pond' => 'A-1',
        'last_update' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'status' => 'active',
        'avatar' => 'PR'
    ],
    [
        'id' => 2,
        'name' => 'Ana Lopez',
        'pond' => 'B-2',
        'last_update' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'status' => 'active',
        'avatar' => 'AL'
    ]
];

// Dummy ponds data with current timestamps
$ponds_data = [
    'A-1' => [
        'organic_level' => 65,
        'temperature' => 28.5,
        'ph' => 7.2,
        'status' => 'warning',
        'staff' => 'Pedro Reyes',
        'coordinates' => [8.3678, 124.8685],
        'last_reading' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'trend' => 'rising'
    ],
    'B-2' => [
        'organic_level' => 82,
        'temperature' => 31.2,
        'ph' => 8.1,
        'status' => 'critical',
        'staff' => 'Ana Lopez',
        'coordinates' => [8.3712, 124.8712],
        'last_reading' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'trend' => 'stable'
    ]
];

// Dummy alerts with timestamps
$alerts = [
    [
        'pond' => 'B-2',
        'type' => 'critical',
        'message' => 'High organic level (82%) detected. Temperature above threshold (31.2°C).',
        'time' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'severity' => 'high'
    ],
    [
        'pond' => 'A-1',
        'type' => 'warning',
        'message' => 'Organic level approaching threshold (65%). Monitor closely.',
        'time' => date('Y-m-d H:i:s', strtotime('-12 minutes')),
        'severity' => 'medium'
    ]
];

// Handle AJAX requests for simulation
if(isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if($_POST['action'] == 'get_chart_data') {
        $period = $_POST['period'] ?? 'daily';
        echo json_encode(generateSimulationChartData($period));
        exit;
    }
    
    if($_POST['action'] == 'notify_admin') {
        echo json_encode([
            'success' => true, 
            'message' => 'Admin notified successfully',
            'notification_id' => rand(1000, 9999),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    if($_POST['action'] == 'get_live_updates') {
        echo json_encode([
            'ponds' => $ponds_data,
            'staff' => $staff_assignments,
            'alerts' => $alerts,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    if($_POST['action'] == 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        exit;
    }
}

function generateSimulationChartData($period) {
    $data = [];
    $points = $period == 'daily' ? 24 : ($period == 'weekly' ? 7 : 30);
    $now = time();
    
    for($i = 0; $i < $points; $i++) {
        if($period == 'daily') {
            $hour = ($i % 24);
            $data['labels'][] = $hour . ':00';
        } elseif($period == 'weekly') {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $data['labels'][] = $days[$i % 7];
        } else {
            $data['labels'][] = 'Day ' . ($i + 1);
        }
        
        // Generate realistic looking data with patterns
        $time_factor = sin($i * 0.3) * 8;
        $random_factor = rand(-3, 3);
        
        $data['organic'][] = round(60 + $time_factor + $random_factor, 1);
        $data['temperature'][] = round(28 + $time_factor * 0.2 + rand(-1, 1), 1);
        $data['ph'][] = round(7.2 + sin($i * 0.2) * 0.4 + rand(-1, 1) / 10, 1);
    }
    
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pond Monitoring System - Manager Dashboard (Simulation)</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #142138 0%, #0d1729 100%);
            color: #ffffff;
            min-height: 100vh;
            position: relative;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0d1729;
        }

        ::-webkit-scrollbar-thumb {
            background: #2a3f5e;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3b82f6;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(13, 23, 41, 0.98);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            font-size: 1.8rem;
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            padding: 0.5rem;
            border-radius: 12px;
        }

        .system-title {
            font-weight: 600;
            font-size: 1.2rem;
            background: linear-gradient(45deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .simulation-badge {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            border: 1px solid rgba(59, 130, 246, 0.3);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-badge {
            background: linear-gradient(45deg, #2a3f5e, #1e2f47);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Main Container */
        .dashboard-container {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Current Time Bar */
        .time-bar {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50px;
            padding: 0.8rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .current-time {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .time-display {
            background: #1e2f47;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-family: monospace;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        /* Grid Layouts */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Card Styles */
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Pond Cards */
        .pond-card {
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .pond-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            transition: all 0.3s ease;
        }

        .pond-card[data-status="safe"]::before {
            background: #4ade80;
            box-shadow: 0 0 20px #4ade80;
        }

        .pond-card[data-status="warning"]::before {
            background: #fbbf24;
            box-shadow: 0 0 20px #fbbf24;
        }

        .pond-card[data-status="critical"]::before {
            background: #ef4444;
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0% { opacity: 1; box-shadow: 0 0 20px #ef4444; }
            50% { opacity: 0.7; box-shadow: 0 0 40px #ef4444; }
            100% { opacity: 1; box-shadow: 0 0 20px #ef4444; }
        }

        .pond-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.2rem 0;
        }

        .metric-item {
            text-align: center;
            padding: 1rem 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .metric-item:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: scale(1.05);
        }

        .metric-icon {
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .metric-icon.organic { color: #4ade80; }
        .metric-icon.temp { color: #fbbf24; }
        .metric-icon.ph { color: #a78bfa; }

        .metric-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 0.2rem;
        }

        .trend-indicator {
            font-size: 0.7rem;
            margin-left: 0.2rem;
        }

        .trend-indicator.rising { color: #ef4444; }
        .trend-indicator.falling { color: #4ade80; }
        .trend-indicator.stable { color: #fbbf24; }

        .timestamp {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.5rem;
        }

        /* Staff List */
        .staff-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .staff-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .staff-item:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateX(5px);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .staff-avatar {
            width: 45px;
            height: 45px;
            background: #2a3f5e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            border: 2px solid #3b82f6;
        }

        .staff-info {
            flex: 1;
            margin-left: 1rem;
        }

        .staff-name {
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .staff-details {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 0.3rem;
            display: flex;
            gap: 1rem;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse 2s infinite;
        }

        /* Map Container */
        #map {
            height: 400px;
            border-radius: 24px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        /* Report Buttons */
        .report-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .report-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .report-btn.active {
            background: #2a3f5e;
            border-color: #3b82f6;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        .report-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Chart Container */
        .chart-container {
            height: 250px;
            margin-top: 1rem;
            position: relative;
        }

        /* Alert Section */
        .alert-section {
            margin-top: 1rem;
        }

        .alert-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem;
            border-radius: 18px;
            margin-bottom: 0.8rem;
            animation: slideIn 0.5s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .alert-item:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-item.critical {
            background: rgba(239, 68, 68, 0.15);
            border-left: 4px solid #ef4444;
        }

        .alert-item.warning {
            background: rgba(251, 191, 36, 0.15);
            border-left: 4px solid #fbbf24;
        }

        .notify-btn {
            background: linear-gradient(45deg, #2a3f5e, #1e2f47);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .notify-btn:hover {
            background: linear-gradient(45deg, #344e73, #253a58);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            background: #1e2f47;
            color: white;
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Legend */
        .map-legend {
            display: flex;
            gap: 1rem;
            background: rgba(13, 23, 41, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
            
            .time-bar {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-area">
            <i class="fas fa-water logo"></i>
            <span class="system-title">PondMonitor Pro</span>
            <span class="simulation-badge">
                <i class="fas fa-sim-card"></i> SIMULATION MODE
            </span>
        </div>
        <div class="user-info">
            <div class="user-badge">
                <div class="user-avatar">MS</div>
                <div>
                    <div style="font-size: 0.8rem; opacity: 0.7;">Manager</div>
                    <div style="font-weight: 600;"><?php echo $manager_name; ?></div>
                </div>
            </div>
            <button class="logout-btn" onclick="simulateLogout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Current Time Bar -->
        <div class="time-bar">
            <div class="current-time">
                <i class="fas fa-clock" style="color: #3b82f6;"></i>
                <span>System Time:</span>
                <span class="time-display" id="currentTime"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="live-indicator">
                <span class="live-dot"></span>
                <span>Live Data Streaming</span>
                <span style="font-size: 0.8rem; opacity: 0.5;">(Simulation)</span>
            </div>
        </div>

        <!-- Staff & Pond Assignments Row -->
        <div class="grid-2">
            <!-- Staff Assignments Card -->
            <div class="card">
                <div class="card-header">
                    <span><i class="fas fa-users" style="color: #3b82f6;"></i> Staff Assignments</span>
                    <span class="tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltiptext">Click staff to highlight pond on map</span>
                    </span>
                </div>
                <div class="staff-list">
                    <?php foreach($staff_assignments as $staff): ?>
                    <div class="staff-item" onclick="highlightStaffPond('<?php echo $staff['pond']; ?>', '<?php echo $staff['name']; ?>')">
                        <div style="display: flex; align-items: center; width: 100%;">
                            <div class="staff-avatar"><?php echo $staff['avatar']; ?></div>
                            <div class="staff-info">
                                <div class="staff-name">
                                    <?php echo $staff['name']; ?>
                                    <span style="font-size: 0.7rem; background: rgba(74, 222, 128, 0.2); color: #4ade80; padding: 0.2rem 0.6rem; border-radius: 50px;">
                                        <i class="fas fa-circle"></i> Active
                                    </span>
                                </div>
                                <div class="staff-details">
                                    <span><i class="fas fa-map-marker-alt"></i> Pond <?php echo $staff['pond']; ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($staff['last_update'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ponds Overview Cards -->
            <div class="grid-2" style="margin: 0; gap: 1rem;">
                <?php foreach($ponds_data as $pond => $data): ?>
                <div class="card pond-card" data-status="<?php echo $data['status']; ?>" onclick="highlightPond('<?php echo $pond; ?>')">
                    <div class="card-header">
                        <span class="pond-title">
                            <i class="fas fa-map-marker-alt" style="color: <?php 
                                echo $data['status'] == 'safe' ? '#4ade80' : 
                                    ($data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                            ?>;"></i>
                            Pond <?php echo $pond; ?>
                        </span>
                        <span class="status-badge" style="background: <?php 
                            echo $data['status'] == 'safe' ? 'rgba(74,222,128,0.15)' : 
                                ($data['status'] == 'warning' ? 'rgba(251,191,36,0.15)' : 'rgba(239,68,68,0.15)'); 
                        ?>; color: <?php 
                            echo $data['status'] == 'safe' ? '#4ade80' : 
                                ($data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                        ?>;">
                            <i class="fas fa-circle"></i> <?php echo ucfirst($data['status']); ?>
                        </span>
                    </div>
                    
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-icon organic"><i class="fas fa-seedling"></i></div>
                            <div class="metric-label">Organic</div>
                            <div class="metric-value">
                                <?php echo $data['organic_level']; ?>%
                                <span class="trend-indicator <?php echo $data['trend']; ?>">
                                    <i class="fas fa-arrow-<?php echo $data['trend'] == 'rising' ? 'up' : ($data['trend'] == 'falling' ? 'down' : 'right'); ?>"></i>
                                </span>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon temp"><i class="fas fa-thermometer-half"></i></div>
                            <div class="metric-label">Temp</div>
                            <div class="metric-value"><?php echo $data['temperature']; ?>°C</div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon ph"><i class="fas fa-flask"></i></div>
                            <div class="metric-label">pH</div>
                            <div class="metric-value"><?php echo $data['ph']; ?></div>
                        </div>
                    </div>
                    
                    <div class="timestamp">
                        <i class="far fa-clock"></i>
                        Last reading: <?php echo date('h:i:s A', strtotime($data['last_reading'])); ?>
                        <span style="margin-left: auto; font-size: 0.7rem;">
                            <i class="fas fa-user"></i> <?php echo $data['staff']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Live Map -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <span><i class="fas fa-map-marked-alt" style="color: #3b82f6;"></i> Live Pond Map - Manolo Fortich, Bukidnon</span>
                <div class="map-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #4ade80;"></div>
                        <span>Safe</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #fbbf24;"></div>
                        <span>Warning</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #ef4444;"></div>
                        <span>Critical</span>
                    </div>
                </div>
            </div>
            <div id="map"></div>
        </div>

        <!-- Live Metrics and Staff Monitoring -->
        <div class="grid-2">
            <!-- Live Metrics Trends -->
            <div class="card">
                <div class="card-header">
                    <span><i class="fas fa-chart-line" style="color: #3b82f6;"></i> Live Metrics Trends (Last 24 Hours)</span>
                    <div class="report-buttons">
                        <button class="report-btn active" onclick="updateChartSimulation('daily', this)">Daily</button>
                        <button class="report-btn" onclick="updateChartSimulation('weekly', this)">Weekly</button>
                        <button class="report-btn" onclick="updateChartSimulation('monthly', this)">Monthly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="metricsChart"></canvas>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.8rem; color: rgba(255,255,255,0.5);">
                    <span><i class="fas fa-chart-line"></i> Real-time simulation data</span>
                    <span>Updated: <span id="chartTimestamp"><?php echo date('h:i:s A'); ?></span></span>
                </div>
            </div>

            <!-- Staff Monitoring Panel -->
            <div class="card">
                <div class="card-header">
                    <span><i class="fas fa-clipboard-list" style="color: #3b82f6;"></i> Staff Monitoring Panel</span>
                    <span class="simulation-badge"><i class="fas fa-sync-alt"></i> Live Updates</span>
                </div>
                <div class="staff-list">
                    <?php foreach($staff_assignments as $staff): 
                        $pond_data = $ponds_data[$staff['pond']];
                    ?>
                    <div class="staff-item" onclick="highlightStaffPond('<?php echo $staff['pond']; ?>', '<?php echo $staff['name']; ?>')">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                                <h4 style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-user-circle" style="color: #3b82f6;"></i> 
                                    <?php echo $staff['name']; ?>
                                </h4>
                                <span class="status-badge" style="background: <?php 
                                    echo $pond_data['status'] == 'safe' ? 'rgba(74,222,128,0.15)' : 
                                        ($pond_data['status'] == 'warning' ? 'rgba(251,191,36,0.15)' : 'rgba(239,68,68,0.15)'); 
                                ?>; color: <?php 
                                    echo $pond_data['status'] == 'safe' ? '#4ade80' : 
                                        ($pond_data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                                ?>;">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst($pond_data['status']); ?>
                                </span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.8rem; background: rgba(255,255,255,0.02); padding: 0.8rem; border-radius: 12px;">
                                <div>
                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Pond</div>
                                    <div style="font-weight: 600;"><?php echo $staff['pond']; ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Organic</div>
                                    <div style="font-weight: 600; color: #4ade80;"><?php echo $pond_data['organic_level']; ?>%</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Temp</div>
                                    <div style="font-weight: 600; color: #fbbf24;"><?php echo $pond_data['temperature']; ?>°C</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">pH</div>
                                    <div style="font-weight: 600; color: #a78bfa;"><?php echo $pond_data['ph']; ?></div>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 0.8rem; font-size: 0.75rem; color: rgba(255,255,255,0.4);">
                                <span><i class="far fa-clock"></i> Last update: <?php echo date('h:i:s A', strtotime($staff['last_update'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> Pond <?php echo $staff['pond']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <span><i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Active Alerts</span>
                <button class="notify-btn" onclick="notifyAdminSimulation()">
                    <i class="fas fa-bell"></i>
                    Notify Admin
                </button>
            </div>
            <div class="alert-section">
                <?php foreach($alerts as $alert): ?>
                <div class="alert-item <?php echo $alert['type']; ?>" onclick="highlightPond('<?php echo $alert['pond']; ?>')">
                    <i class="fas fa-<?php echo $alert['type'] == 'critical' ? 'exclamation-circle' : 'exclamation-triangle'; ?>" 
                       style="font-size: 1.3rem; color: <?php echo $alert['type'] == 'critical' ? '#ef4444' : '#fbbf24'; ?>;"></i>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                            <strong>Pond <?php echo $alert['pond']; ?></strong>
                            <span style="background: <?php echo $alert['type'] == 'critical' ? 'rgba(239,68,68,0.2)' : 'rgba(251,191,36,0.2)'; ?>; 
                                       color: <?php echo $alert['type'] == 'critical' ? '#ef4444' : '#fbbf24'; ?>;
                                       padding: 0.2rem 0.6rem; border-radius: 50px; font-size: 0.7rem;">
                                <?php echo strtoupper($alert['type']); ?>
                            </span>
                        </div>
                        <p style="font-size: 0.9rem; color: rgba(255,255,255,0.8); margin-bottom: 0.3rem;">
                            <?php echo $alert['message']; ?>
                        </p>
                        <span style="font-size: 0.7rem; color: rgba(255,255,255,0.4);">
                            <i class="far fa-clock"></i> <?php echo date('h:i:s A', strtotime($alert['time'])); ?>
                        </span>
                    </div>
                    <span class="tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltiptext">Click to view on map</span>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Simulation Status Bar -->
        <div style="margin-top: 1.5rem; padding: 1rem; text-align: center; background: rgba(255,255,255,0.02); border-radius: 50px; font-size: 0.85rem; color: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.05);">
            <i class="fas fa-sim-card"></i> SIMULATION MODE - All data displayed are for demonstration purposes only • 
            <span id="footerTimestamp"><?php echo date('Y-m-d H:i:s'); ?></span>
        </div>
    </div>

    <script>
        // Initialize map and markers
        let map, markers = {}, chart;
        let updateInterval;

        // Custom marker icons with blinking animation
        function createMarkerIcon(status) {
            const colors = {
                safe: '#4ade80',
                warning: '#fbbf24',
                critical: '#ef4444'
            };
            
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background: ${colors[status]};
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 0 20px ${colors[status]};
                    animation: ${status === 'critical' ? 'blinkMarker 1s infinite' : 'none'};
                "></div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map with dark theme
            map = L.map('map').setView([8.3695, 124.8698], 15);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Add markers for each pond
            <?php foreach($ponds_data as $pond => $data): ?>
                markers['<?php echo $pond; ?>'] = L.marker(
                    [<?php echo $data['coordinates'][0]; ?>, <?php echo $data['coordinates'][1]; ?>],
                    { 
                        icon: createMarkerIcon('<?php echo $data['status']; ?>'),
                        riseOnHover: true
                    }
                ).addTo(map)
                .bindPopup(`
                    <div style="color: #142138; padding: 12px; max-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                            <i class="fas fa-map-marker-alt" style="color: <?php 
                                echo $data['status'] == 'safe' ? '#4ade80' : 
                                    ($data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                            ?>; font-size: 1.2rem;"></i>
                            <h3 style="margin: 0; font-size: 1.1rem;">Pond <?php echo $pond; ?></h3>
                            <span style="background: <?php 
                                echo $data['status'] == 'safe' ? '#4ade80' : 
                                    ($data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                            ?>20; color: <?php 
                                echo $data['status'] == 'safe' ? '#4ade80' : 
                                    ($data['status'] == 'warning' ? '#fbbf24' : '#ef4444'); 
                            ?>; padding: 2px 8px; border-radius: 50px; font-size: 0.7rem;">
                                <?php echo ucfirst($data['status']); ?>
                            </span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px;">
                            <div style="text-align: center;">
                                <i class="fas fa-seedling" style="color: #4ade80;"></i>
                                <div style="font-weight: 600;"><?php echo $data['organic_level']; ?>%</div>
                                <div style="font-size: 0.7rem; color: #666;">Organic</div>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-thermometer-half" style="color: #fbbf24;"></i>
                                <div style="font-weight: 600;"><?php echo $data['temperature']; ?>°C</div>
                                <div style="font-size: 0.7rem; color: #666;">Temp</div>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-flask" style="color: #a78bfa;"></i>
                                <div style="font-weight: 600;"><?php echo $data['ph']; ?></div>
                                <div style="font-size: 0.7rem; color: #666;">pH</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #eee;">
                            <p style="margin: 3px 0;"><i class="fas fa-user"></i> <strong><?php echo $data['staff']; ?></strong></p>
                            <p style="margin: 3px 0; font-size: 0.8rem; color: #666;">
                                <i class="far fa-clock"></i> <?php echo date('h:i:s A', strtotime($data['last_reading'])); ?>
                            </p>
                        </div>
                    </div>
                `);
            <?php endforeach; ?>

            // Initialize chart with simulation data
            updateChartSimulation('daily', document.querySelector('.report-btn.active'));

            // Start live time updates
            startLiveUpdates();
        });

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes blinkMarker {
                0% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.2); }
                100% { opacity: 1; transform: scale(1); }
            }
            
            .leaflet-popup-content {
                font-family: 'Inter', sans-serif;
            }
            
            .leaflet-container {
                font-family: 'Inter', sans-serif;
            }
        `;
        document.head.appendChild(style);

        // Start live updates simulation
        function startLiveUpdates() {
            // Update current time every second
            setInterval(() => {
                const now = new Date();
                document.getElementById('currentTime').textContent = 
                    now.toISOString().slice(0, 10) + ' ' + 
                    now.toTimeString().slice(0, 8);
                document.getElementById('footerTimestamp').textContent = 
                    now.toISOString().slice(0, 10) + ' ' + 
                    now.toTimeString().slice(0, 8);
            }, 1000);

            // Simulate live data updates every 30 seconds
            setInterval(() => {
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=get_live_updates'
                })
                .then(response => response.json())
                .then(data => {
                    // Update timestamps
                    document.querySelectorAll('.timestamp').forEach((el, index) => {
                        if (index < 2) {
                            const pond = index === 0 ? 'A-1' : 'B-2';
                            el.innerHTML = `<i class="far fa-clock"></i> Last reading: ${new Date().toLocaleTimeString()}`;
                        }
                    });
                    
                    showSimulationFeedback('New data received');
                });
            }, 30000);
        }

        // Chart update simulation
        function updateChartSimulation(period, btn) {
            // Update active button
            document.querySelectorAll('.report-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Show loading state
            const chartContainer = document.querySelector('.chart-container');
            chartContainer.style.opacity = '0.5';

            // Simulate API call
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_chart_data&period=' + period
            })
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    const ctx = document.getElementById('metricsChart').getContext('2d');
                    
                    if (chart) chart.destroy();
                    
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Organic Level',
                                    data: data.organic,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 2
                                },
                                {
                                    label: 'Temperature',
                                    data: data.temperature,
                                    borderColor: '#fbbf24',
                                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 2
                                },
                                {
                                    label: 'pH Level',
                                    data: data.ph,
                                    borderColor: '#4ade80',
                                    backgroundColor: 'rgba(74, 222, 128, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            },
                            plugins: {
                                legend: {
                                    labels: { 
                                        color: '#ffffff',
                                        font: { size: 11, weight: '500' }
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: '#1e2f47',
                                    titleColor: '#ffffff',
                                    bodyColor: 'rgba(255,255,255,0.8)',
                                    borderColor: 'rgba(255,255,255,0.1)',
                                    borderWidth: 1,
                                    padding: 12
                                }
                            },
                            scales: {
                                y: { 
                                    grid: { color: 'rgba(255,255,255,0.1)' }, 
                                    ticks: { color: '#ffffff', font: { size: 10 } }
                                },
                                x: { 
                                    grid: { color: 'rgba(255,255,255,0.1)' }, 
                                    ticks: { color: '#ffffff', font: { size: 10 } }
                                }
                            }
                        }
                    });
                    
                    chartContainer.style.opacity = '1';
                    document.getElementById('chartTimestamp').textContent = new Date().toLocaleTimeString();
                }, 600);
            });
        }

        // Highlight pond on map
        function highlightPond(pondId) {
            if (markers[pondId]) {
                map.setView(markers[pondId].getLatLng(), 18);
                markers[pondId].openPopup();
                
                // Highlight card with animation
                document.querySelectorAll('.pond-card').forEach(card => {
                    card.style.transform = 'scale(1)';
                    card.style.borderColor = '';
                    card.style.boxShadow = '';
                });
                
                // Find and highlight the corresponding card
                document.querySelectorAll('.pond-card').forEach((card, index) => {
                    if ((pondId === 'A-1' && index === 0) || (pondId === 'B-2' && index === 1)) {
                        card.style.transform = 'scale(1.02)';
                        card.style.borderColor = '#3b82f6';
                        card.style.boxShadow = '0 0 30px rgba(59, 130, 246, 0.3)';
                        
                        setTimeout(() => {
                            card.style.transform = 'scale(1)';
                            card.style.borderColor = '';
                            card.style.boxShadow = '';
                        }, 2000);
                    }
                });

                showSimulationFeedback(`Pond ${pondId} highlighted on map`);
            }
        }

        // Highlight staff's assigned pond
        function highlightStaffPond(pondId, staffName) {
            highlightPond(pondId);
            
            // Highlight staff item
            document.querySelectorAll('.staff-item').forEach(item => {
                item.style.background = '';
                item.style.borderColor = '';
            });
            
            // Find and highlight the clicked staff item
            event.currentTarget.style.background = 'rgba(59, 130, 246, 0.15)';
            event.currentTarget.style.borderColor = '#3b82f6';
            
            setTimeout(() => {
                event.currentTarget.style.background = '';
                event.currentTarget.style.borderColor = '';
            }, 2000);

            showSimulationFeedback(`Showing ${staffName}'s assigned pond (Pond ${pondId})`);
        }

        // Notify admin simulation
        function notifyAdminSimulation() {
            const btn = document.querySelector('.notify-btn');
            const originalHtml = btn.innerHTML;
            
            // Show loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Notifying Admin...';
            btn.disabled = true;

            // Simulate API call
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=notify_admin'
            })
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    if (data.success) {
                        // Success animation
                        btn.innerHTML = '<i class="fas fa-check"></i> Admin Notified!';
                        btn.style.background = '#4ade80';
                        btn.style.color = '#142138';
                        
                        // Show notification simulation
                        showNotification(`Admin notified successfully at ${data.timestamp}`, 'success');
                        
                        setTimeout(() => {
                            btn.innerHTML = originalHtml;
                            btn.style.background = '';
                            btn.style.color = '';
                            btn.disabled = false;
                        }, 2000);
                    }
                }, 1500);
            });
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: ${type === 'success' ? '#4ade80' : '#3b82f6'};
                color: #142138;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.3);
                z-index: 9999;
                animation: slideInRight 0.3s ease;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.8rem;
                border: 1px solid rgba(255,255,255,0.2);
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}" style="font-size: 1.2rem;"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Show simulation feedback
        function showSimulationFeedback(message) {
            const feedback = document.createElement('div');
            feedback.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 20px;
                background: #1e2f47;
                color: white;
                padding: 0.8rem 1.2rem;
                border-radius: 50px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.3);
                z-index: 9999;
                animation: fadeInUp 0.3s ease;
                font-size: 0.9rem;
                border: 1px solid rgba(255,255,255,0.1);
                display: flex;
                align-items: center;
                gap: 0.5rem;
            `;
            feedback.innerHTML = `<i class="fas fa-sim-card" style="color: #3b82f6;"></i> ${message}`;
            
            document.body.appendChild(feedback);
            
            setTimeout(() => {
                feedback.style.animation = 'fadeOutDown 0.3s ease';
                setTimeout(() => feedback.remove(), 300);
            }, 2000);
        }

        // Simulate logout
        function simulateLogout() {
            if (confirm('Simulation: Logout from Manager Dashboard?')) {
                showNotification('Logging out...', 'info');
                
                setTimeout(() => {
                    // Simulate redirect
                    document.body.innerHTML = `
                        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #142138; color: white; flex-direction: column; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: #4ade80; font-size: 4rem;"></i>
                            <h2>Logged Out Successfully</h2>
                            <p style="color: rgba(255,255,255,0.6);">This is a simulation. Click below to return to dashboard.</p>
                            <button onclick="window.location.reload()" style="background: #3b82f6; color: white; border: none; padding: 0.8rem 2rem; border-radius: 50px; cursor: pointer; margin-top: 1rem;">Return to Dashboard</button>
                        </div>
                    `;
                }, 1500);
            }
        }

        // Add animation keyframes
        const animationStyles = document.createElement('style');
        animationStyles.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes fadeInUp {
                from {
                    transform: translateY(100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes fadeOutDown {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(animationStyles);
    </script>
</body>
</html>
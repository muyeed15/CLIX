<?php
global $conn;
session_start();
require_once './db-connection.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CLIX: Analytics Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/admin-base.css">
    <link rel="stylesheet" href="../css/charts.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .analytics-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(83, 207, 107, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .analytics-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        #analyticsModal .modal-dialog {
            margin-top: 70px;
        }

        .modal-backdrop {
            top: 70px;
            height: calc(100% - 70px);
        }

        .stats-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.08);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #3d81ff;
        }
        .stats-label {
            font-size: 1rem;
            color: #6c757d;
        }
        .insight-item {
            padding: 10px 15px;
            border-left: 4px solid #3d81ff;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 0 5px 5px 0;
        }
        .recommendation-item {
            padding: 10px 15px;
            border-left: 4px solid #53cf6b;
            background-color: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 0 5px 5px 0;
        }
        .graph-img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .last-update {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: right;
            margin-top: 10px;
        }
        .no-data-container {
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 30px;
            background: #f8f9fa;
            border: 1px solid rgba(83, 207, 107, 0.1);
            position: relative;
        }
        #liveOutput {
            height: 300px; 
            overflow-y: auto; 
            background-color: #f8f9fa; 
            border-radius: 5px; 
            padding: 10px; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 0.85rem; 
            white-space: pre-wrap; 
            color: #333;
            border: 1px solid #dee2e6;
            font-family: 'Consolas', 'Monaco', monospace;
            background: #1a1a1a;
            color: #53cf6b;
            border: none;
            border-radius: 4px;
            padding: 12px;
            line-height: 1.5;
        }
        .thinking-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background: transparent;
            padding: 10px;
            border-radius: 4px;
        }
        .thinking-dot {
            height: 8px;
            width: 8px;
            margin: 0 3px;
            background-color: #53cf6b;
            border-radius: 50%;
            display: inline-block;
            animation: subtlePulse 1.5s infinite;
            box-shadow: 0 0 10px rgba(83, 207, 107, 0.5);
        }
        @keyframes subtlePulse {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }
        /* Header styles for title and refresh button */
        .header-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .refresh-button {
            background-color: #53cf6b;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }
        .refresh-button:hover {
            background-color: #48b85d;
        }
        .refresh-button i {
            font-size: 1.2rem;
            margin-right: 8px;
        }
        #refreshIndicator {
            display: inline-block;
            margin-left: 10px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* AI-themed gradient backgrounds */
        .analytics-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(83, 207, 107, 0.1);
            backdrop-filter: blur(10px);
            animation: cardFadeIn 0.6s ease-out;
        }

        /* Neural network background pattern */
        .no-data-container {
            background: 
                radial-gradient(circle at 10% 20%, rgba(83, 207, 107, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 80% 60%, rgba(83, 207, 107, 0.05) 0%, transparent 20%);
            position: relative;
            overflow: hidden;
        }

        /* Floating particles effect */
        .no-data-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2353cf6b' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: particleFloat 20s linear infinite;
        }

        /* Processing animation enhancements */
        .thinking-header {
            background: transparent;
            padding: 10px;
            border-radius: 4px;
        }

        .thinking-dot {
            height: 8px;
            width: 8px;
            margin: 0 3px;
            background-color: #53cf6b;
            border-radius: 50%;
            display: inline-block;
            animation: subtlePulse 1.5s infinite;
        }

        /* Progress bar enhancement */
        #analyticsProgress {
            background-image: linear-gradient(
                45deg,
                rgba(255, 255, 255, .15) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, .15) 50%,
                rgba(255, 255, 255, .15) 75%,
                transparent 75%,
                transparent
            );
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 1s linear infinite;
        }

        /* AI-themed animations */
        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes particleFloat {
            from { background-position: 0 0; }
            to { background-position: 60px 60px; }
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Enhanced modal styling */
        #analyticsModal .modal-content {
            border: none;
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        #liveOutput {
            font-family: 'Consolas', 'Monaco', monospace;
            background: #1a1a1a;
            color: #53cf6b;
            border: none;
            border-radius: 4px;
            padding: 12px;
            line-height: 1.5;
        }

        #liveOutput::before {
            content: '> ';
            color: #53cf6b;
            opacity: 0.7;
        }

        /* Clean modal styling */
        #analyticsModal .modal-content {
            border: none;
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php require_once './admin-header.php'; ?>

    <!-- Main Content -->
    <main id="main-section">
        <div class="header-actions">
            <h2 id="sub-div-header">AI Analytics</h2>
            <button id="runAnalytics" class="refresh-button" onclick="runAnalytics()">
                <i class="bi bi-arrow-repeat"></i>
                <span>Refresh Analytics</span>
                <span id="refreshIndicator" style="display: none;">
                    <i class="bi bi-hourglass-split"></i>
                </span>
            </button>
        </div>
        
        <?php
        // Load analytics data from files
        $analytics_dir = '../data';
        $data_file = $analytics_dir . '/analytics_data.json';
        $summary_file = $analytics_dir . '/analytics_summary.json';
        
        // Check if files exist and are readable
        $analytics_exist = file_exists($data_file) && file_exists($summary_file);
        ?>
        
        <!-- No Analytics Data View -->
        <div id="noDataView" class="<?php echo $analytics_exist ? 'd-none' : ''; ?>">
            <div class="no-data-container">
                <h3>No Analytics Data Available</h3>
                <p class="mb-4">Analytics data has not been generated yet. Please run the analytics pipeline to generate insights and visualizations.</p>
                <button id="initialRunAnalytics" class="btn btn-primary" onclick="runAnalytics()" style="display: none">
                    <i class="bi bi-arrow-repeat me-2"></i>Run Analytics Pipeline
                </button>
            </div>
        </div>
        
        <!-- Analytics Content View -->
        <div id="analyticsContent" class="<?php echo $analytics_exist ? '' : 'd-none'; ?>">
            <?php
            if ($analytics_exist) {
                try {
                    $data = json_decode(file_get_contents($data_file), true);
                    $summary = json_decode(file_get_contents($summary_file), true);
                    $file_modified = date("F j, Y, g:i a", filemtime($data_file));
                    
                    // Extract data for display
                    $stats = $data['stats'] ?? [];
                    $graphs = $data['graphs'] ?? [];
            ?>
            
            <!-- Summary Header -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card analytics-card">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($summary['title'] ?? 'CLIX Analytics Dashboard'); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($summary['summary'] ?? 'Analytics summary not available.'); ?></p>
                            <p class="last-update">Last updated: <?php echo htmlspecialchars($file_modified); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
                        <div class="stats-label">Total Users</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo number_format($stats['active_devices'] ?? 0); ?></div>
                        <div class="stats-label">Active Devices</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo number_format($stats['unpaid_devices'] ?? 0); ?></div>
                        <div class="stats-label">Unpaid Devices</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo number_format($stats['pending_requests'] ?? 0); ?></div>
                        <div class="stats-label">Pending Requests</div>
                    </div>
                </div>
            </div>
            
            <!-- Graphs Section -->
            <div class="row mb-4">
                <?php foreach ($graphs as $graph): ?>
                <div class="col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($graph['title']); ?></h5>
                            <img src="<?php echo $analytics_dir . '/graphs/' . htmlspecialchars($graph['filename']); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($graph['title']); ?>" class="graph-img">
                            <p class="card-text mt-2"><?php echo htmlspecialchars($graph['description']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- AI Insights Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Key Insights</h5>
                            <?php if (!empty($summary['insights'])): ?>
                                <?php foreach ($summary['insights'] as $insight): ?>
                                    <div class="insight-item">
                                        <?php echo htmlspecialchars($insight); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No insights available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Recommendations</h5>
                            <?php if (!empty($summary['recommendations'])): ?>
                                <?php foreach ($summary['recommendations'] as $recommendation): ?>
                                    <div class="recommendation-item">
                                        <?php echo htmlspecialchars($recommendation); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No recommendations available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error loading analytics data: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            ?>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once './admin-footer.php'; ?>

    <!-- Analytics Processing Modal -->
    <div class="modal fade" id="analyticsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="analyticsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="analyticsModalLabel">Analytics Pipeline</h5>
                </div>
                <div class="modal-body">
                    <div class="thinking-header">
                        <div id="thinkingIndicator">
                            <span>Processing</span>
                            <span class="thinking-dot"></span>
                            <span class="thinking-dot"></span>
                            <span class="thinking-dot"></span>
                        </div>
                    </div>
                    
                    <div class="progress mb-3">
                        <div id="analyticsProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 5%"></div>
                    </div>
                    
                    <div id="liveOutput">Starting analytics pipeline...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancelButton" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/bootstrap.bundle.js"></script>
    <script>
        // Initialize analytics modal
        let analyticsModal;
        let controller; // For aborting fetch
        let checkInterval; // Interval for periodically checking for results
        
        document.addEventListener('DOMContentLoaded', function() {
            analyticsModal = new bootstrap.Modal(document.getElementById('analyticsModal'));
            
            // Set up cancel button
            document.getElementById('cancelButton').addEventListener('click', function() {
                if (controller) {
                    controller.abort();
                }
                if (checkInterval) {
                    clearInterval(checkInterval);
                }
            });
        });
        
        function runAnalytics() {
            // Determine which button was clicked
            const isInitialRun = document.getElementById('noDataView').classList.contains('d-none') === false;
            const button = isInitialRun ? 
                document.getElementById('initialRunAnalytics') : 
                document.getElementById('runAnalytics');
            
            // Disable the button and show spinner
            button.disabled = true;
            document.getElementById('refreshIndicator').style.display = 'inline-block';
            
            // Reset progress and output
            document.getElementById('analyticsProgress').style.width = '5%';
            document.getElementById('analyticsProgress').classList.add('progress-bar-animated');
            document.getElementById('analyticsProgress').classList.remove('bg-danger', 'bg-success');
            document.getElementById('analyticsProgress').classList.add('bg-primary');
            
            document.getElementById('liveOutput').innerHTML = 'Starting analytics pipeline...\n';
            document.getElementById('thinkingIndicator').style.display = 'block';
            
            // Show modal
            analyticsModal.show();
            
            // Set up progress animation
            const totalDuration = 30000;
            const startTime = Date.now();
            const startProgress = 5;
            const endProgress = 100;
            
            const progressInterval = setInterval(() => {
                const elapsedTime = Date.now() - startTime;
                const progress = startProgress + ((endProgress - startProgress) * (elapsedTime / totalDuration));
                
                if (elapsedTime >= totalDuration) {
                    clearInterval(progressInterval);
                    document.getElementById('analyticsProgress').style.width = '100%';
                    document.getElementById('analyticsProgress').classList.remove('progress-bar-animated');
                    document.getElementById('analyticsProgress').classList.add('bg-success');
                    document.getElementById('thinkingIndicator').style.display = 'none';
                    document.getElementById('liveOutput').innerHTML += '\n\n✅ Analytics completed successfully!\nReloading dashboard...';
                    
                    // Refresh the page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000); // Wait 1 second before refreshing
                    
                    return;
                }
                
                document.getElementById('analyticsProgress').style.width = Math.min(progress, 99) + '%';
            }, 100);
            
            // Create abort controller for fetch
            controller = new AbortController();
            const signal = controller.signal;
            
            // Make the AJAX request
            fetch('run-analytics.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: signal
            })
            .then(response => {
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                
                function processText({ done, value }) {
                    if (done) {
                        return;
                    }
                    
                    // Decode chunk and add to buffer
                    const chunk = decoder.decode(value, { stream: true });
                    buffer += chunk;
                    
                    // Process complete JSON objects
                    let jsonLines = buffer.split('\n');
                    buffer = jsonLines.pop(); // Keep the last potentially incomplete line
                    
                    for (const line of jsonLines) {
                        if (line.trim() === '') continue;
                        
                        try {
                            const data = JSON.parse(line);
                            processMessage(data);
                        } catch (e) {
                            console.error('Error parsing JSON:', e, line);
                        }
                    }
                    
                    // Continue reading
                    return reader.read().then(processText);
                }
                
                return reader.read().then(processText);
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    console.log('Fetch aborted');
                } else {
                    console.error('Fetch error:', error);
                    
                    // Update UI for error
                    document.getElementById('analyticsProgress').classList.remove('progress-bar-animated');
                    document.getElementById('analyticsProgress').classList.remove('bg-primary');
                    document.getElementById('analyticsProgress').classList.add('bg-danger');
                    
                    document.getElementById('liveOutput').innerHTML += '\n\nError: ' + error.message;
                    document.getElementById('thinkingIndicator').style.display = 'none';
                }
                
                // Stop progress animation and reset button
                clearInterval(progressInterval);
                button.disabled = false;
                document.getElementById('refreshIndicator').style.display = 'none';
            });
            
            // Function to process incoming messages
            function processMessage(data) {
                const outputEl = document.getElementById('liveOutput');
                
                if (data.type === 'progress') {
                    // Append output data
                    outputEl.innerHTML += data.data;
                    outputEl.scrollTop = outputEl.scrollHeight;
                } 
                else if (data.type === 'complete') {
                    outputEl.innerHTML += '\n\n' + (data.success ? 
                        '✅ Processing completed successfully!' : 
                        '❌ Error: ' + data.message);
                    
                    // Scroll to bottom of output
                    outputEl.scrollTop = outputEl.scrollHeight;
                }
            }
        }
    </script>
</body>
</html>
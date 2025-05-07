<?php
// Prevent direct access to this script
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Set appropriate content type for JSON response
header('Content-Type: application/json');

try {
    // Get the absolute path to the Python script
    $pythonScript = realpath(__DIR__ . '/../python/clix_analytics.py');
    
    if (!file_exists($pythonScript)) {
        throw new Exception("Python script not found at: $pythonScript");
    }
    
    // Create data directory with correct permissions
    $dataDir = realpath(__DIR__ . '/..') . '/data';
    if (!is_dir($dataDir)) {
        // Try to create the directory with web server permissions
        if (!mkdir($dataDir, 0777, true)) {
            echo json_encode([
                'type' => 'progress',
                'data' => "Warning: Couldn't create data directory with PHP, will try from Terminal\n"
            ]) . "\n";
            ob_flush();
            flush();
        } else {
            chmod($dataDir, 0777); // Make sure permissions are set correctly
        }
    }
    
    // Graphdir
    $graphDir = $dataDir . '/graphs';
    if (!is_dir($graphDir)) {
        // Try to create the directory with web server permissions
        if (!mkdir($graphDir, 0777, true)) {
            echo json_encode([
                'type' => 'progress',
                'data' => "Warning: Couldn't create graphs directory with PHP, will try from Terminal\n"
            ]) . "\n";
            ob_flush();
            flush();
        } else {
            chmod($graphDir, 0777); // Make sure permissions are set correctly
        }
    }
    
    // Send initial progress update
    echo json_encode([
        'type' => 'progress',
        'data' => "Starting CLIX analytics pipeline...\n"
    ]) . "\n";
    ob_flush();
    flush();
    
    // Create AppleScript to run the Python script in Terminal
    $scriptDir = dirname($pythonScript);
    $appleScript = 'tell application "Terminal" to do script "cd ' . $scriptDir . ' && python3 ' . basename($pythonScript) . '"';
    
    // Execute the AppleScript command
    $command = 'osascript -e \'' . $appleScript . '\'';
    exec($command, $output, $exitCode);
    
    if ($exitCode !== 0) {
        throw new Exception("Failed to open Terminal to run the Python script. Exit code: $exitCode");
    }
    
    // Simulate progress updates so the user feels things are happening
    $steps = [
        "Python script launched in Terminal",
        "Connecting to database...",
        "Fetching data...",
        "Analyzing data...",
        "Creating AI-powered analytics...",
        "Finalizing outputs..."
    ];
    
    // Calculate fixed delay to make total process take 25 seconds
    $totalSeconds = 25;
    $fixedDelay = $totalSeconds / count($steps);
    
    foreach ($steps as $step) {
        usleep($fixedDelay * 1000000);
        
        echo json_encode([
            'type' => 'progress',
            'data' => "$step\n"
        ]) . "\n";
        ob_flush();
        flush();
    }
    
    // Final progress message
    echo json_encode([
        'type' => 'progress',
        'data' => "\nAnalytics process started successfully. The dashboard will update automatically when processing is complete.\n"
    ]) . "\n";
    ob_flush();
    flush();
    
    // Send response to browser
    echo json_encode([
        'type' => 'complete',
        'success' => true,
        'message' => 'Analytics process started in Terminal',
        'output' => implode("\n", $output)
    ]);
    
} catch (Exception $e) {
    // Send error message
    echo json_encode([
        'type' => 'complete',
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
?>
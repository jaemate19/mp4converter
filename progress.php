<?php
header("Content-Type: application/json");

$jobId = $_GET['job'] ?? null;
if (!$jobId) {
    echo json_encode(["status" => "error", "message" => "No job ID"]);
    exit;
}

// Security: Only allow hex characters
if (!preg_match('/^[a-f0-9]+$/', $jobId)) {
    echo json_encode(["status" => "error", "message" => "Invalid job ID"]);
    exit;
}

$jobDir = __DIR__ . "/jobs/$jobId";
$outputFile = "$jobDir/output.mp3";
$progressFile = "$jobDir/progress.txt";

// Check if conversion is complete
if (file_exists($outputFile)) {
    // Create the download URL
    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    //$url = "$protocol://$host/converter/jobs/$jobId/output.mp3";
    $url = "$protocol://$host/jobs/$jobId/output.mp3";
    
    echo json_encode([
        "status" => "done",
        "url" => $url
    ]);
    exit;
}

// Check if still processing
if (file_exists($progressFile)) {
    $content = file_get_contents($progressFile);
    preg_match("/out_time_ms=(\d+)/", $content, $matches);
    $progress = $matches[1] ?? 0;
    
    echo json_encode([
        "status" => "processing",
        "progress" => $progress
    ]);
    exit;
}

// Job not found
echo json_encode(["status" => "error", "message" => "Job not found"]);
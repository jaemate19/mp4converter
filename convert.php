<?php

// This tells the browser/client to expect JSON format
header("Content-Type: application/json");

/* CHECK 1: Validate that a file was actually uploaded
 * $_FILES['upload'] contains information about the uploaded file
 * If it's not set, no file was submitted with the form  */
if (!isset($_FILES['upload'])) 
{
  echo json_encode(["status" => "error", "message" => "No file uploaded"]);
  exit;
}

// Store the uploaded file information in a variable for easier access
$file = $_FILES['upload'];

// Define maximum allowed file size (10MB in bytes)
// The underscore in 10_000_000 is a PHP 7.4+ feature for readability (same as 10000000)
$maxSize = 10_000_000;


/* CHECK 2: Verify the file is actually an MP4 using MIME type detection 
 * This is more secure than just checking the file extension because it
 * examines the file's actual content  */
$finfo = finfo_open(FILEINFO_MIME_TYPE);  // Initialize the file info system
$mime = finfo_file($finfo, $file['tmp_name']);  // Get the actual MIME type from temp file
finfo_close($finfo);  // Close the file info resource to free memory

// If the file isn't an MP4 video, reject it
if ($mime !== "video/mp4") 
{
  echo json_encode(["status" => "error", "message" => "Invalid file type"]);
  exit;
}

/**
 * CHECK 3: Ensure the file size doesn't exceed our limit
 */
if ($file['size'] > $maxSize) 
{
  echo json_encode(["status" => "error", "message" => "File too large"]);
  exit;
}

/**
 * CREATE A UNIQUE JOB DIRECTORY
 * 
 * Generate a random job ID to prevent collisions and for security
 * (so users can't guess each other's file paths)
 */
$jobId = bin2hex(random_bytes(8));  // Creates a 16-character random string (e.g., "4f6a8b2c1d3e9f7a")
$jobDir = __DIR__ . "/jobs/$jobId";  // Full path to the job directory (e.g /var/www/html/jobs/4f6a8b2c1d3e9f7a)

// Create the directory with read/write/execute for owner, read/execute for others
// The third parameter "true" allows creating nested directories if needed
mkdir($jobDir, 0755, true);

/**
 * SET UP FILE PATHS
 * 
 * Define where the input, output, and progress files will be stored
 */
$input = "$jobDir/input.mp4";        // Original uploaded file (/var/www/html/jobs/4f6a8b2c1d3e9f7a/input.mp4)
$output = "$jobDir/output.mp3";       // Converted audio file  (/var/www/html/jobs/4f6a8b2c1d3e9f7a/output.mp3)
$progressFile = "$jobDir/progress.txt";  // FFmpeg progress tracking file

/**
 * SAVE THE UPLOADED FILE
 * 
 * Move the file from PHP's temporary storage to our permanent job directory
 */
move_uploaded_file($file['tmp_name'], $input);

// Initialize progress file with "0" meaning 0% complete
// FFmpeg will update this file during conversion
file_put_contents($progressFile, "0");

/**
 * BUILD AND EXECUTE THE FFMPEG COMMAND
 * 
 * FFmpeg options explained:
 * -i %s              : Input file path
 * -vn                : Disable video processing (no video in output)
 * -acodec libmp3lame : Use LAME MP3 encoder
 * -ab 192k           : Audio bitrate of 192 kbps (good quality)
 * %s                 : Output file path
 * -progress %s       : File to write progress information
 * > /dev/null 2>&1   : Redirect all output to nowhere (run silently)
 * &                  : Run in background (async/non-blocking)
 * Final: "ffmpeg -i '/path/input.mp4' -vn -acodec libmp3lame -ab 192k '/path/output.mp3' -progress '/path/progress.txt' ..."
 */

$cmd = sprintf(
    "./convert.sh %s %s %s > /dev/null 2>&1 &",
    escapeshellarg($input),
    escapeshellarg($output),
    escapeshellarg($progressFile)
);
// Execute the command (runs in background, script continues immediately)
exec($cmd);

/**
 * RETURN SUCCESS RESPONSE
 * 
 * Send back the job ID so the client can check conversion status
 * The client will use this ID to poll for completion/download
 */
echo json_encode([
  "status" => "success",
  "jobId" => $jobId  // Client needs this to retrieve the converted file later
]);

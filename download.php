<?php

$jobId = $_GET['job'] ?? null;

if (!$jobId) {
    http_response_code(400);
    exit;
}

$file = __DIR__ . "/jobs/$jobId/output.mp3";

if (!file_exists($file)) {
    http_response_code(404);
    exit;
}

header("Content-Type: audio/mpeg");
header("Content-Disposition: attachment; filename=converted.mp3");

readfile($file);

/* Delete files after download */

$folder = __DIR__ . "/jobs/$jobId";

foreach (glob("$folder/*") as $f) {
    unlink($f);
}

rmdir($folder);

Change the download URL

Instead of:

jobs/$jobId/output.mp3

Return:

download.php?job=$jobId
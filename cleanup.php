<?php

$jobsDir = __DIR__ . "/jobs";
$maxAge = 3600; // seconds (1 hour)

$now = time();

foreach (glob("$jobsDir/*") as $jobFolder) {

    if (!is_dir($jobFolder)) continue;

    $folderAge = $now - filemtime($jobFolder);

    if ($folderAge > $maxAge) {

        foreach (glob("$jobFolder/*") as $file) {
            unlink($file);
        }

        rmdir($jobFolder);
    }
}

echo "Cleanup complete\n";
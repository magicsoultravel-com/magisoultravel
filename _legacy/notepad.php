<?php
// Enforce execution time and memory
ini_set('max_execution_time', 300); // 5 minutes
set_time_limit(300);
ini_set('memory_limit', '1G');

// Require authentication and admin check
require_once __DIR__ . '/../inc/auth.php';
if (!is_admin()) {
    header('Location: /index.php');
    exit;
}

// Base directory
$baseDir = realpath(__DIR__ . '/../');

// Backup directory
$backupDir = $baseDir . DIRECTORY_SEPARATOR . 'backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Exclude directory (gallery originals)
$excludeDir = realpath($baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery' . DIRECTORY_SEPARATOR . 'originals') . DIRECTORY_SEPARATOR;

// ZIP name
$rootDirectoryName = basename($baseDir);
$zipName = $rootDirectoryName . '_' . date('Ymd_His') . '.zip';
$zipPath = $backupDir . DIRECTORY_SEPARATOR . $zipName;

// Initialize ZipArchive
$zip = new ZipArchive();
$result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
if ($result !== TRUE) {
    die("Failed to create archive (code $result)");
}

// Recursive function to add files/folders
function addFolderToZip($folder, $zip, $basePathLength, $excludeDirPath, $excludeExtensions) {
    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $folder . DIRECTORY_SEPARATOR . $item;
        $normalizedFullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, realpath($fullPath)) . DIRECTORY_SEPARATOR;

        if (strpos($normalizedFullPath, $excludeDirPath) === 0) continue;

        if (is_dir($fullPath)) {
            addFolderToZip($fullPath, $zip, $basePathLength, $excludeDirPath, $excludeExtensions);
        } elseif (is_file($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if (in_array($ext, $excludeExtensions)) continue;

            $localPath = substr($fullPath, $basePathLength);
            $localPath = ltrim(str_replace('\\', '/', $localPath), '/');
            $zip->addFile($fullPath, $localPath);
        }
    }
}

// Exclude images and archive files
$excludeFileExtensions = ['jpg','jpeg','img','zip','tar','gz','rar'];

// Add files to ZIP
addFolderToZip($baseDir, $zip, strlen($baseDir), $excludeDir, $excludeFileExtensions);

// Close archive
$zip->close();

// Stream ZIP to browser for download
if (file_exists($zipPath) && filesize($zipPath) > 0) {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('X-Accel-Buffering: no'); // prevent Nginx buffering

    $fp = fopen($zipPath, 'rb');
    if ($fp) {
        while (!feof($fp)) {
            echo fread($fp, 8192);
            flush();
        }
        fclose($fp);
    }
    unlink($zipPath); // remove after download
    exit;
} else {
    echo "Error creating ZIP file.";
}

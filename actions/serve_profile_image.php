<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

$filename = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING);
$filepath = '../uploads/profiles/' . basename($filename);

if (file_exists($filepath)) {
    $mime = mime_content_type($filepath);
    header('Content-Type: ' . $mime);
    readfile($filepath);
} else {
    header("HTTP/1.0 404 Not Found");
}
?> 
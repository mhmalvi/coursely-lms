<?php

// Laravel Bootstrap for Vercel Serverless Functions
// This file serves as the entry point for all Laravel routes

// Set the current directory for relative path resolution
chdir(__DIR__ . '/../');

// Override server variables for proper Laravel routing
$_SERVER['SCRIPT_NAME'] = '/api/index.php';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

// Remove /api prefix from REQUEST_URI for proper Laravel routing
if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 4) ?: '/';
}

// Set document root to Laravel's public directory
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';

// Ensure proper path info for Laravel
if (!isset($_SERVER['PATH_INFO']) && isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['PATH_INFO'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

// Set HTTPS for production
if (!isset($_SERVER['HTTPS']) && isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Set SERVER_NAME if not present
if (!isset($_SERVER['SERVER_NAME']) && isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
}

// Set SERVER_PORT for HTTPS
if (!isset($_SERVER['SERVER_PORT'])) {
    $_SERVER['SERVER_PORT'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '443' : '80';
}

// Bootstrap Laravel application
require_once __DIR__ . '/../public/index.php';
<?php

// 1. Enforce HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    http_response_code(403);
    exit('Access Forbidden - HTTPS is required.');
}

// 2. Get accept header for handle in Browser Request
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

// 3. Check for Browser Request (React Logic)
// If the client explicitly accepts 'text/html', serve the React App.
if (strpos($acceptHeader, 'text/html') !== false) {

    // Path to your React index.html
    $reactIndex = __DIR__ . '/index.html';

    if (file_exists($reactIndex)) {
        // Set correct header and serve the file
        header('Content-Type: text/html');
        readfile($reactIndex);
        exit();
    } else {
        // Fallback if index.html is missing
        echo "React build files (index.html) not found in root directory.";
        exit();
    }
}

// 4. Proxy Logic (If not a browser HTML request)
// This code only runs if the 'if' block above was skipped.
// Proxy request from Iran server to main pasarguard panel sub 
$host_domain = "sub.replace-with-your-pasarguard-domain.com";
$server_domain = "https://" . $host_domain;
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$requestUri = $_SERVER['REQUEST_URI'];
$url = $server_domain . $requestUri;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADERFUNCTION => function ($curl, $header) {
        // Forward headers from the target response back to the client
        header($header);
        return strlen($header);
    },
]);

// Prepare Headers
$headers = [
    "CF-Connecting-IP: $ip",
    "Host: $host_domain",
    "User-Agent: $userAgent",
    "Accept: " . $acceptHeader
];

// Forward other incoming headers
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        // Skip Host and Connection headers to avoid conflicts
        if ($key === 'HTTP_HOST' || $key === 'HTTP_CONNECTION') continue;

        $headerName = str_replace('_', '-', substr($key, 5));
        // Avoid duplicating headers we manually set above
        if (
            stripos($headerName, 'CF-Connecting-IP') === false &&
            stripos($headerName, 'User-Agent') === false &&
            stripos($headerName, 'Accept') === false
        ) {
            $headers[] = "$headerName: $value";
        }
    }
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    exit('Internal Server Error: ' . curl_error($ch));
}

curl_close($ch);
echo $response;

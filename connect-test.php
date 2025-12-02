<?php
// connect_test.php
require_once __DIR__ . '/config.php';

// This will let us see whether $conn exists and is a mysqli object
echo '<pre>';
echo 'CONFIG LOADED: ' . __FILE__ . PHP_EOL;
var_dump(isset($conn) ? get_class($conn) : null);
if (isset($conn) && $conn instanceof mysqli) {
    echo "Server info: " . $conn->server_info . PHP_EOL;
}
echo '</pre>';

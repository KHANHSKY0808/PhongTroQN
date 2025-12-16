<?php
echo "<h1>Test Routing</h1>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "</pre>";

// Kiểm tra htaccess
if (!function_exists('apache_get_modules')) {
    echo "<p style='color:red'>Apache modules info không available</p>";
} else {
    echo "<h2>Apache Modules:</h2>";
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color:green'>✓ mod_rewrite is ENABLED</p>";
    } else {
        echo "<p style='color:red'>✗ mod_rewrite is NOT enabled</p>";
    }
}
?>

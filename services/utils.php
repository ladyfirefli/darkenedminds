<?php
function customLog($message) {
    // Check the environment variable to enable/disable logging
    $loggingEnabled = getenv('LOGGING_ENABLED') === 'true';
    if ($loggingEnabled) {
        error_log($message);
    }
}
?>
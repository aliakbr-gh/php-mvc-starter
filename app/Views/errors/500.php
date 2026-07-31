<?php
$status = in_array($status, [401, 403, 404, 419, 500], true) ? $status : 500;
$title = 'Server error';
$defaultMessage = 'Something went wrong while processing your request.';
require __DIR__ . '/error.php';

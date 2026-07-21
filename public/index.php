<?php

declare(strict_types=1);

use App\Core\Request;

define('BASE_PATH', dirname(__DIR__));

$app = require BASE_PATH . '/bootstrap/app.php';
require BASE_PATH . '/routes/web.php';

$app->run(Request::capture());

<?php

declare(strict_types=1);

namespace Core;

use mysqli;

interface Migration
{
    public function up(mysqli $database): void;

    public function down(mysqli $database): void;
}

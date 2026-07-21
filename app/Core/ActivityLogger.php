<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Activity;
use Throwable;

final class ActivityLogger
{
    public static function log(string $activity, ?int $userId = null): void
    {
        try {
            (new Activity())->create($activity, $userId ?? (Auth::user()['id'] ?? null));
        } catch (Throwable $exception) {
            // Activity history is useful, but it should never break the user request.
            Logger::error('Could not save database activity', ['exception' => $exception->getMessage()]);
        }
    }
}

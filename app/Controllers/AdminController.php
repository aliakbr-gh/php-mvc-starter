<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use PDOException;

abstract class AdminController extends Controller
{
    protected function filters(Request $request): array
    {
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [5, 10, 25, 50], true)) {
            $perPage = 10;
        }

        return [
            trim((string) $request->query('search', '')),
            max(1, (int) $request->query('page', 1)),
            $perPage,
        ];
    }

    protected function databaseError(
        PDOException $exception,
        string $back,
        string $duplicateMessage,
        string $defaultMessage
    ): Response {
        flash(
            'error',
            (string) $exception->getCode() === '23000' ? $duplicateMessage : $defaultMessage
        );

        return Response::redirect($back);
    }

    protected function missing(string $resource, string $indexUrl): Response
    {
        flash('error', $resource . ' not found.');

        return Response::redirect(url($indexUrl));
    }
}

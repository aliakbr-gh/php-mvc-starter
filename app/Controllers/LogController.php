<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Paginator;
use Core\RequestLogger;

final class LogController extends Controller
{
    public function index(): void
    {
        $date = (string) ($_GET['date'] ?? date('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $request = Paginator::request(50);
        $allRows = RequestLogger::read($date);
        $total = count($allRows);
        $page = $request['page'];
        $offset = Paginator::offset($total, $page, $request['per_page']);
        $pagination = Paginator::result(
            array_slice($allRows, $offset, $request['per_page']),
            $total,
            $page,
            $request['per_page'],
            ['date' => $date]
        );

        $this->view('logs/index', [
            'title' => 'Request Logs',
            'date' => $date,
            'pagination' => $pagination,
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
            'paginationPath' => 'logs',
            'loggingEnabled' => (bool) $GLOBALS['config']['request_logging'],
        ]);
    }
}

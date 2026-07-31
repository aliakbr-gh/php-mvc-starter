<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ActivityLog;
use Core\Controller;
use Core\Paginator;

final class ActivityLogController extends Controller
{
    public function index(): void
    {
        $date = (string) ($_GET['date'] ?? date('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $request = Paginator::request(25);
        $this->view('activity-logs/index', [
            'title' => 'Activity Logs',
            'date' => $date,
            'pagination' => (new ActivityLog())->paginate(
                $date,
                $request['page'],
                $request['per_page']
            ),
            'allowedPerPage' => Paginator::PER_PAGE_OPTIONS,
            'paginationPath' => 'activity-logs',
        ]);
    }
}

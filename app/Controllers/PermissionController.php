<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;
use PDOException;

final class PermissionController extends Controller
{
    public function index(): Response
    {
        $request = Request::capture();
        [$search, $page, $perPage] = $this->filters($request);
        $result = (new Permission())->paginate($search, $page, $perPage);

        return $this->view('admin/permissions/index', [
            'title' => 'Permissions',
            'user' => Auth::user(),
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'result' => $result,
        ], 'layouts/dashboard');
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(): Response
    {
        $request = Request::capture();
        $data = $this->data($request);

        if ($data === null) {
            return Response::redirect(url('admin/permissions/create'));
        }

        try {
            (new Permission())->create(...$data);
        } catch (PDOException $exception) {
            return $this->databaseError($exception, url('admin/permissions/create'));
        }

        ActivityLogger::log(Auth::user()['name'] . ' created permission ' . $data[1] . ' from ' . $request->ip());
        flash('success', 'Permission created.');

        return Response::redirect(url('admin/permissions'));
    }

    public function edit(string $id): Response
    {
        $record = (new Permission())->find((int)$id);

        return $record ? $this->form($record) : $this->missing();
    }

    public function update(string $id): Response
    {
        $request = Request::capture();

        if ((new Permission())->find((int)$id) === null) {
            return $this->missing();
        }

        $data = $this->data($request);

        if ($data === null) {
            return Response::redirect(url('admin/permissions/' . $id . '/edit'));
        }

        try {
            (new Permission())->update((int)$id, ...$data);
        } catch (PDOException $exception) {
            return $this->databaseError($exception, url('admin/permissions/' . $id . '/edit'));
        }

        ActivityLogger::log(Auth::user()['name'] . ' updated permission ' . $data[1] . ' from ' . $request->ip());
        flash('success', 'Permission updated.');

        return Response::redirect(url('admin/permissions'));
    }

    public function delete(string $id): Response
    {
        $record = (new Permission())->find((int)$id);

        if ($record === null) {
            return $this->missing();
        }

        (new Permission())->delete((int)$id);
        ActivityLogger::log(Auth::user()['name'] . ' deleted permission ' . $record['slug'] . ' from ' . Request::capture()->ip());
        flash('success', 'Permission deleted.');

        return Response::redirect(url('admin/permissions'));
    }

    private function form(?array $record): Response
    {
        return $this->view('admin/permissions/form', [
            'title' => $record ? 'Edit permission' : 'Create permission',
            'user' => Auth::user(),
            'record' => $record,
        ], 'layouts/dashboard');
    }

    private function data(Request $request): ?array
    {
        $name = trim((string)$request->input('name'));
        $slug = strtolower(trim((string)$request->input('slug')));

        if (strlen($name) < 2 || !preg_match('/^[a-z0-9.-]+$/', $slug)) {
            flash('error', 'Use a valid name and slug such as reports.view.');
            return null;
        }

        return [$name, $slug];
    }

    private function filters(Request $request): array
    {
        $perPage = (int)$request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        return [
            trim((string)$request->query('search', '')),
            max(1, (int)$request->query('page', 1)),
            $perPage,
        ];
    }

    private function databaseError(PDOException $exception, string $back): Response
    {
        $message = (string)$exception->getCode() === '23000'
            ? 'That permission slug already exists.'
            : 'Could not save the permission.';

        flash('error', $message);
        return Response::redirect($back);
    }

    private function missing(): Response
    {
        flash('error', 'Permission not found.');
        return Response::redirect(url('admin/permissions'));
    }
}

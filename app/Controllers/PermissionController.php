<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;
use PDOException;

final class PermissionController extends AdminController
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
        ]);
    }

    public function create(): Response
    {
        return $this->view('admin/permissions/create', ['title' => 'Create permission', 'user' => Auth::user()]);
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
            return $this->databaseError($exception, url('admin/permissions/create'), 'That permission slug already exists.', 'Could not save the permission.');
        }

        ActivityLogger::log(Auth::user()['name'] . ' created permission ' . $data[1] . ' from ' . $request->ip());
        flash('success', 'Permission created.');

        return Response::redirect(url('admin/permissions'));
    }

    public function edit(string $id): Response
    {
        $record = (new Permission())->find((int) $id);

        return $record
            ? $this->view('admin/permissions/edit', ['title' => 'Edit permission', 'user' => Auth::user(), 'record' => $record])
            : $this->missing('Permission', 'admin/permissions');
    }

    public function update(string $id): Response
    {
        $request = Request::capture();

        if ((new Permission())->find((int) $id) === null) {
            return $this->missing('Permission', 'admin/permissions');
        }

        $data = $this->data($request);

        if ($data === null) {
            return Response::redirect(url('admin/permissions/' . $id . '/edit'));
        }

        try {
            (new Permission())->update((int) $id, ...$data);
        } catch (PDOException $exception) {
            return $this->databaseError($exception, url('admin/permissions/' . $id . '/edit'), 'That permission slug already exists.', 'Could not save the permission.');
        }

        ActivityLogger::log(Auth::user()['name'] . ' updated permission ' . $data[1] . ' from ' . $request->ip());
        flash('success', 'Permission updated.');

        return Response::redirect(url('admin/permissions'));
    }

    public function delete(string $id): Response
    {
        $record = (new Permission())->find((int) $id);

        if ($record === null) {
            return $this->missing('Permission', 'admin/permissions');
        }

        (new Permission())->delete((int) $id);
        ActivityLogger::log(Auth::user()['name'] . ' deleted permission ' . $record['slug'] . ' from ' . Request::capture()->ip());
        flash('success', 'Permission deleted.');

        return Response::redirect(url('admin/permissions'));
    }

    private function data(Request $request): ?array
    {
        $name = trim((string) $request->input('name'));
        $slug = strtolower(trim((string) $request->input('slug')));

        if (strlen($name) < 2 || !preg_match('/^[a-z0-9.-]+$/', $slug)) {
            flash('error', 'Use a valid name and slug such as reports.view.');
            return null;
        }

        return [$name, $slug];
    }

}

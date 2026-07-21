# Project Flow Guide

This guide explains the four application layers you use most often:

```text
Route → Controller → Model → View → Response
```

## 1. Route

A route connects an HTTP method and URL to a controller method. Routes live in
`routes/web.php`.

```php
use App\Controllers\ProductController;

$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{id}', [ProductController::class, 'show']);
$router->post('/products', [ProductController::class, 'store'], [
    'auth',
    'permission:products.create',
]);
```

The first route means:

```text
When GET /products is requested, call ProductController::index().
```

The `{id}` segment is passed to the controller method:

```php
public function show(string $id): Response
{
    // $id comes from /products/{id}
}
```

Use route middleware to protect a route:

```php
['auth']
['guest']
['role:admin']
['permission:products.view']
```

## 2. Controller

A controller receives the request, validates input, asks a model for data, and
returns a view or redirect. Controllers live in `app/Controllers`.

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

final class ProductController extends Controller
{
    public function index(): Response
    {
        $products = (new Product())->all();

        return $this->view('products/index', [
            'title' => 'Products',
            'products' => $products,
        ]);
    }

    public function show(string $id): Response
    {
        $product = (new Product())->find((int) $id);

        if ($product === null) {
            flash('error', 'Product not found.');
            return Response::redirect(url('products'));
        }

        return $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
        ]);
    }

    public function store(): Response
    {
        $request = Request::capture();
        $name = trim((string) $request->input('name'));

        if (strlen($name) < 2) {
            flash('error', 'Enter a valid product name.');
            return Response::redirect(url('products/create'));
        }

        (new Product())->create($name);
        flash('success', 'Product created.');

        return Response::redirect(url('products'));
    }
}
```

Keep HTTP concerns in controllers. Do not put HTML or large SQL queries directly in
a controller.

## 3. Model

A model reads and writes database records. Models live in `app/Models` and extend
`App\Core\Model` to access the shared PDO connection.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Product extends Model
{
    public function all(): array
    {
        return $this->db()
            ->query('SELECT id, name, created_at FROM products ORDER BY id DESC')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, name, created_at FROM products WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function create(string $name): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO products (name) VALUES (:name)'
        );
        $statement->execute(['name' => $name]);

        return (int) $this->db()->lastInsertId();
    }
}
```

Always use prepared statements for request or user data.

## 4. View

A view renders the data supplied by a controller. Views live in `app/Views`.

For this controller call:

```php
return $this->view('products/index', [
    'title' => 'Products',
    'products' => $products,
]);
```

create `app/Views/products/index.php`:

```php
<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

<?php foreach ($products as $product): ?>
    <article>
        <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
    </article>
<?php endforeach; ?>
```

Escape values that may contain user-controlled content. Views should display data;
they should not run database queries.

## Complete request example

When a browser requests:

```text
GET /products/15
```

the flow is:

1. `.htaccess` sends the request to `public/index.php`.
2. The application bootstrap loads configuration, sessions, helpers, and routes.
3. The router matches `/products/{id}`.
4. The router calls `ProductController::show('15')`.
5. The controller calls `Product::find(15)`.
6. The model executes a prepared SQL query.
7. The model returns a product array to the controller.
8. The controller passes that array to `products/show.php`.
9. The view is inserted into the selected layout.
10. The generated HTML is returned in a `Response`.

```text
Browser
   ↓
routes/web.php
   ↓
ProductController::show($id)
   ↓
Product::find($id)
   ↓
MySQL
   ↓
ProductController
   ↓
app/Views/products/show.php
   ↓
HTML Response
```

## Form flow

Every POST form needs a CSRF token:

```php
<form method="post" action="<?= htmlspecialchars(url('products'), ENT_QUOTES, 'UTF-8') ?>">
    <?= csrf_field() ?>
    <input name="name" required>
    <button type="submit">Save</button>
</form>
```

The matching POST route calls the controller's `store()` method. The controller
reads input with:

```php
$request = Request::capture();
$name = $request->input('name');
```

## Choosing the correct layer

| Concern | Correct location |
|---|---|
| URL and permission middleware | `routes/web.php` |
| Request validation and redirects | Controller |
| SQL and database persistence | Model |
| HTML rendering | View |
| Shared page wrapper | Layout |
| Reusable HTML fragment | Partial |
| Application-wide behavior | `app/Core` |
| Application settings | `config/config.php` |
| Database settings | `config/database.php` |

## Checklist for a new module

1. Add its SQL table.
2. Create its model.
3. Create its controller.
4. Create its views.
5. Register GET and POST routes.
6. Create its permissions.
7. Assign permissions to roles.
8. Protect routes using `permission:module.action`.
9. Hide unauthorized UI using `Auth::can()`.
10. Add `csrf_field()` to POST forms.
11. Log meaningful changes using `ActivityLogger`.
12. Test authorized, unauthorized, validation, and empty states.

<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\HttpClient;
use Throwable;

final class ExternalApiController extends Controller
{
    public function posts(): void
    {
        $posts = [];
        $error = null;

        try {
            $response = (new HttpClient(['Accept' => 'application/json']))->get(
                'https://jsonplaceholder.typicode.com/posts',
                ['_limit' => 10]
            );

            if (!$response->successful()) {
                $error = 'JSONPlaceholder returned HTTP status ' . $response->status() . '.';
            } else {
                $posts = $response->json();
            }
        } catch (Throwable) {
            $error = 'The external API is currently unavailable. Please try again.';
        }

        $this->view('external-api/posts', [
            'title' => 'External API Demo',
            'posts' => $posts,
            'error' => $error,
        ]);
    }
}

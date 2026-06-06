<?php

declare(strict_types=1);

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
| This is an API-first project; the only "web" surface is a minimal
| landing endpoint so visiting the root of the API host returns something
| sane rather than 404. The actual UI lives in the TanStack frontend.
*/

Route::get('/', fn () => ApiResponse::success([
    'name' => config('app.name'),
    'docs' => '/api/v1/health',
]));

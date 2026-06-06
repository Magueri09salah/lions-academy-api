<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Base controller for the Lions Academy API.
 *
 * Laravel 11+ removed the AuthorizesRequests / ValidatesRequests traits
 * from the default Controller — we add them back so existing code that
 * uses $this->authorize('view', $model) / $this->validate(...) keeps
 * working in admin controllers.
 */
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;
}

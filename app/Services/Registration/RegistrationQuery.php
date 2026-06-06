<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Http\Requests\Registration\IndexRegistrationRequest;
use App\Models\Registration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds an admin-list query from validated IndexRegistrationRequest input.
 *
 * Separated from the controller so the same query can be reused by:
 *   - the JSON list endpoint
 *   - the CSV export endpoint
 *
 * Returns either a paginator (for HTTP) or a chunked Cursor (for export)
 * via the dedicated `forExport()` method.
 */
final class RegistrationQuery
{
    public function paginate(IndexRegistrationRequest $request): LengthAwarePaginator
    {
        return $this->base($request)
            ->paginate(
                perPage: $request->perPage(),
                page: (int) ($request->validated('page') ?? 1),
            )
            ->withQueryString();
    }

    /**
     * @return \Generator<int, Registration>
     */
    public function forExport(IndexRegistrationRequest $request): \Generator
    {
        // Yields rows one chunk at a time so a 100k-row export does not
        // hold the entire result set in memory.
        foreach ($this->base($request)->lazy(500) as $row) {
            yield $row;
        }
    }

    private function base(IndexRegistrationRequest $request): Builder
    {
        $statuses = $request->statusFilter();

        return Registration::query()
            ->with(['formation:id,slug,title', 'documents:id,disk,path,mime,visibility'])
            ->withCount('documents')
            ->when($request->validated('q'), fn (Builder $q, $term) => $q->search((string) $term))
            ->when($statuses !== [], fn (Builder $q) => $q->status($statuses))
            ->when(
                $request->validated('formation'),
                fn (Builder $q, $ref) => $q->formation((string) $ref),
            )
            ->submittedBetween(
                $request->validated('date_from'),
                $request->validated('date_to'),
            )
            ->orderBy($request->sortField(), $request->sortDirection())
            // Stable secondary order so paging is deterministic.
            ->orderBy('id', 'desc');
    }
}

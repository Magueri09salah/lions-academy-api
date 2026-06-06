<?php

declare(strict_types=1);

namespace App\Services\ContactMessage;

use App\Http\Requests\ContactMessage\IndexContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the admin list query from a validated IndexContactMessageRequest.
 *
 * Lives in its own service so future export endpoints (CSV / XLSX) can
 * reuse the exact same filter pipeline as the JSON list — guaranteeing
 * that "what you see is what you export".
 */
final class ContactMessageQuery
{
    public function paginate(IndexContactMessageRequest $request): LengthAwarePaginator
    {
        return $this->base($request)
            ->paginate(
                perPage: $request->perPage(),
                page: (int) ($request->validated('page') ?? 1),
            )
            ->withQueryString();
    }

    private function base(IndexContactMessageRequest $request): Builder
    {
        $statuses = $request->statusFilter();

        return ContactMessage::query()
            ->when($request->validated('q'), fn (Builder $q, $term) => $q->search((string) $term))
            ->when($statuses !== [], fn (Builder $q) => $q->status($statuses))
            ->submittedBetween(
                $request->validated('date_from'),
                $request->validated('date_to'),
            )
            ->orderBy($request->sortField(), $request->sortDirection())
            // Stable secondary order so paging is deterministic.
            ->orderBy('id', 'desc');
    }
}

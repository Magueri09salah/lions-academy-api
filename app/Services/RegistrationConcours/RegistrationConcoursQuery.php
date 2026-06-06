<?php

declare(strict_types=1);

namespace App\Services\RegistrationConcours;

use App\Http\Requests\RegistrationConcours\IndexRegistrationConcoursRequest;
use App\Models\RegistrationConcours;
use App\Support\Enums\RegistrationConcoursPriority;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the admin leads list query from a validated request.
 *
 * Also used by the CSV/XLSX export so "what you filter is what you export."
 *
 * Default sort is `-priority` then `-submitted_at` so the high-fit
 * newest leads bubble to the top automatically — matches the marketing
 * spec: "Prioriser les relances des profils ayant une note régionale
 * élevée et des filières compatibles".
 */
final class RegistrationConcoursQuery
{
    public function paginate(IndexRegistrationConcoursRequest $request): LengthAwarePaginator
    {
        return $this->base($request)
            ->paginate(
                perPage: $request->perPage(),
                page: (int) ($request->validated('page') ?? 1),
            )
            ->withQueryString();
    }

    /** @return \Generator<int, RegistrationConcours> */
    public function forExport(IndexRegistrationConcoursRequest $request): \Generator
    {
        foreach ($this->base($request)->lazy(500) as $row) {
            yield $row;
        }
    }

    private function base(IndexRegistrationConcoursRequest $request): Builder
    {
        $statuses = $request->statusFilter();
        $priorities = $request->priorityFilter();

        $sortField = $request->sortField();
        $sortDirection = $request->sortDirection();

        $q = RegistrationConcours::query()
            ->when($request->validated('q'), fn (Builder $q, $term) => $q->search((string) $term))
            ->when($statuses !== [], fn (Builder $q) => $q->status($statuses))
            ->when($priorities !== [], fn (Builder $q) => $q->priority($priorities))
            ->filiere($request->validated('filiere'))
            ->regionalGrade($request->validated('regional_grade'))
            ->format($request->validated('preferred_format'))
            ->city($request->validated('city'))
            ->submittedBetween(
                $request->validated('date_from'),
                $request->validated('date_to'),
            );

        // MySQL string-sort on the enum value gets the wrong priority order
        // (high < low alphabetically). We re-cast to a numeric ordering so
        // "high" ranks above "medium" above "low".
        if ($sortField === 'priority') {
            $q->orderByRaw(
                'FIELD(priority, ?, ?, ?) '.$sortDirection,
                [
                    RegistrationConcoursPriority::High->value,
                    RegistrationConcoursPriority::Medium->value,
                    RegistrationConcoursPriority::Low->value,
                ],
            );
        } else {
            $q->orderBy($sortField, $sortDirection);
        }

        return $q->orderBy('id', 'desc');
    }
}

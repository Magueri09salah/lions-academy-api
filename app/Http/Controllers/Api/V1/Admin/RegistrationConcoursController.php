<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationConcours\IndexRegistrationConcoursRequest;
use App\Http\Requests\RegistrationConcours\UpdateRegistrationConcoursRequest;
use App\Http\Resources\RegistrationConcoursListResource;
use App\Http\Resources\RegistrationConcoursResource;
use App\Models\RegistrationConcours;
use App\Services\RegistrationConcours\RegistrationConcoursQuery;
use App\Services\RegistrationConcours\RegistrationConcoursService;
use App\Support\ApiResponse;
use App\Support\EnaCities;
use App\Support\Enums\EnaFiliere;
use App\Support\Enums\EnaPreferredFormat;
use App\Support\Enums\EnaRegionalGrade;
use App\Support\Enums\RegistrationConcoursPriority;
use App\Support\Enums\RegistrationConcoursStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationConcoursController extends Controller
{
    public function __construct(
        private readonly RegistrationConcoursService $service,
        private readonly RegistrationConcoursQuery $query,
    ) {}

    /**
     * GET /api/v1/admin/registrations-concours
     */
    public function index(IndexRegistrationConcoursRequest $request): JsonResponse
    {
        $paginator = $this->query->paginate($request);

        return ApiResponse::success(
            data: RegistrationConcoursListResource::collection($paginator),
            meta: [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
                'filters' => [
                    'q' => $request->validated('q'),
                    'status' => array_map(fn (RegistrationConcoursStatus $s) => $s->value, $request->statusFilter()),
                    'priority' => array_map(fn (RegistrationConcoursPriority $p) => $p->value, $request->priorityFilter()),
                    'filiere' => $request->validated('filiere'),
                    'city' => $request->validated('city'),
                    'regional_grade' => $request->validated('regional_grade'),
                    'preferred_format' => $request->validated('preferred_format'),
                    'date_from' => $request->validated('date_from'),
                    'date_to' => $request->validated('date_to'),
                    'sort' => $request->validated('sort') ?? '-priority',
                ],
                'status_options' => RegistrationConcoursStatus::options(),
                'priority_options' => RegistrationConcoursPriority::options(),
                'filiere_options' => EnaFiliere::options(),
                'grade_options' => EnaRegionalGrade::options(),
                'format_options' => EnaPreferredFormat::options(),
                'city_options' => EnaCities::LIST,
                'status_counts' => $this->statusCounts(),
                'priority_counts' => $this->priorityCounts(),
            ],
        );
    }

    public function show(Request $request, RegistrationConcours $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        $lead->load('statusChangedBy');

        return ApiResponse::success(new RegistrationConcoursResource($lead));
    }

    public function update(UpdateRegistrationConcoursRequest $request, RegistrationConcours $lead): JsonResponse
    {
        $updated = $this->service->update($lead, $request->validated(), $request->user());

        return ApiResponse::success(new RegistrationConcoursResource($updated));
    }

    public function destroy(Request $request, RegistrationConcours $lead): JsonResponse
    {
        $this->authorize('delete', $lead);
        $this->service->delete($lead);

        return ApiResponse::success(['deleted' => true]);
    }

    /** @return array<string, int> */
    private function statusCounts(): array
    {
        $rows = RegistrationConcours::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $out = [];
        foreach (RegistrationConcoursStatus::cases() as $c) {
            $out[$c->value] = (int) ($rows[$c->value] ?? 0);
        }
        $out['all'] = array_sum($out);

        return $out;
    }

    /** @return array<string, int> */
    private function priorityCounts(): array
    {
        $rows = RegistrationConcours::query()
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $out = [];
        foreach (RegistrationConcoursPriority::cases() as $p) {
            $out[$p->value] = (int) ($rows[$p->value] ?? 0);
        }

        return $out;
    }
}

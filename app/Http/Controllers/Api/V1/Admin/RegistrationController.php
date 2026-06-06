<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\IndexRegistrationRequest;
use App\Http\Requests\Registration\UpdateRegistrationRequest;
use App\Http\Resources\RegistrationListResource;
use App\Http\Resources\RegistrationResource;
use App\Models\Registration;
use App\Services\Registration\RegistrationQuery;
use App\Services\Registration\RegistrationService;
use App\Support\ApiResponse;
use App\Support\Enums\RegistrationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $service,
        private readonly RegistrationQuery $query,
    ) {}

    /**
     * GET /api/v1/admin/registrations
     *
     * Filters: q, status[], formation, date_from, date_to, sort, per_page.
     * Returns a paginated list with status counters in meta so the admin
     * UI can render badges next to each status tab without a second request.
     */
    public function index(IndexRegistrationRequest $request): JsonResponse
    {
        $paginator = $this->query->paginate($request);

        return ApiResponse::success(
            data: RegistrationListResource::collection($paginator),
            meta: [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
                'filters' => [
                    'q' => $request->validated('q'),
                    'status' => array_map(fn (RegistrationStatus $s) => $s->value, $request->statusFilter()),
                    'formation' => $request->validated('formation'),
                    'date_from' => $request->validated('date_from'),
                    'date_to' => $request->validated('date_to'),
                    'sort' => $request->validated('sort') ?? '-submitted_at',
                ],
                'status_options' => RegistrationStatus::options(),
                'status_counts' => $this->statusCounts(),
            ],
        );
    }

    /**
     * GET /api/v1/admin/registrations/{registration}
     */
    public function show(Request $request, Registration $registration): JsonResponse
    {
        $this->authorize('view', $registration);

        $registration->load(['formation', 'documents', 'statusChangedBy']);

        return ApiResponse::success(new RegistrationResource($registration));
    }

    /**
     * PATCH /api/v1/admin/registrations/{registration}
     * Body: { status?: enum, admin_notes?: string|null }
     */
    public function update(UpdateRegistrationRequest $request, Registration $registration): JsonResponse
    {
        $updated = $this->service->update(
            registration: $registration,
            changes: $request->validated(),
            actor: $request->user(),
        );

        return ApiResponse::success(new RegistrationResource($updated));
    }

    /**
     * DELETE /api/v1/admin/registrations/{registration}   (admin only)
     */
    public function destroy(Request $request, Registration $registration): JsonResponse
    {
        $this->authorize('delete', $registration);

        $this->service->delete($registration);

        return ApiResponse::success(['deleted' => true]);
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $rows = Registration::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $out = [];
        foreach (RegistrationStatus::cases() as $case) {
            $out[$case->value] = (int) ($rows[$case->value] ?? 0);
        }
        $out['all'] = array_sum($out);

        return $out;
    }
}

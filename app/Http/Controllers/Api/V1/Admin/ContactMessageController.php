<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessage\IndexContactMessageRequest;
use App\Http\Requests\ContactMessage\UpdateContactMessageRequest;
use App\Http\Resources\ContactMessageListResource;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Services\ContactMessage\ContactMessageQuery;
use App\Services\ContactMessage\ContactMessageService;
use App\Support\ApiResponse;
use App\Support\Enums\ContactMessageStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $service,
        private readonly ContactMessageQuery $query,
    ) {}

    /**
     * GET /api/v1/admin/contact-messages
     * Filters: q, status[], date_from, date_to, sort, per_page.
     */
    public function index(IndexContactMessageRequest $request): JsonResponse
    {
        $paginator = $this->query->paginate($request);

        return ApiResponse::success(
            data: ContactMessageListResource::collection($paginator),
            meta: [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
                'filters' => [
                    'q' => $request->validated('q'),
                    'status' => array_map(fn (ContactMessageStatus $s) => $s->value, $request->statusFilter()),
                    'date_from' => $request->validated('date_from'),
                    'date_to' => $request->validated('date_to'),
                    'sort' => $request->validated('sort') ?? '-submitted_at',
                ],
                'status_options' => ContactMessageStatus::options(),
                'status_counts' => $this->statusCounts(),
            ],
        );
    }

    /**
     * GET /api/v1/admin/contact-messages/{message}
     *
     * Side-effect: implicitly transitions a "new" message to "read" the
     * first time it's opened by an admin. The admin can still manually
     * set it back via PATCH if needed.
     */
    public function show(Request $request, ContactMessage $message): JsonResponse
    {
        $this->authorize('view', $message);

        $message = $this->service->markRead($message, $request->user());
        $message->load('handler');

        return ApiResponse::success(new ContactMessageResource($message));
    }

    /**
     * PATCH /api/v1/admin/contact-messages/{message}
     * Body: { status?: enum, admin_notes?: string|null }
     */
    public function update(UpdateContactMessageRequest $request, ContactMessage $message): JsonResponse
    {
        $updated = $this->service->update(
            message: $message,
            changes: $request->validated(),
            actor: $request->user(),
        );

        return ApiResponse::success(new ContactMessageResource($updated));
    }

    /**
     * DELETE /api/v1/admin/contact-messages/{message}  (admin only)
     */
    public function destroy(Request $request, ContactMessage $message): JsonResponse
    {
        $this->authorize('delete', $message);

        $this->service->delete($message);

        return ApiResponse::success(['deleted' => true]);
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $rows = ContactMessage::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $out = [];
        foreach (ContactMessageStatus::cases() as $case) {
            $out[$case->value] = (int) ($rows[$case->value] ?? 0);
        }
        $out['all'] = array_sum($out);

        return $out;
    }
}

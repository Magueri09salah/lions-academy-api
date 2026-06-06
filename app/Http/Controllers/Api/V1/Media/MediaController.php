<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Requests\Media\UploadVideoRequest;
use App\Http\Resources\MediaAssetResource;
use App\Models\MediaAsset;
use App\Services\Media\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * POST /api/v1/admin/media     (admin/editor only — see routes)
     * multipart/form-data with `file`, optional `folder`, `alt`.
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $asset = $this->media->storeImage(
            file: $request->file('file'),
            folder: $request->folder(),
            uploader: $request->user(),
            alt: $request->validated('alt'),
        );

        return ApiResponse::created(new MediaAssetResource($asset));
    }

    /**
     * POST /api/v1/admin/media/videos
     *
     * Separate endpoint from /admin/media because videos have a much
     * higher size limit (200 MB) and a different MIME whitelist. Same
     * underlying MediaService → MediaAsset row → public URL.
     */
    public function storeVideo(UploadVideoRequest $request): JsonResponse
    {
        $asset = $this->media->storeVideo(
            file: $request->file('file'),
            folder: $request->folder(),
            uploader: $request->user(),
            alt: $request->validated('alt'),
        );

        return ApiResponse::created(new MediaAssetResource($asset));
    }

    /**
     * DELETE /api/v1/admin/media/{media}
     */
    public function destroy(Request $request, MediaAsset $media): JsonResponse
    {
        $this->authorizeOrFail($request, $media, 'delete');

        $this->media->delete($media);

        return ApiResponse::success(['deleted' => true]);
    }

    /**
     * GET /media/{media}    (signed) — streams a private asset.
     * Public media should use the direct `/storage/...` URL instead.
     */
    public function download(Request $request, MediaAsset $media): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        return Storage::disk($media->disk)->download(
            $media->path,
            $media->original_name ?? basename($media->path),
            ['Content-Type' => $media->mime],
        );
    }

    private function authorizeOrFail(Request $request, MediaAsset $media, string $ability): void
    {
        $user = $request->user();
        if ($user === null || $user->cannot($ability, $media)) {
            abort(403, 'Action non autorisée.');
        }
    }
}

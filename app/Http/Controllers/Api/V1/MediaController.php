<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\UploadMediaAction;
use App\DTOs\Media\UploadMediaDTO;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Resources\MediaResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * List all media files belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $media = $request->user()
            ->media()
            ->when($request->query('collection'), fn ($q, $c) => $q->where('collection', $c))
            ->latest()
            ->paginate(20);

        return ApiResponse::paginated($media, __('api.media_retrieved'));
    }

    /**
     * Upload a new file.
     */
    public function store(UploadMediaRequest $request, UploadMediaAction $action): JsonResponse
    {
        $media = $action->execute(
            file: $request->file('file'),
            user: $request->user(),
            dto: UploadMediaDTO::fromRequest($request->validated()),
        );

        return ApiResponse::created(
            new MediaResource($media),
            __('api.media_uploaded')
        );
    }

    /**
     * Get a temporary signed URL for downloading a private file.
     */
    public function url(Request $request, int $id): JsonResponse
    {
        $media = $request->user()->media()->find($id);

        if (! $media) {
            throw ApiException::notFound('Media');
        }

        return ApiResponse::success(
            [
                'url'        => $media->url(),
                'thumbnail'  => $media->thumbnailUrl(),
                'expires_in' => '60 minutes',
            ],
            __('api.media_url_generated')
        );
    }

    /**
     * Delete a media file and remove it from storage.
     */
    public function destroy(Request $request, int $id, DeleteMediaAction $action): JsonResponse
    {
        $media = $request->user()->media()->find($id);

        if (! $media) {
            throw ApiException::notFound('Media');
        }

        $action->execute($media);

        return ApiResponse::noContent(__('api.media_deleted'));
    }
}

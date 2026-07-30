<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Export\CreateExportAction;
use App\DTOs\Export\CreateExportDTO;
use App\Enums\ExportFormat;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Export\StoreExportRequest;
use App\Http\Resources\ExportResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * List all exports for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $exports = $request->user()
            ->exports()
            ->with('media')
            ->latest()
            ->paginate(20);

        return ApiResponse::paginated($exports, __('api.export_retrieved'));
    }

    /**
     * Trigger a new background export.
     * Returns 202 Accepted immediately — the file is generated asynchronously.
     *
     * Admin-only resources require the admin role.
     */
    public function store(StoreExportRequest $request, CreateExportAction $action): JsonResponse
    {
        // Guard admin-only resources.
        if ($request->resourceIsAdminOnly() && ! $request->user()->hasRole('admin')) {
            throw ApiException::forbidden('This export resource requires admin privileges.');
        }

        $export = $action->execute(
            CreateExportDTO::fromRequest($request->validated(), $request->user()->id)
        );

        return ApiResponse::accepted(
            __('api.export_queued'),
            new ExportResource($export->load('media'))
        );
    }

    /**
     * Get the status and download URL of a specific export.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $export = $request->user()
            ->exports()
            ->with('media')
            ->find($id);

        if (! $export) {
            throw ApiException::notFound('Export');
        }

        return ApiResponse::success(
            new ExportResource($export),
            $export->isCompleted()
                ? __('api.export_ready')
                : __('api.export_not_ready')
        );
    }

    /**
     * List all available exportable resources and formats.
     */
    public function resources(): JsonResponse
    {
        $resources = collect(config('export.resources', []))
            ->map(fn ($class, $key) => [
                'key'          => $key,
                'label'        => app($class)->label(),
                'admin_only'   => app($class)->isAdminOnly(),
            ])
            ->values();

        $formats = collect(ExportFormat::cases())->map(fn ($f) => $f->value);

        return ApiResponse::success(
            ['resources' => $resources, 'formats' => $formats],
            __('api.export_resources_listed')
        );
    }
}

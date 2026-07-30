<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Import\CreateImportAction;
use App\DTOs\Import\CreateImportDTO;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Import\StoreImportRequest;
use App\Http\Resources\ImportResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * List all imports for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $imports = $request->user()
            ->imports()
            ->with('media')
            ->latest()
            ->paginate(20);

        return ApiResponse::paginated($imports, __('api.import_retrieved'));
    }

    /**
     * Trigger a new background data import.
     * Returns 202 Accepted immediately.
     */
    public function store(StoreImportRequest $request, CreateImportAction $action): JsonResponse
    {
        // Guard admin-only resources
        if ($request->resourceIsAdminOnly() && ! $request->user()->hasRole('admin')) {
            throw ApiException::forbidden('This import resource requires admin privileges.');
        }

        $import = $action->execute(
            file: $request->file('file'),
            user: $request->user(),
            dto: CreateImportDTO::fromRequest($request->validated(), $request->user()->id)
        );

        return ApiResponse::accepted(
            __('api.import_queued'),
            new ImportResource($import->load('media'))
        );
    }

    /**
     * Get the details, progress, and errors of a specific import.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $import = $request->user()
            ->imports()
            ->with('media')
            ->find($id);

        if (! $import) {
            throw ApiException::notFound('Import');
        }

        return ApiResponse::success(new ImportResource($import));
    }

    /**
     * List all available importable resources.
     */
    public function resources(): JsonResponse
    {
        $resources = collect(config('import.resources', []))
            ->map(fn ($class, $key) => [
                'key'        => $key,
                'label'      => app($class)->label(),
                'admin_only' => app($class)->isAdminOnly(),
            ])
            ->values();

        return ApiResponse::success(
            ['resources' => $resources],
            __('api.import_resources_listed')
        );
    }
}

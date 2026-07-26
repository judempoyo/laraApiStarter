<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\V1\User;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePreferenceRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /**
     * Return all preferences for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $preferences = $request->user()
            ->preferences()
            ->orderBy('key')
            ->get(['key', 'value', 'updated_at'])
            ->mapWithKeys(fn($pref) => [$pref->key => $pref->value]);

        return ApiResponse::success($preferences, 'Preferences retrieved successfully.');
    }

    /**
     * Set or update a preference by key.
     */
    public function set(UpdatePreferenceRequest $request, string $key): JsonResponse
    {

        $request->user()->preferences()->updateOrCreate(
            ['key' => $key],
            ['value' => $request->input('value')]
        );

        return ApiResponse::success(
            [$key => $request->input('value')],
            "Preference '{$key}' saved."
        );
    }

    /**
     * Delete a preference by key.
     */
    public function destroy(Request $request, string $key): JsonResponse
    {
        $deleted = $request->user()->preferences()->where('key', $key)->delete();

        if (! $deleted) {
            throw ApiException::notFound("Preference '{$key}'");
        }

        return ApiResponse::noContent("Preference '{$key}' deleted.");
    }
}

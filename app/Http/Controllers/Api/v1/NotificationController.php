<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CustomNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marquée comme lue.',
        ]);
    }

    /**
     * Store a newly created notification
     *
     * Creates and sends a notification to a specific user or broadcast.
     *
     * @authenticated
     *
     * @response 201 scenario="Notification created" {
     *   "message": "Notification created successfully."
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'string', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'data' => ['nullable', 'array'],
        ]);

        $user = isset($validated['user_id'])
            ? User::findOrFail($validated['user_id'])
            : $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        /** @var User $user */
        $user->notify(new CustomNotification(
            $validated['title'],
            $validated['message'],
            $validated['type'] ?? 'info',
            $validated['data'] ?? []
        ));

        return response()->json([
            'message' => 'Notification créée avec succès.',
        ], 201);
    }

    /**
     * Update the specified notification
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // TODO: Implement notification update
        return response()->json([
            'message' => 'Not implemented yet',
        ], 501);
    }

    /**
     * Remove the specified notification
     */
    public function destroy(string $id): JsonResponse
    {
        // TODO: Implement notification deletion
        return response()->json([
            'message' => 'Not implemented yet',
        ], 501);
    }
}

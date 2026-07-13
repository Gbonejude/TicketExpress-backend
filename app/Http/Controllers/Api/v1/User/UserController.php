<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Actions\V1\User\CreateUserAction;
use App\Actions\V1\User\DeleteUserAction;
use App\Actions\V1\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\StoreRequest;
use App\Http\Requests\v1\User\UpdateRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * @group User management
 *
 * APIs for managing users
 */
final class UserController extends Controller
{
    /**
     * List Users
     *
     * Get a listing of the users.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel \App\Models\User
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return UserResource::collection($users);
    }

    /**
     * Store User
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "User created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "email": "user@example.com",
     *     "lastName": "Doe",
     *     "firstName": "John"
     *   }
     * }
     */
    public function store(StoreRequest $request, CreateUserAction $action): JsonResponse
    {
        /** @var array{last_name: string, first_name: string, phone: string, gender: string, role: string, email?: string|null, password?: string|null, address?: string|null, birthday?: string|null, organization_id?: string|null, image?: UploadedFile|null} $data */
        $data = $request->validated();
        $user = $action->execute($data);

        return $this->created(new UserResource($user));
    }

    /**
     * Show User
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam user string required The ID of the user (ULID)
     *
     * @apiResource \App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel \App\Models\User
     */
    public function show(User $id): JsonResponse
    {
        return $this->success(new UserResource($id));
    }

    /**
     * Update User
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam user string required The ID of the user (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "User updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "email": "user@example.com"
     *   }
     * }
     */
    public function update(UpdateRequest $request, User $id, UpdateUserAction $action): JsonResponse
    {
        /** @var array{user: User, last_name?: string, first_name?: string, phone?: string, gender?: string, role?: string, email?: string|null, password?: string|null, address?: string|null, birthday?: string|null, organization_id?: string|null, image?: UploadedFile|null} $data */
        $data = [
            'user' => $id,
            ...$request->validated(),
        ];
        $user = $action->execute($data);

        return $this->success(new UserResource($user));
    }

    /**
     * Delete User
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam user string required The ID of the user (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(User $id, DeleteUserAction $action): JsonResponse
    {
        $action->execute(['user' => $id]);

        return $this->noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\V1\Auth\AdminLoginAction;
use App\Actions\V1\Auth\ForgotPasswordAction;
use App\Actions\V1\Auth\IssueTokenAction;
use App\Actions\V1\Auth\LogoutAction;
use App\Actions\V1\Auth\RegisterClientAction;
use App\Actions\V1\Auth\RegisterOrganizerManagerAction;
use App\Actions\V1\Auth\RegisterUserAction;
use App\Actions\V1\Auth\SendOtpAction;
use App\Actions\V1\Auth\VerifyOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\AdminLoginRequest;
use App\Http\Requests\V1\Auth\CompleteRegistrationRequest;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\RegisterClientRequest;
use App\Http\Requests\V1\Auth\RegisterOrganizerManagerRequest;
use App\Http\Requests\V1\Auth\SendOtpRequest;
use App\Http\Requests\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * @group Authentication
 *
 * Handles user authentication via OTP, registration, admin login, and logout.
 */
final class AuthController extends Controller
{
    /**
     * Send OTP
     *
     * Generates and sends an OTP code to the provided phone number.
     * Used for both login and registration flows.
     *
     * @unauthenticated
     *
     * @header X-Device-Name mobile
     *
     * @response 200 scenario="OTP sent" {
     *   "message": "OTP sent successfully."
     * }
     * @response 429 scenario="Cooldown active" {
     *   "message": "Please wait 60 seconds before requesting a new OTP."
     * }
     */
    public function sendOtp(SendOtpRequest $request, SendOtpAction $action): JsonResponse
    {
        $action->execute(['phone' => $request->phone]);

        return $this->success(message: 'OTP sent successfully.');
    }

    /**
     * Verify OTP
     *
     * Verifies the OTP code received by the user.
     *
     * - If the phone number is **new** → returns `is_new_user: true` and the phone
     *   number to redirect the user to the registration flow.
     * - If the phone number is **known** → returns an access token and user data.
     *
     * @unauthenticated
     *
     * @header X-Device-Name mobile
     *
     * @response 200 scenario="Existing user" {
     *   "is_new_user": false,
     *   "token": "1|abc123...",
     *   "user": {"id": 1, "first_name": "Simon", "phone": "+22890000000"}
     * }
     * @response 200 scenario="New user" {
     *   "is_new_user": true,
     *   "phone": "+22890000000",
     *   "message": "Phone verified. Please complete your registration."
     * }
     * @response 422 scenario="Invalid or expired OTP" {
     *   "message": "Invalid or expired OTP code."
     * }
     * @response 422 scenario="Wrong OTP code" {
     *   "message": "Incorrect OTP code. 4 attempt(s) remaining."
     * }
     */
    public function verifyOtp(
        VerifyOtpRequest $request,
        VerifyOtpAction $verify,
        IssueTokenAction $issueToken,
    ): JsonResponse {
        $result = $verify->execute([
            'phone' => $request->phone,
            'code' => $request->code,
        ]);

        if ($result['is_new_user']) {
            return $this->success(
                ['is_new_user' => true, 'phone' => $result['phone']],
                'Phone verified. Please complete your registration.',
            );
        }

        $token = $issueToken->execute([
            'user' => $result['user'],
            'device_name' => $request->header('X-Device-Name', 'mobile'),
        ]);

        return $this->success([
            'is_new_user' => false,
            'token' => $token,
            'user' => new UserResource($result['user']),
        ], 'Login successful.');
    }

    /**
     * Complete Registration
     *
     * Finalizes account creation after OTP verification.
     * The phone number must have been verified within the last 15 minutes.
     *
     * @unauthenticated
     *
     * @header X-Device-Name mobile
     *
     * @response 201 scenario="Account created" {
     *   "token": "2|xyz789...",
     *   "user": {"id": 2, "first_name": "Simon", "last_name": "Dev", "phone": "+22890000000"},
     *   "message": "Account created successfully."
     * }
     * @response 422 scenario="OTP not verified or session expired" {
     *   "message": "Phone number not verified or session has expired."
     * }
     * @response 409 scenario="Account already exists" {
     *   "message": "An account already exists for this phone number."
     * }
     */
    public function register(
        CompleteRegistrationRequest $request,
        RegisterUserAction $registerUser,
        IssueTokenAction $issueToken,
    ): JsonResponse {
        /** @var array{phone: string, first_name: string, last_name: string, email?: string|null, address?: string|null, birthday?: string|null, gender?: string|null, image?: UploadedFile|null} $data */
        $data = $request->validated();
        $user = $registerUser->execute($data);

        $token = $issueToken->execute([
            'user' => $user,
            'device_name' => $request->header('X-Device-Name', 'mobile'),
        ]);

        return $this->created([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Account created successfully.');
    }

    /**
     * Admin Login
     *
     * Authenticates an administrator using email and password.
     * Returns an access token along with permission rules.
     *
     * @unauthenticated
     *
     * @response 200 scenario="Login successful" {
     *   "accessToken": "3|admin123...",
     *   "userData": {"id": 1, "first_name": "Admin", "email": "admin@agoo.tg"},
     *   "userAbilityRules": [{"action": "manage", "subject": "all"}],
     *   "message": "Login successful."
     * }
     * @response 401 scenario="Invalid credentials" {
     *   "message": "Invalid credentials."
     * }
     */
    public function adminLogin(
        AdminLoginRequest $request,
        AdminLoginAction $login,
        IssueTokenAction $issueToken,
    ): JsonResponse {
        /** @var array{email: string, password: string} $credentials */
        $credentials = $request->validated();
        $user = $login->execute($credentials);

        $token = $issueToken->execute([
            'user' => $user,
            'device_name' => 'dashboard',
        ]);

        return $this->success([
            'accessToken' => $token,
            'userData' => new UserResource($user),
            'userAbilityRules' => [['action' => 'manage', 'subject' => 'all']],
        ], 'Login successful.');
    }

    /**
     * Register Client
     *
     * Creates a new client account with password directly.
     * No OTP verification required for this registration flow.
     *
     * @unauthenticated
     *
     * @response 201 scenario="Client account created" {
     *   "success": true,
     *   "message": "Compte client créé avec succès.",
     *   "data": {
     *     "user": {
     *       "id": "01J...",
     *       "firstName": "Jean",
     *       "lastName": "Dupont",
     *       "fullName": "Jean Dupont",
     *       "email": "jean.dupont@example.com",
     *       "phone": "+22890200001"
     *     }
     *   }
     * }
     * @response 422 scenario="Validation error" {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": ["Cette adresse email est déjà utilisée."]
     *   }
     * }
     */
    public function registerClient(
        RegisterClientRequest $request,
        RegisterClientAction $action,
    ): JsonResponse {
        /** @var array{first_name: string, last_name: string, email: string, phone: string, password: string} $data */
        $data = $request->validated();
        $user = $action->execute($data);

        return $this->created([
            'user' => new UserResource($user),
        ], 'Compte client créé avec succès.');
    }

    /**
     * Register Organizer Manager
     *
     * Creates a new organizer manager account with password.
     * The organizer will be created with 'pending' status awaiting admin approval.
     *
     * @unauthenticated
     *
     * @response 201 scenario="Organizer manager account created" {
     *   "success": true,
     *   "message": "Compte organisateur créé avec succès. En attente d'approbation.",
     *   "data": {
     *     "user": {
     *       "id": "01J...",
     *       "firstName": "Marie",
     *       "lastName": "Martin",
     *       "fullName": "Marie Martin",
     *       "email": "marie.martin@example.com",
     *       "phone": "+22890300001",
     *       "organizer": {
     *         "id": "01J...",
     *         "companyName": "EventPro Togo",
     *         "status": "pending"
     *       }
     *     }
     *   }
     * }
     * @response 422 scenario="Validation error" {
     *   "message": "The phone has already been taken.",
     *   "errors": {
     *     "phone": ["Ce numéro de téléphone est déjà utilisé."]
     *   }
     * }
     */
    public function registerOrganizerManager(
        RegisterOrganizerManagerRequest $request,
        RegisterOrganizerManagerAction $action,
    ): JsonResponse {
        /** @var array{first_name: string, last_name: string, email: string, phone: string, password: string, company_name: string, description?: string|null, website?: string|null} $data */
        $data = $request->validated();
        $user = $action->execute($data);

        return $this->created([
            'user' => new UserResource($user),
        ], 'Compte organisateur créé avec succès. En attente d\'approbation.');
    }

    /**
     * Logout
     *
     * Revokes the current access token of the authenticated user.
     *
     * @authenticated
     *
     * @response 200 scenario="Logout successful" {
     *   "message": "Logged out successfully."
     * }
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute(['user' => $request->user()]);

        return $this->success(message: 'Logged out successfully.');
    }

    /**
     * Forgot Password
     *
     * Sends a password reset link to the user's email address.
     *
     * @unauthenticated
     *
     * @response 200 scenario="Reset link sent" {
     *   "message": "Password reset link sent successfully."
     * }
     */
    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute(['email' => $request->validated('email')]);

        return $this->success(message: 'Password reset link sent successfully.');
    }
}

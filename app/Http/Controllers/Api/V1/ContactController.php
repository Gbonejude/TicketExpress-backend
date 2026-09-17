<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Contact\StoreContactRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

/**
 * @group Contact
 *
 * Public support form.
 */
final class ContactController extends Controller
{
    /**
     * Send a contact message
     *
     * Delivers the public contact form to the support inbox. Nothing is
     * persisted: the message is an e-mail, and the reply happens in the inbox.
     *
     * @header Accept-Language fr
     *
     * @response 200 scenario="Sent" {
     *   "success": true,
     *   "message": "Message envoyé. Notre équipe vous répondra rapidement.",
     *   "data": null
     * }
     * @response 422 scenario="Invalid" {
     *   "success": false,
     *   "message": "The given data was invalid.",
     *   "errors": {"email": ["L'adresse e-mail n'est pas valide."]}
     * }
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        /** @var array{name: string, email: string, phone?: string|null, subject: string, message: string} $data */
        $data = $request->validated();

        // Falls back to the application's own from-address: a support inbox is
        // deployment configuration, not something to hard-code here.
        $recipient = (string) config('mail.contact_address', config('mail.from.address'));

        Mail::to($recipient)->send(new ContactMessageMail(
            senderName: $data['name'],
            senderEmail: $data['email'],
            senderPhone: (string) ($data['phone'] ?? ''),
            subjectLine: $data['subject'],
            body: $data['message'],
        ));

        return $this->success(
            message: 'Message envoyé. Notre équipe vous répondra rapidement.',
        );
    }
}

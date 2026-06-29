<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Contracts\Auth\OtpSender;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;

class EdokingOtpSender implements OtpSender
{
    public function send(string $phone, string $code): void
    {
        $client = new Client;

        $senderName = config('edoking.sender_name', 'KWEEK');
        $normalizedPhone = ltrim($phone, '+');

        try {
            $response = $client->post(
                uri: config('edoking.url'),
                options: [
                    'headers' => [
                        'APIKEY' => config('edoking.key'),
                        'CLIENTID' => config('edoking.client_id'),
                    ],
                    'form_params' => [
                        'from' => $senderName,
                        'to' => $normalizedPhone,
                        'type' => 1,
                        'message' => "Votre code OTP est {$code}. Il expire dans 5 minutes.",
                        'dlr' => 1,
                    ],
                    'timeout' => 30,
                ],
            );

            if ($response->getStatusCode() !== JsonResponse::HTTP_CREATED) {
                throw new ApiException('Failed to send OTP via SMS.', 500);
            }

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('SMS service unavailable. Please try again.', 503);
        }
    }
}

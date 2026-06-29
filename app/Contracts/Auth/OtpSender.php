<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

/**
 * Contrat pour l'envoi de codes OTP.
 * Implémenter cette interface pour switcher entre WhatsApp, SMS, email, etc.
 */
interface OtpSender
{
    /**
     * Envoyer le code OTP au numéro de téléphone fourni.
     */
    public function send(string $phone, string $code): void;
}

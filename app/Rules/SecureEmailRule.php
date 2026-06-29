<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les emails sécurisés
 *
 * Critères :
 * - Format email valide
 * - Vérification DNS du domaine
 * - Pas d'emails temporaires
 * - Pas de caractères suspects
 */
final class SecureEmailRule implements ValidationRule
{
    public function __construct(
        private readonly bool $checkDns = true,
        private readonly bool $rejectTemporaryEmails = true,
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("Le champ {$attribute} doit être une chaîne de caractères.");

            return;
        }

        $email = mb_trim($value);

        // Vérifier le format de base
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail("Le champ {$attribute} doit être une adresse email valide.");

            return;
        }

        // Vérifier la longueur
        if (mb_strlen($email) > 254) {
            $fail("L'adresse email ne peut pas dépasser 254 caractères.");

            return;
        }

        // Vérifier les caractères suspects
        if ($this->containsSuspiciousCharacters($email)) {
            $fail("L'adresse email contient des caractères suspects.");

            return;
        }

        // Vérifier les emails temporaires
        if ($this->rejectTemporaryEmails && $this->isTemporaryEmail($email)) {
            $fail('Les adresses email temporaires ne sont pas autorisées.');

            return;
        }

        // Vérifier le DNS du domaine
        if ($this->checkDns && ! $this->hasValidDns($email)) {
            $fail("Le domaine de l'adresse email n'existe pas ou n'est pas accessible.");

            return;
        }
    }

    /**
     * Vérifie la présence de caractères suspects
     */
    private function containsSuspiciousCharacters(string $email): bool
    {
        $suspiciousPatterns = [
            '/[<>]/',                    // Balises HTML
            '/javascript/i',             // JavaScript
            '/vbscript/i',              // VBScript
            '/data:/i',                 // Data URI
            '/javascript:/i',            // JavaScript URI
            '/vbscript:/i',             // VBScript URI
            '/on\w+\s*=/i',            // Event handlers
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie la validité DNS du domaine
     */
    private function hasValidDns(string $email): bool
    {
        // Skip DNS check in local/testing environment
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        $atPosition = mb_strrchr($email, '@');
        if ($atPosition === false) {
            return false;
        }

        $domain = mb_substr($atPosition, 1);
        if ($domain === '' || $domain === '0') {
            return false;
        }

        // Vérifier les enregistrements MX
        $mxRecords = [];
        $result = getmxrr($domain, $mxRecords);

        if ($result && ! empty($mxRecords)) {
            return true;
        }

        // Si pas de MX, vérifier les enregistrements A
        $aRecords = gethostbyname($domain);

        return $aRecords !== $domain; // Si différent, le domaine existe
    }

    /**
     * Vérifie si l'email est temporaire
     */
    private function isTemporaryEmail(string $email): bool
    {
        $atPosition = mb_strrchr($email, '@');
        if ($atPosition === false) {
            return false;
        }

        $domain = mb_substr($atPosition, 1);
        if ($domain === '' || $domain === '0') {
            return false;
        }

        $temporaryDomains = [
            '10minutemail.com',
            'tempmail.org',
            'guerrillamail.com',
            'mailinator.com',
            'yopmail.com',
            'temp-mail.org',
            'throwaway.email',
            'getnada.com',
            'maildrop.cc',
            'tempail.com',
            'sharklasers.com',
            'guerrillamailblock.com',
            'pokemail.net',
            'spam4.me',
            'bccto.me',
            'chacuo.net',
            'dispostable.com',
            'mailnesia.com',
            'meltmail.com',
            'mohmal.com',
            'mytrashmail.com',
            'nada.email',
            'nada.ltd',
            'nada.pro',
            'nada.email',
            'nada.ltd',
            'nada.pro',
            'nada.email',
            'nada.ltd',
            'nada.pro',
        ];

        return in_array(mb_strtolower($domain), $temporaryDomains);
    }
}

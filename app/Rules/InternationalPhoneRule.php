<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les numéros de téléphone internationaux
 *
 * Format accepté : E.164
 * - +[indicatif pays][numéro] (ex: +33612345678, +228 90123456, +1234567890)
 * - Longueur totale : 8 à 16 caractères (incluant le +)
 */
final class InternationalPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que la valeur n'est pas null
        if ($value === null) {
            $fail('Le numéro de téléphone ne peut pas être vide.');

            return;
        }

        // Vérifier que la valeur est une chaîne
        if (! is_string($value)) {
            $fail('Le numéro de téléphone doit être une chaîne de caractères.');

            return;
        }

        // Vérifier que la chaîne n'est pas vide
        if (empty(mb_trim($value))) {
            $fail('Le numéro de téléphone ne peut pas être vide.');

            return;
        }

        // Nettoyer le numéro (supprimer espaces, tirets, parenthèses)
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $value);

        // Vérifier que le numéro ne contient que des chiffres et le signe +
        if (! preg_match('/^[\d\+]+$/', $cleanPhone)) {
            $fail('Le numéro de téléphone ne peut contenir que des chiffres et le signe +.');

            return;
        }

        // Format E.164 : +[country code 1-3 digits][number 4-14 digits]
        // Total: 7 à 16 caractères (+ inclus)
        // Exemple: +228 90123456 (Togo), +33 612345678 (France), +1 2025551234 (USA)
        $pattern = '/^\+[1-9]\d{6,14}$/';

        if (! preg_match($pattern, $cleanPhone)) {
            $fail('Le numéro de téléphone doit être au format international (+indicatif pays suivi du numéro).');

            return;
        }

        // Vérifications additionnelles de sécurité
        // 1. Le numéro ne doit pas contenir de séquences suspectes
        if ($this->containsSuspiciousPatterns($cleanPhone)) {
            $fail('Le numéro de téléphone contient des caractères invalides.');

            return;
        }

        // 2. Le numéro ne doit pas être trop court ou trop long
        $length = mb_strlen($cleanPhone);
        if ($length < 8 || $length > 16) {
            $fail('Le numéro de téléphone doit contenir entre 8 et 16 caractères.');
        }
    }

    /**
     * Vérifie si le numéro contient des patterns suspects
     */
    private function containsSuspiciousPatterns(string $phone): bool
    {
        // Vérifier les séquences répétitives excessives (ex: +00000000000)
        if (preg_match('/(\d)\1{9,}/', $phone)) {
            return true;
        }

        // Vérifier les séquences croissantes/décroissantes trop longues
        if (preg_match('/(?:0123456789|9876543210)/', $phone)) {
            return true;
        }

        return false;
    }
}

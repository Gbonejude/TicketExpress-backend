<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les numéros de téléphone du Togo
 *
 * Formats acceptés :
 * - +228XXXXXXXX (international)
 * - 0XXXXXXXX (national)
 * - XXXXXXXXX (sans préfixe)
 */
final class TogoPhoneRule implements ValidationRule
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

        // Formats acceptés pour le Togo
        $patterns = [
            '/^\+228[0-9]{8}$/',           // +228XXXXXXXX (international)
            '/^0[0-9]{8}$/',               // 0XXXXXXXX (national)
            '/^[0-9]{8}$/',               // XXXXXXXX (sans préfixe)
        ];

        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                $isValid = true;
                break;
            }
        }

        if (! $isValid) {
            $fail('Le numéro de téléphone doit être au format togolais valide (+228XXXXXXXX, 0XXXXXXXX, ou XXXXXXXX).');

            return;
        }

        // Vérifier que le numéro commence par un préfixe valide du Togo
        $nationalNumber = $this->extractNationalNumber($cleanPhone);
        if (! $this->isValidTogoPrefix($nationalNumber)) {
            $fail('Le numéro de téléphone doit commencer par un préfixe valide du Togo (70, 71, 72, 76, 77, 78, 79, 90, 91, 92, 93, 96, 97, 98, 99, 22, 23).');
        }
    }

    /**
     * Extrait le numéro national (sans préfixe international)
     */
    private function extractNationalNumber(string $phone): string
    {
        // Si format international +228XXXXXXXX
        if (str_starts_with($phone, '+228')) {
            return mb_substr($phone, 4); // Retourne XXXXXXXX
        }

        // Si format national 0XXXXXXXX
        if (str_starts_with($phone, '0')) {
            return mb_substr($phone, 1); // Retourne XXXXXXXX
        }

        // Si format XXXXXXXX
        return $phone;
    }

    /**
     * Vérifie si le préfixe est valide pour le Togo
     */
    private function isValidTogoPrefix(string $nationalNumber): bool
    {
        $validPrefixes = [
            '70', '71', '72', '76', '77', '78', '79',
            '90', '91', '92', '93', '96', '97', '98', '99',
            '22', '23',
        ];

        foreach ($validPrefixes as $prefix) {
            if (str_starts_with($nationalNumber, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

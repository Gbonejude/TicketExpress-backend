<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les mots de passe sécurisés
 *
 * Critères de sécurité :
 * - Minimum 8 caractères
 * - Au moins 1 majuscule
 * - Au moins 1 minuscule
 * - Au moins 1 chiffre
 * - Au moins 1 caractère spécial
 * - Pas de mots de passe communs
 */
final class SecurePasswordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que la valeur n'est pas null
        if ($value === null) {
            $fail('Le mot de passe ne peut pas être vide.');

            return;
        }

        // Vérifier que la valeur est une chaîne
        if (! is_string($value)) {
            $fail('Le mot de passe doit être une chaîne de caractères.');

            return;
        }

        $password = $value;

        // Vérifier que la chaîne n'est pas vide
        if (empty(mb_trim($password))) {
            $fail('Le mot de passe ne peut pas être vide.');

            return;
        }

        // Vérifier la longueur minimale
        if (mb_strlen($password) < 8) {
            $fail('Le mot de passe doit contenir au moins 8 caractères.');

            return;
        }

        // Vérifier la longueur maximale (éviter les attaques par déni de service)
        if (mb_strlen($password) > 128) {
            $fail('Le mot de passe ne peut pas dépasser 128 caractères.');

            return;
        }

        // Vérifier la présence d'au moins une majuscule
        if (! preg_match('/[A-Z]/', $password)) {
            $fail('Le mot de passe doit contenir au moins une lettre majuscule.');

            return;
        }

        // Vérifier la présence d'au moins une minuscule
        if (! preg_match('/[a-z]/', $password)) {
            $fail('Le mot de passe doit contenir au moins une lettre minuscule.');

            return;
        }

        // Vérifier la présence d'au moins un chiffre
        if (! preg_match('/[0-9]/', $password)) {
            $fail('Le mot de passe doit contenir au moins un chiffre.');

            return;
        }

        // Vérifier la présence d'au moins un caractère spécial
        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $fail('Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()_+-=[]{}|;:,.<>?).');

            return;
        }

        // Vérifier qu'il n'y a pas de caractères non-ASCII (sécurité)
        if (! mb_check_encoding($password, 'ASCII')) {
            $fail('Le mot de passe ne peut contenir que des caractères ASCII.');

            return;
        }

        // Vérifier contre les mots de passe communs
        if ($this->isCommonPassword($password)) {
            $fail('Ce mot de passe est trop commun. Veuillez choisir un mot de passe plus unique.');

            return;
        }

        // Vérifier les séquences répétitives
        if ($this->hasRepeatingPatterns($password)) {
            $fail('Le mot de passe ne doit pas contenir de séquences répétitives (ex: 111, aaa, abcabc).');

            return;
        }
    }

    /**
     * Vérifie la présence de séquences répétitives
     */
    private function hasRepeatingPatterns(string $password): bool
    {
        // Vérifier les répétitions de caractères (3+ fois)
        if (preg_match('/(.)\1{2,}/', $password)) {
            return true;
        }

        // Vérifier les séquences numériques répétitives (123123, 321321, etc.)
        if (preg_match('/(123|321|456|654|789|987)\1/', $password)) {
            return true;
        }

        // Vérifier les séquences alphabétiques répétitives (abcabc, cbacba, etc.)
        if (preg_match('/(abc|cba|def|fed|ghi|ihg)\1/', $password)) {
            return true;
        }

        // Vérifier les séquences de clavier répétitives (qweqwe, asdasd, etc.)
        return (bool) (preg_match('/(qwe|ewq|asd|dsa|zxc|cxz)\1/', $password));
    }

    /**
     * Vérifie si le mot de passe fait partie des mots de passe communs
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password',
            '123456',
            '123456789',
            'qwerty',
            'abc123',
            'password123',
            'admin',
            'letmein',
            'welcome',
            'monkey',
            '1234567890',
            'password1',
            'qwerty123',
            'dragon',
            'master',
            'hello',
            'freedom',
            'whatever',
            'qazwsx',
            'trustno1',
            '654321',
            'jordan23',
            'harley',
            'password123',
            'fuckyou',
            '123123',
            'senha',
            'pussy',
            'dickhead',
            'biteme',
            'shadow',
            'monkey',
            'master',
            'hello',
            'freedom',
            'whatever',
            'qazwsx',
            'trustno1',
            'dragon',
            'passw0rd',
            'superman',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
            '1q2w3e4r',
            '1qaz2wsx',
            'zaq1xsw2',
            'qwerty123',
            'password1',
            '1234567890',
            '123456789',
            '12345678',
            '1234567',
            '123456',
            '12345',
            '1234',
            '123',
            'togo',
            'lome',
            'africa',
            'togolais',
            'togolaise',
        ];

        return in_array(mb_strtolower($password), $commonPasswords);
    }
}

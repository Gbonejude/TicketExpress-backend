<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour détecter et bloquer les tentatives XSS
 *
 * Détecte :
 * - Scripts JavaScript
 * - Tags HTML malveillants
 * - Encodages suspects
 * - Caractères de contournement
 */
final class NoXssRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("Le champ {$attribute} doit être une chaîne de caractères.");

            return;
        }

        $input = $value;

        // Vérifier les scripts JavaScript
        if ($this->containsJavaScript($input)) {
            $fail("Le champ {$attribute} ne peut pas contenir de code JavaScript.");

            return;
        }

        // Vérifier les tags HTML malveillants
        if ($this->containsMaliciousHtml($input)) {
            $fail("Le champ {$attribute} ne peut pas contenir de balises HTML malveillantes.");

            return;
        }

        // Vérifier les encodages suspects
        if ($this->containsSuspiciousEncoding($input)) {
            $fail("Le champ {$attribute} contient des caractères encodés suspects.");

            return;
        }

        // Vérifier les tentatives de contournement
        if ($this->containsBypassAttempts($input)) {
            $fail("Le champ {$attribute} contient des tentatives de contournement de sécurité.");

            return;
        }

        // Vérifier les caractères de contrôle
        if ($this->containsControlCharacters($input)) {
            $fail("Le champ {$attribute} ne peut pas contenir de caractères de contrôle.");

            return;
        }
    }

    /**
     * Détecte les tentatives de contournement
     */
    private function containsBypassAttempts(string $input): bool
    {
        $bypassPatterns = [
            '/script\s*>/i',          // script> sans <
            '/<script/i',             // <script
            '/<\/script/i',           // </script
            '/javascript/i',          // javascript
            '/vbscript/i',           // vbscript
            '/onload/i',              // onload
            '/onerror/i',             // onerror
            '/onclick/i',             // onclick
            '/onmouseover/i',         // onmouseover
            '/onfocus/i',             // onfocus
            '/onblur/i',              // onblur
            '/onchange/i',            // onchange
            '/onsubmit/i',            // onsubmit
            '/onreset/i',             // onreset
            '/onselect/i',            // onselect
            '/onkeydown/i',           // onkeydown
            '/onkeyup/i',             // onkeyup
            '/onkeypress/i',          // onkeypress
            '/onmousedown/i',         // onmousedown
            '/onmouseup/i',           // onmouseup
            '/onmousemove/i',         // onmousemove
            '/onmouseout/i',          // onmouseout
            '/ondblclick/i',          // ondblclick
            '/oncontextmenu/i',       // oncontextmenu
            '/onresize/i',            // onresize
            '/onscroll/i',            // onscroll
            '/onunload/i',            // onunload
            '/onbeforeunload/i',      // onbeforeunload
            '/onabort/i',             // onabort
            '/onerror/i',             // onerror
            '/onload/i',              // onload
            '/onbeforeprint/i',       // onbeforeprint
            '/onafterprint/i',        // onafterprint
        ];

        foreach ($bypassPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les caractères de contrôle
     */
    private function containsControlCharacters(string $input): bool
    {
        // Vérifier les caractères de contrôle (ASCII 0-31, sauf tab, line feed, carriage return)
        for ($i = 0; $i < mb_strlen($input); $i++) {
            $char = $input[$i];
            $ascii = ord($char);

            // Caractères de contrôle interdits (sauf tab=9, LF=10, CR=13)
            if ($ascii < 32 && ! in_array($ascii, [9, 10, 13])) {
                return true;
            }

            // Caractères de suppression (DEL=127)
            if ($ascii === 127) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte la présence de code JavaScript
     */
    private function containsJavaScript(string $input): bool
    {
        $javascriptPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript\s*:/i',
            '/on\w+\s*=/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<applet[^>]*>/i',
            '/<meta[^>]*http-equiv/i',
            '/<link[^>]*javascript/i',
            '/<style[^>]*>.*?<\/style>/is',
        ];

        foreach ($javascriptPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les balises HTML malveillantes
     */
    private function containsMaliciousHtml(string $input): bool
    {
        $maliciousTags = [
            '/<script/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<applet/i',
            '/<form/i',
            '/<input/i',
            '/<button/i',
            '/<select/i',
            '/<textarea/i',
            '/<link/i',
            '/<meta/i',
            '/<style/i',
            '/<base/i',
            '/<frame/i',
            '/<frameset/i',
        ];

        foreach ($maliciousTags as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les encodages suspects
     */
    private function containsSuspiciousEncoding(string $input): bool
    {
        $suspiciousPatterns = [
            '/%3Cscript/i',           // <script encodé
            '/%3C\/script/i',         // </script encodé
            '/&#x3C;script/i',        // <script en entité HTML
            '/&#60;script/i',         // <script en entité HTML
            '/&lt;script/i',          // <script en entité HTML
            '/%2Fscript/i',           // /script encodé
            '/%2E%2E%2F/i',           // ../ encodé
            '/%00/i',                 // Null byte
            '/%0A/i',                 // Line feed
            '/%0D/i',                 // Carriage return
            '/%09/i',                 // Tab
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}

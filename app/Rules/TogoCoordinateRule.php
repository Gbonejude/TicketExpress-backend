<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les coordonnées géographiques du Togo
 *
 * Limites géographiques du Togo :
 * - Latitude : 6.0°N à 11.0°N
 * - Longitude : 0.0°E à 1.8°E
 */
final class TogoCoordinateRule implements ValidationRule
{
    public function __construct(
        private readonly string $coordinateType = 'latitude', // 'latitude' ou 'longitude'
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail("La {$this->coordinateType} doit être un nombre valide.");

            return;
        }

        $coordinate = (float) $value;

        if ($this->coordinateType === 'latitude') {
            $this->validateLatitude($coordinate, $fail);
        } else {
            $this->validateLongitude($coordinate, $fail);
        }
    }

    /**
     * Valide la latitude du Togo
     */
    private function validateLatitude(float $latitude, Closure $fail): void
    {
        // Limites géographiques du Togo
        $minLatitude = 6.0;
        $maxLatitude = 11.0;

        if ($latitude < $minLatitude || $latitude > $maxLatitude) {
            $fail("La latitude doit être comprise entre {$minLatitude}°N et {$maxLatitude}°N pour le Togo.");

            return;
        }

        // La latitude est valide si elle est dans les limites du Togo
    }

    /**
     * Valide la longitude du Togo
     */
    private function validateLongitude(float $longitude, Closure $fail): void
    {
        // Limites géographiques du Togo
        $minLongitude = 0.0;
        $maxLongitude = 1.8;

        if ($longitude < $minLongitude || $longitude > $maxLongitude) {
            $fail("La longitude doit être comprise entre {$minLongitude}°E et {$maxLongitude}°E pour le Togo.");

            return;
        }

        // La longitude est valide si elle est dans les limites du Togo
    }
}

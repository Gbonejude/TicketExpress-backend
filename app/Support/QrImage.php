<?php

declare(strict_types=1);

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

/**
 * L'image du QR code d'un billet.
 *
 * Un seul endroit produit l'image, parce qu'elle sort par deux chemins qui
 * doivent montrer la même chose : la route publique que le front met dans un
 * `<img>`, et le PDF du billet. Deux appels séparés à la librairie finissaient
 * par diverger sur la taille et la correction d'erreurs, donc sur la lisibilité
 * au portique.
 *
 * PNG, produit par GD. La librairie précédente (`simplesoftwareio/simple-qrcode`)
 * n'était pas installée du tout — les deux appels levaient « class not found »,
 * ce qui rendait le QR indisponible sur le site et cassait le PDF. Son rendu PNG
 * dépend en plus de l'extension `imagick`, absente de ce PHP ; `endroid/qr-code`
 * écrit le PNG avec `gd`, présent.
 *
 * Correction d'erreurs haute : un billet est scanné sur un écran de téléphone
 * rayé, en plein soleil, ou sur une feuille pliée. 30 % de redondance est ce qui
 * fait la différence entre un scan immédiat et trois tentatives.
 */
final class QrImage
{
    /** Les octets d'un PNG encodant `$payload`. */
    public static function png(string $payload, int $size = 400, int $margin = 8): string
    {
        return (new Builder)->build(
            writer: new PngWriter,
            data: $payload,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
        )->getString();
    }

    /**
     * Le même PNG en `data:` URI.
     *
     * C'est la forme qu'attend dompdf : il ne va pas chercher une URL d'API
     * pendant le rendu (elle est protégée par un jeton, et le rendu se fait
     * parfois en file d'attente sans contexte HTTP), mais il sait décoder une
     * image embarquée.
     */
    public static function dataUri(string $payload, int $size = 300, int $margin = 8): string
    {
        return 'data:image/png;base64,'.base64_encode(self::png($payload, $size, $margin));
    }
}

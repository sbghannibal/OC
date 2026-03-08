<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Bouwt de EPC/SEPA SCT QR-code payload tekst op volgens de
 * "Quick Response Code – Guidelines to Enable the Data Capture for the Initiation
 * of a SEPA Credit Transfer" (versie 2.0).
 */
final class EpcQr
{
    public function build(
        string $beneficiaryName,
        string $iban,
        string $bic,
        float  $amount,
        string $remittance
    ): string {
        $amountFormatted = 'EUR' . number_format($amount, 2, '.', '');

        $lines = [
            'BCD',          // Service Tag
            '002',          // Version
            'UTF-8',        // Character set
            'SCT',          // Identification code
            $bic,           // BIC van de begunstigde bank
            $beneficiaryName,
            $iban,
            $amountFormatted,
            '',             // Purpose (leeg)
            '',             // Structured remittance reference (leeg)
            $remittance,    // Unstructured remittance information
        ];

        return implode("\n", $lines);
    }
}

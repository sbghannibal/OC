<?php

declare(strict_types=1);

namespace App\Services;

final class PaymentReference
{
    /** Alfabet zonder O, 0, 1, I om verwarring te voorkomen. */
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generate(string $campaignSlug): string
    {
        $slug  = preg_replace('/[^a-zA-Z0-9\-]/', '', $campaignSlug);
        $token = $this->randomToken(10);
        return 'OClourdes-actie-betaling-' . $slug . '-' . $token;
    }

    private function randomToken(int $length): string
    {
        $alphabet  = self::ALPHABET;
        $alphabetLen = strlen($alphabet);
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $alphabet[random_int(0, $alphabetLen - 1)];
        }
        return $token;
    }
}

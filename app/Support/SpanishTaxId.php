<?php

namespace App\Support;

final class SpanishTaxId
{
    private const NIF_CONTROL_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private const CIF_CONTROL_LETTERS = 'JABCDEFGHI';

    /**
     * @return 'Empresa'|'Persona'|null
     */
    public static function detectType(?string $taxId): ?string
    {
        $normalizedTaxId = self::normalize($taxId);

        if ($normalizedTaxId === null) {
            return null;
        }

        if (self::isValidCif($normalizedTaxId)) {
            return 'Empresa';
        }

        if (self::isValidNif($normalizedTaxId) || self::isValidNie($normalizedTaxId)) {
            return 'Persona';
        }

        return null;
    }

    public static function isValid(?string $taxId): bool
    {
        return self::detectType($taxId) !== null;
    }

    public static function normalize(?string $taxId): ?string
    {
        if ($taxId === null) {
            return null;
        }

        $normalizedTaxId = mb_strtoupper(trim($taxId));
        $normalizedTaxId = preg_replace('/[\s\-]/', '', $normalizedTaxId);

        if (! is_string($normalizedTaxId) || $normalizedTaxId === '') {
            return null;
        }

        return $normalizedTaxId;
    }

    private static function isValidNif(string $taxId): bool
    {
        if (! preg_match('/^\d{8}[A-Z]$/', $taxId)) {
            return false;
        }

        $number = (int) substr($taxId, 0, 8);
        $expectedLetter = self::NIF_CONTROL_LETTERS[$number % 23];

        return $taxId[8] === $expectedLetter;
    }

    private static function isValidNie(string $taxId): bool
    {
        if (! preg_match('/^[XYZ]\d{7}[A-Z]$/', $taxId)) {
            return false;
        }

        $prefixMap = [
            'X' => '0',
            'Y' => '1',
            'Z' => '2',
        ];
        $numericNie = $prefixMap[$taxId[0]].substr($taxId, 1, 7);
        $number = (int) $numericNie;
        $expectedLetter = self::NIF_CONTROL_LETTERS[$number % 23];

        return $taxId[8] === $expectedLetter;
    }

    private static function isValidCif(string $taxId): bool
    {
        if (! preg_match('/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/', $taxId)) {
            return false;
        }

        $entityType = $taxId[0];
        $controlCharacter = $taxId[8];
        $digits = substr($taxId, 1, 7);

        $sumOddPositions = 0;
        $sumEvenPositions = 0;

        for ($index = 0; $index < 7; $index++) {
            $digit = (int) $digits[$index];

            if ($index % 2 === 0) {
                $doubled = $digit * 2;
                $sumOddPositions += intdiv($doubled, 10) + ($doubled % 10);
            } else {
                $sumEvenPositions += $digit;
            }
        }

        $controlDigit = (10 - (($sumOddPositions + $sumEvenPositions) % 10)) % 10;
        $controlLetter = self::CIF_CONTROL_LETTERS[$controlDigit];

        if (in_array($entityType, ['A', 'B', 'E', 'H'], true)) {
            return $controlCharacter === (string) $controlDigit;
        }

        if (in_array($entityType, ['K', 'P', 'Q', 'S'], true)) {
            return $controlCharacter === $controlLetter;
        }

        return $controlCharacter === (string) $controlDigit || $controlCharacter === $controlLetter;
    }
}

<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    /**
     * Converts a peso amount (e.g. 486770.00) into words, e.g.
     * "Four Hundred Eighty Six Thousand Seven Hundred Seventy Pesos".
     */
    public static function convert(float $amount): string
    {
        $pesos = (int) floor($amount);
        $centavos = (int) round(($amount - $pesos) * 100);

        $words = $pesos === 0 ? 'Zero' : self::convertWholeNumber($pesos);
        $words .= ' Pesos';

        if ($centavos > 0) {
            $words .= ' and '.self::convertWholeNumber($centavos).' Centavos';
        }

        return $words;
    }

    private static function convertWholeNumber(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        $units = [
            ['divisor' => 1000000000, 'label' => 'Billion'],
            ['divisor' => 1000000, 'label' => 'Million'],
            ['divisor' => 1000, 'label' => 'Thousand'],
        ];

        $parts = [];
        foreach ($units as $unit) {
            if ($number >= $unit['divisor']) {
                $chunk = intdiv($number, $unit['divisor']);
                $number %= $unit['divisor'];
                $parts[] = self::convertHundreds($chunk).' '.$unit['label'];
            }
        }

        if ($number > 0) {
            $parts[] = self::convertHundreds($number);
        }

        return trim(implode(' ', $parts));
    }

    private static function convertHundreds(int $number): string
    {
        $parts = [];

        if ($number >= 100) {
            $parts[] = self::$ones[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $tensWord = self::$tens[intdiv($number, 10)];
            $remainder = $number % 10;
            $parts[] = $remainder > 0 ? "{$tensWord} ".self::$ones[$remainder] : $tensWord;
        } elseif ($number > 0) {
            $parts[] = self::$ones[$number];
        }

        return trim(implode(' ', array_filter($parts)));
    }
}

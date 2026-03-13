<?php

namespace App\Support;

class Base62Encoder
{
    private const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function encode(int $number): string
    {
        if ($number === 0) {
            return '0';
        }

        $base = strlen(self::CHARACTERS);
        $result = '';

        while ($number > 0) {
            $remainder = $number % $base;
            $result = self::CHARACTERS[$remainder] . $result;
            $number = intdiv($number, $base);
        }

        return $result;
    }

    public function decode(string $string): int
    {
        $base = strlen(self::CHARACTERS);
        $length = strlen($string);
        $number = 0;

        for ($i = 0; $i < $length; $i++) {
            $number = $number * $base + strpos(self::CHARACTERS, $string[$i]);
        }

        return $number;
    }
}
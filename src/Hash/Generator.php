<?php

namespace App\Hash;

class Generator
{
    const BASE62_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function process($input, $strength = 7): string
    {
        $length = strlen($input);
        $string = '';

        for ($i = 0; $i < $strength; $i++) {
            $randomChar = $input[random_int(0, $length - 1)];
            $string .= $randomChar;
        }

        return $string;
    }
}
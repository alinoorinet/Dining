<?php
namespace App\Library;

class randomWord
{
    public function randomWord()
    {
        $seed = str_split('abcdefghijklm!@$%^&*nopqrstuvwxyz0123456789'.'!@$%^&*');
        $seed2 = str_split('abcdefghijklmnopqrstuvwxyz'.'0123456789');

        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 6) as $k)
            $rand .= $seed[$k];

        $begin = $seed2[array_rand($seed2, 1)];
        $end = $seed2[array_rand($seed2, 1)];
        return $begin.$rand.$end;
    }
}
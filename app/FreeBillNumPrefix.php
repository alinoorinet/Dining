<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FreeBillNumPrefix extends Model
{
    protected $table = 'free_billnum_prefix';
    protected $fillable = [
        'date',
        'prefix',
    ];

    public function random_string($length = 10)
    {
        $permitted_chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length    = strlen($permitted_chars);
        $random_string   = '';
        for($i = 0; $i < $length; $i++) {
            $random_character = $permitted_chars[random_int(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }
}

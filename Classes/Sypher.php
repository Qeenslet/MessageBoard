<?php
/**
 * Created by PhpStorm.
 * User: gulidoveg
 * Date: 26.02.19
 * Time: 11:08
 */

class Sypher
{
    public static function encode($string)
    {
        $tmp = '@gh7y567@sbhZ3e';
        $salt = md5($tmp);
        return crypt($string, $salt);
    }


    public static function verify($saved_pass, $input_pass)
    {
        $entered = self::encode($input_pass);
        return $entered === $saved_pass;
    }
}
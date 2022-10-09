<?php


namespace datagutten\descriptionMaker;


class HTMLToBBCode
{
    public static function convertP($string)
    {
        return preg_replace('#<p>(.+)</p>#U', "$1\n", $string);
    }

    public static function convertUL($string)
    {
        if (preg_match('#<ul>(.+)</ul>#Us', $string, $matches)) {
            $list = preg_replace('#<li>(.+)</li>#Us', "[*]$1", $matches[1]);
            return str_replace($matches[0], $list, $string);
        } else
            return $string;
    }

    public static function convert($string)
    {
        $string = self::convertP($string);
        $string = self::convertUL($string);
        return trim(strip_tags($string));
    }
}
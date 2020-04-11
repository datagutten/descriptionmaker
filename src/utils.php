<?php


namespace datagutten\descriptionmaker;


class utils
{
    /**
     * Convert duration in seconds to hours, minutes and seconds
     * https://stackoverflow.com/a/3172368/2630074
     * @param int $seconds
     * @return string Minutes:Seconds
     */
    public static function seconds_to_time($seconds)
    {
        return sprintf('%02d:%02d',floor(($seconds/60) % 60),$seconds % 60);
    }
}
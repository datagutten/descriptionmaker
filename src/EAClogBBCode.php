<?php


namespace datagutten\descriptionmaker;


class EAClogBBCode
{
    static function color_class($matches)
    {
        switch ($matches[1])
        {
            case 'log2': $tag = 'color'; $value='yellow'; break;
            case 'log3': $tag = 'color'; $value='#0E88C6'; break;
            case 'log4': $tag = 'b'; break;
            case 'log4 log1': $tag = 'b'; break;
            case 'log5': $tag = 'u'; break;
            case 'good': $tag = 'color'; $value='green'; $tag2 = 'b'; break;
            case 'bad': $tag = 'color';  $value='red'; $tag2='b'; break;
            case 'goodish': $tag = 'color'; $value = '#35BF00'; $tag2='b'; break;
            case 'badish': $tag = 'color'; $value = '#E5B244'; $tag2='b'; break;
            default: return $matches[2];
        }


        if(empty($value))
            $string = sprintf('[%1$s]%2$s[/%1$s]', $tag, $matches[2]);
        else
            $string = sprintf('[%1$s=%3$s]%2$s[/%1$s]', $tag, $matches[2], $value);
        if(!empty($tag2))
            $string = sprintf('[%1$s]%2$s[/%1$s]', $tag2, $string);
        return $string;
    }

    /**
     * Rewrite log HTML to BBCode
     * @param string $log Log with HTML
     * @return string Log with BBCode
     */
    static function rewrite($log)
    {
        $count = 1;
        while($count>0) {
            $pattern = '#<span class="([a-z0-9\s]+)">((?:[^</]|(?:\[/))+)</span>#';
            $log = preg_replace_callback($pattern, 'self::color_class', $log, -1, $count);
        }
        $log = preg_replace('#<strong>([^</]+)</strong>#', '$1', $log);
        $log = preg_replace_callback('#<span class="([a-z0-9]+)">(.+)</span>#U','self::color_class', $log);
        return $log;
    }
}
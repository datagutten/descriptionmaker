<?php


namespace datagutten\descriptionMaker;


class BBCode
{
    /**
     * Create a simple BBCode tag
     * @param string $tag Tag name
     * @param string $value Tag value
     * @return string Tag with value
     */
    public static function simple(string $tag, string $value): string
    {
        return sprintf('[%1$s]%2$s[/%1$s]', $tag, $value);
    }

    /**
     * Create a BBCode with an argument
     * @param string $tag Tag name
     * @param string $argument Argument
     * @param string $value Tag value
     * @return string Tag with value and argument
     */
    public static function argument(string $tag, string $argument, string $value): string
    {
        return sprintf('[%1$s=%3$s]%2$s[/%1$s]', $tag, $value, $argument);
    }

    /**
     * Create a link
     * @param string $url Link target
     * @param string|null $text Link text
     * @return string Link tag
     */
    public static function link(string $url, string $text = null): string
    {
        if ($text)
            return self::argument('url', $url, $text);
        else
            return self::simple('url', $url);
    }

    /**
     * Create image BBCode
     * @param string $url Image URL
     * @param string|null $link URL to make the image a link
     * @return string Image tag
     */
    public static function image(string $url, string $link = null): string
    {
        if (!empty($link))
            return self::argument('url', $link, self::simple('img', $url));
        else
            return self::simple('img', $url);
    }


}
<?php
foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file)
{
    if (file_exists($file))
    {
        /** @noinspection PhpIncludeInspection */
        require $file;
        break;
    }
}
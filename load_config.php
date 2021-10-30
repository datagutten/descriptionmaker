<?php
function descriptionmaker_config()
{
    $config = require __DIR__ . '/config.php';
    $config['tvdb'] = require __DIR__ . '/config_tvdb.php';
    return $config;
}
<?php
//Only define function if config file exists (allow including projects to define their own config)
if (file_exists(__DIR__ . '/config.php'))
{
    function descriptionmaker_config()
    {
        $config = require __DIR__ . '/config.php';
        $config['tvdb'] = require __DIR__ . '/config_tvdb.php';
        return $config;
    }
}
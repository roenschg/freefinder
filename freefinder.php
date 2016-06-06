<?php
require(__DIR__.'/vendor/autoload.php');

$config_file = __DIR__.'/settings.json';

// Load configuration file
if (!file_exists($config_file)) {
    throw new Exception("The file '$config_file' does not exist!");
}

$settings = json_decode(file_get_contents($config_file));
if (!$settings) {
    throw new Exception("Was not able to load configuration file '$config_file'.");
}


foreach ($settings->providers as $provider) {
    if (!class_exists($provider->name)) {
        throw new Exception("The provider '{$provider->name}' is not implented!");
    }

    $current_provider = new $provider->name($provider->settings);
    var_dump($current_provider->getEvents());
}

// Run vendors and store events

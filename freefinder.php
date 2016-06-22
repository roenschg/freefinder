<?php
require(__DIR__.'/vendor/autoload.php');

$config_file = __DIR__.'/settings.json';
$providers = [];
$events = [];

// Load configuration file
if (!file_exists($config_file)) {
    throw new Exception("The file '$config_file' does not exist!");
}

$settings = json_decode(file_get_contents($config_file));
if (!$settings) {
    throw new Exception("Was not able to load configuration file '$config_file'.");
}


// Read settings and create providers
foreach ($settings->providers as $provider_settings) {
    if (!class_exists($provider_settings->name)) {
        throw new Exception("The provider '{$provider_settings->name}' is not implented!");
    }

    $providers[] = new $provider_settings->name($provider_settings->settings);

}

// Load events
foreach ($providers as $provider) {
    $events = array_merge($events, $provider->getEvents());
}

// Show event titles
foreach ($events as $event) {
    printf("Event '%s'".PHP_EOL, $event->VEVENT->SUMMARY);
}

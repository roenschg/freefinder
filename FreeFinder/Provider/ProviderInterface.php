<?php
namespace FreeFinder\Provider;

interface ProviderInterface
{
    public function __construct($settings);
    public function getEvents();
}

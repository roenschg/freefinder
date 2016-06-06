<?php
namespace FreeFinder\Provider;

class iCloud implements ProviderInterface
{
    protected $settings = null;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function getEvents()
    {
        foreach ($this->settings->calendars as $calendar) {
            // create a new cURL resource
            $ch = curl_init();

            echo $this->getCalendarURI($calendar).PHP_EOL;
            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $this->getCalendarURI($calendar));
            $headers = array(
                'Content-Type:text/calendar',
                'Authorization: Basic ' . base64_encode($this->settings->appleid.':'.$this->settings->password),
                'User-Agent: FreeFinder 0.1',
                'Depth: 1'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

            // grab URL and pass it to the browser
            echo curl_exec($ch);
            return curl_error($ch);

            // close cURL resource, and free up system resources
            curl_close($ch);
        }
    }

    protected function getCalendarURI($calendar_name)
    {
        return sprintf(
            "%s/%s/calendars/%s/",
            $this->settings->server,
            $this->settings->principle,
            $calendar_name
        );
    }
}

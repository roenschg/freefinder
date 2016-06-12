<?php
namespace FreeFinder\Provider;
use Sabre\VObject;

class iCloud implements ProviderInterface
{
    protected $settings = null;


    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get all events from all calendars configured in settings
     *
     * @return array Of Sabre\Component\VCalendar
     */
    public function getEvents()
    {
        // Get events for every configured calendar
        foreach ($this->settings->calendars as $calendar) {
            $events = [];
            $xml = $this->getEventsXML($calendar);

            // Read every entry in CALDAV response and get given ical file(s)
            foreach ($xml->response as $entry) {
                $event = $this->getVCalendarByURI($entry->href);

                if ($event) {
                    $events[] = $event;
                }
            }
        }

        return $events;
    }

    /**
     * Get VCalendar object by an relative CALDAC URI
     *
     * @return VObject\Component\VCalendar
     */
    protected function getVCalendarByURI($uri)
    {
        $ch = $this->setupAuthenticatedCurl();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $this->getEventURI($uri));

        $remote_content = curl_exec($ch);
        $mime_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (false !== strpos($mime_type, "text/calendar")) {
            return VObject\Reader::read($remote_content);
        } else {
            return false;
        }
    }


    protected function getEventsXML($calendar)
    {
        $ch = $this->setupAuthenticatedCurl();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $this->getCalendarURI($calendar));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

        // grab URL and pass it to the browser
        $xml_content = curl_exec($ch);

        // close cURL resource, and free up system resources
        curl_close($ch);

        // Return XML
        return new \SimpleXMLElement($xml_content);
    }


    /**
     * Set headers for authorization, user-agent and content type. Also set output
     * of curlopt to return value.
     *
     * @return resource The curl resource
     */
    protected function setupAuthenticatedCurl()
    {
        // create a new cURL resource
        $ch = curl_init();

        $headers = array(
            'Content-Type:text/calendar',
            'Authorization: Basic ' . base64_encode($this->settings->appleid.':'.$this->settings->password),
            'User-Agent: FreeFinder 0.1',
            'Depth: 1'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }


    /**
     * Combine server, principle and calendar name to an absolute URI.
     *
     * @return string An CALDAV URI
     */
    protected function getCalendarURI($calendar_name)
    {
        return sprintf(
            "%s/%s/calendars/%s/",
            $this->settings->server,
            $this->settings->principle,
            $calendar_name
        );
    }


    /**
     * Convert a relative calendar URI into an absolute one.
     *
     * @return string URI with protocol and domain as well
     */
    protected function getEventURI($event_uri_short)
    {
        return sprintf(
            "%s/%s",
            $this->settings->server,
            $event_uri_short
        );
    }
}

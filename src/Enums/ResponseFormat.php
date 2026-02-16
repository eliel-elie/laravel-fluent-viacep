<?php

namespace ViaCep\Enums;

enum ResponseFormat: string
{
    case JSON  = 'json';
    case XML   = 'xml';
    case PIPED = 'piped';
    case JSONP = 'jsonp';

    /**
     * Returns the API endpoint for the format.
     */
    public function endpoint(): string
    {
        return match ($this) {
            self::JSON  => 'json',
            self::XML   => 'xml',
            self::PIPED => 'piped',
            self::JSONP => 'json', // JSONP uses JSON endpoint with callback
        };
    }

    /**
     * Returns the MIME type of the format.
     */
    public function mimeType(): string
    {
        return match ($this) {
            self::JSON  => 'application/json',
            self::XML   => 'application/xml',
            self::PIPED => 'text/plain',
            self::JSONP => 'application/javascript',
        };
    }

    /**
     * Checks if the format supports multiple results.
     */
    public function supportsMultipleResults(): bool
    {
        return $this !== self::PIPED;
    }
}

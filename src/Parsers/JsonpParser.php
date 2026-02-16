<?php

namespace ViaCep\Parsers;

use ViaCep\DTO\Address;

class JsonpParser implements ParserInterface
{
    public function parse(string $response): Address|array
    {
        // Remove callback wrapper: myCallback({...})
        $json       = preg_replace('/^[^(]*\((.*)\)[^)]*;?$/s', '$1', trim($response));

        // Use JsonParser to process the JSON
        $jsonParser = new JsonParser;

        return $jsonParser->parse($json);
    }

    public function canParse(string $response): bool
    {
        // Check if it has a JSONP pattern: callback({...})
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\s*\(.*\)\s*;?$/', trim($response)) === 1;
    }
}

<?php

namespace ViaCep\Parsers;

use RuntimeException;
use SimpleXMLElement;
use ViaCep\DTO\Address;
use ViaCep\Exceptions\CepNotFoundException;

class XmlParser implements ParserInterface
{
    public function parse(string $response): Address|array
    {
        libxml_use_internal_errors(true);
        $xml       = simplexml_load_string($response);

        if ($xml === false) {
            throw new RuntimeException('Invalid XML response');
        }

        if (isset($xml->erro) && (string) $xml->erro === 'true') {
            throw new CepNotFoundException('CEP not found');
        }

        if (isset($xml->cep)) {
            return $this->mapElementToAddress($xml);
        }

        $addresses = [];

        foreach ($xml->children() as $item) {
            if (isset($item->cep)) {
                $addresses[] = $this->mapElementToAddress($item);
            }
        }

        if (empty($addresses) && isset($xml->element)) {
            foreach ($xml->element as $item) {
                $addresses[] = $this->mapElementToAddress($item);
            }
        }

        return $addresses;
    }

    protected function mapElementToAddress(SimpleXMLElement $element): Address
    {
        return Address::fromArray([
            'cep'         => (string) $element->cep,
            'logradouro'  => (string) $element->logradouro,
            'complemento' => (string) $element->complemento,
            'bairro'      => (string) $element->bairro,
            'localidade'  => (string) $element->localidade,
            'uf'          => (string) $element->uf,
            'ibge'        => (string) $element->ibge,
            'gia'         => (string) $element->gia,
            'ddd'         => (string) $element->ddd,
            'siafi'       => (string) $element->siafi,
        ]);
    }

    public function canParse(string $response): bool
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        libxml_clear_errors();

        return $xml !== false;
    }
}

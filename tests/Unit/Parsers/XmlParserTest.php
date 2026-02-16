<?php

use ViaCep\DTO\Address;
use ViaCep\Exceptions\CepNotFoundException;
use ViaCep\Parsers\XmlParser;

describe('XmlParser', function () {
    it('can parse a single xml response', function () {
        $xml     = '<?xml version="1.0" encoding="UTF-8"?>
        <xmlcep>
          <cep>01001-000</cep>
          <logradouro>Praça da Sé</logradouro>
          <complemento>lado ímpar</complemento>
          <bairro>Sé</bairro>
          <localidade>São Paulo</localidade>
          <uf>SP</uf>
          <ibge>3550308</ibge>
          <gia>1004</gia>
          <ddd>11</ddd>
          <siafi>7107</siafi>
        </xmlcep>';

        $parser  = new XmlParser;
        $address = $parser->parse($xml);

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000');
    });

    it('throws exception if cep not found in xml', function () {
        $xml    = '<?xml version="1.0" encoding="UTF-8"?>
        <xmlcep>
          <erro>true</erro>
        </xmlcep>';

        $parser = new XmlParser;
        $parser->parse($xml);
    })->throws(CepNotFoundException::class);

    it('can parse multiple addresses in xml', function () {
        $xml       = '<?xml version="1.0" encoding="UTF-8"?>
        <xmlcep>
          <element>
            <cep>01001-000</cep>
            <logradouro>Praça da Sé</logradouro>
          </element>
          <element>
            <cep>01001-001</cep>
            <logradouro>Praça da Sé</logradouro>
          </element>
        </xmlcep>';

        $parser    = new XmlParser;
        $addresses = $parser->parse($xml);

        expect($addresses)->toBeArray()
            ->and($addresses)->toHaveCount(2)
            ->and($addresses[0])->toBeInstanceOf(Address::class);
    });
});

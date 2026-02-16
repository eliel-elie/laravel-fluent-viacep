<?php

use ViaCep\DTO\Address;
use ViaCep\Exceptions\CepNotFoundException;
use ViaCep\Parsers\JsonParser;

describe('JsonParser', function () {
    it('can parse a single json response', function () {
        $json    = '{
            "cep": "01001-000",
            "logradouro": "Praça da Sé",
            "complemento": "lado ímpar",
            "bairro": "Sé",
            "localidade": "São Paulo",
            "uf": "SP",
            "ibge": "3550308",
            "gia": "1004",
            "ddd": "11",
            "siafi": "7107"
        }';

        $parser  = new JsonParser;
        $address = $parser->parse($json);

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000');
    });

    it('throws exception if cep not found in json', function () {
        $json   = '{"erro": true}';

        $parser = new JsonParser;
        $parser->parse($json);
    })->throws(CepNotFoundException::class);

    it('can parse multiple addresses in json', function () {
        $json      = '[
            {
                "cep": "01001-000",
                "logradouro": "Praça da Sé"
            },
            {
                "cep": "01001-001",
                "logradouro": "Praça da Sé"
            }
        ]';

        $parser    = new JsonParser;
        $addresses = $parser->parse($json);

        expect($addresses)->toBeArray()
            ->and($addresses)->toHaveCount(2)
            ->and($addresses[0])->toBeInstanceOf(Address::class);
    });
});

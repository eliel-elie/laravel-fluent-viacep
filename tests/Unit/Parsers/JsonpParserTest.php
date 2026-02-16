<?php

use ViaCep\DTO\Address;
use ViaCep\Parsers\JsonpParser;

describe('JsonpParser', function () {
    it('can parse a jsonp response', function () {
        $jsonp   = 'myCallback({
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
        });';

        $parser  = new JsonpParser;
        $address = $parser->parse($jsonp);

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000');
    });
});

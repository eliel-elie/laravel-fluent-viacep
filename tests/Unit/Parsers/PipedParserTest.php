<?php

use ViaCep\DTO\Address;
use ViaCep\Parsers\PipedParser;

describe('PipedParser', function () {
    it('can parse a piped response', function () {
        $piped   = 'cep:01001-000|logradouro:Praça da Sé|complemento:lado ímpar|bairro:Sé|localidade:São Paulo|uf:SP|ibge:3550308|gia:1004|ddd:11|siafi:7107';

        $parser  = new PipedParser;
        $address = $parser->parse($piped);

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000')
            ->and($address->logradouro)->toBe('Praça da Sé');
    });
});

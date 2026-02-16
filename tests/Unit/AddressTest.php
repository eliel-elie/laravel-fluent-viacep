<?php

use ViaCep\DTO\Address;

describe('Address DTO', function () {
    it('can create an address from an array', function () {
        $data    = [
            'cep'         => '01001-000',
            'logradouro'  => 'Praça da Sé',
            'complemento' => 'lado ímpar',
            'bairro'      => 'Sé',
            'localidade'  => 'São Paulo',
            'uf'          => 'SP',
            'ibge'        => '3550308',
            'gia'         => '1004',
            'ddd'         => '11',
            'siafi'       => '7107',
        ];

        $address = Address::fromArray($data);

        expect($address->cep)->toBe('01001-000')
            ->and($address->logradouro)->toBe('Praça da Sé')
            ->and($address->getCity())->toBe('São Paulo')
            ->and($address->getState())->toBe('SP');
    });

    it('can format the cep', function () {
        $address  = Address::fromArray(['cep' => '01001000']);
        expect($address->getFormattedCep())->toBe('01001-000');

        $address2 = Address::fromArray(['cep' => '01001-000']);
        expect($address2->getFormattedCep())->toBe('01001-000');
    });

    it('can return the full address', function () {
        $address = Address::fromArray([
            'cep'        => '01001000',
            'logradouro' => 'Praça da Sé',
            'bairro'     => 'Sé',
            'localidade' => 'São Paulo',
            'uf'         => 'SP',
        ]);

        expect($address->getFullAddress())->toBe('Praça da Sé, Sé, São Paulo, SP, 01001-000');
    });

    it('can be converted to json', function () {
        $data    = [
            'cep'         => '01001-000',
            'logradouro'  => 'Praça da Sé',
            'complemento' => 'lado ímpar',
            'bairro'      => 'Sé',
            'localidade'  => 'São Paulo',
            'uf'          => 'SP',
            'ibge'        => '3550308',
            'gia'         => '1004',
            'ddd'         => '11',
            'siafi'       => '7107',
        ];

        $address = Address::fromArray($data);

        expect(json_encode($address))->toBe(json_encode($data));
    });
});

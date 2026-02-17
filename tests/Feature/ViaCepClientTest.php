<?php

use Illuminate\Support\Facades\Http;
use ViaCep\DTO\Address;
use ViaCep\Facades\ViaCep;

describe('ViaCepClient', function () {
    it('can fetch an address by cep using fluent interface', function () {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([
                'cep'        => '01001-000',
                'logradouro' => 'Praça da Sé',
                'localidade' => 'São Paulo',
                'uf'         => 'SP',
            ], 200),
        ]);

        $address = ViaCep::cep('01001000')->get();

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000')
            ->and($address->logradouro)->toBe('Praça da Sé');
    });

    it('can fetch an address in xml format', function () {
        $xml     = '<?xml version="1.0" encoding="UTF-8"?><xmlcep><cep>01001-000</cep><logradouro>Praça da Sé</logradouro></xmlcep>';

        Http::fake([
            'viacep.com.br/ws/01001000/xml/' => Http::response($xml, 200),
        ]);

        $address = ViaCep::cep('01001000')->asXml()->get();

        expect($address)->toBeInstanceOf(Address::class)
            ->and($address->cep)->toBe('01001-000');
    });

    it('can fetch multiple addresses by address details', function () {
        Http::fake([
            'viacep.com.br/ws/SP/Sao%20Paulo/Praca%20da%20Se/json/' => Http::response([
                ['cep' => '01001-000', 'logradouro' => 'Praça da Sé'],
                ['cep' => '01001-001', 'logradouro' => 'Praça da Sé'],
            ], 200),
        ]);

        $addresses = ViaCep::state('SP')
            ->city('Sao Paulo')
            ->street('Praca da Se')
            ->get();

        expect($addresses)->toBeArray()
            ->and($addresses)->toHaveCount(2)
            ->and($addresses[0]->cep)->toBe('01001-000');
    });

    it('can use cache to store results', function () {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response(['cep' => '01001-000'], 200),
        ]);

        // First call
        ViaCep::cep('01001000')->cache(3600)->get();

        // Second call - should not hit Http::fake again
        ViaCep::cep('01001000')->cache(3600)->get();

        Http::assertSentCount(1);
    });

        it('can return raw response', function () {

            $json = '{"cep": "01001-000"}';

            Http::fake([
                'viacep.com.br/ws/01001000/json/' => Http::response($json, 200),
            ]);

            $response = ViaCep::cep('01001000')->raw();

            expect($response)->toBe($json);
        });

        it('returns an empty array when provided with invalid ceps in bulk', function () {

            $results = ViaCep::bulk(['invalid', '123'])->get();

            expect($results)->toBeArray()
                ->and($results)->toBeEmpty();
        });

    });

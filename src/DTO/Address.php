<?php

namespace ViaCep\DTO;

use JsonSerializable;

class Address implements JsonSerializable
{
    public function __construct(
        public readonly string $cep,
        public readonly string $logradouro,
        public readonly string $complemento,
        public readonly string $bairro,
        public readonly string $localidade,
        public readonly string $uf,
        public readonly string $ibge,
        public readonly string $gia,
        public readonly string $ddd,
        public readonly string $siafi,
    ) {}

    /**
     * Create Address from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cep: $data['cep'] ?? '',
            logradouro: $data['logradouro'] ?? '',
            complemento: $data['complemento'] ?? '',
            bairro: $data['bairro'] ?? '',
            localidade: $data['localidade'] ?? '',
            uf: $data['uf'] ?? '',
            ibge: $data['ibge'] ?? '',
            gia: $data['gia'] ?? '',
            ddd: $data['ddd'] ?? '',
            siafi: $data['siafi'] ?? '',
        );
    }

    /**
     * Returns the full formatted address.
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->logradouro,
            $this->complemento,
            $this->bairro,
            $this->localidade,
            $this->uf,
            $this->getFormattedCep(),
        ]);

        return implode(', ', $parts);
    }

    /**
     * Returns formatted CEP (00000-000).
     */
    public function getFormattedCep(): string
    {
        $cep = preg_replace('/\D/', '', $this->cep);

        if (strlen($cep) !== 8) {
            return $this->cep;
        }

        return substr($cep, 0, 5) . '-' . substr($cep, 5);
    }

    /**
     * Alias for localidade.
     */
    public function getCity(): string
    {
        return $this->localidade;
    }

    /**
     * Alias for uf.
     */
    public function getState(): string
    {
        return $this->uf;
    }

    /**
     * Alias for logradouro.
     */
    public function getStreet(): string
    {
        return $this->logradouro;
    }

    /**
     * Alias for bairro.
     */
    public function getNeighborhood(): string
    {
        return $this->bairro;
    }

    /**
     * Returns IBGE code.
     */
    public function getIbgeCode(): string
    {
        return $this->ibge;
    }

    /**
     * Checks if the address is complete.
     */
    public function isComplete(): bool
    {
        return ! empty($this->logradouro)
            && ! empty($this->bairro)
            && ! empty($this->localidade)
            && ! empty($this->uf);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'cep'         => $this->cep,
            'logradouro'  => $this->logradouro,
            'complemento' => $this->complemento,
            'bairro'      => $this->bairro,
            'localidade'  => $this->localidade,
            'uf'          => $this->uf,
            'ibge'        => $this->ibge,
            'gia'         => $this->gia,
            'ddd'         => $this->ddd,
            'siafi'       => $this->siafi,
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns string representation.
     */
    public function __toString(): string
    {
        return $this->getFullAddress();
    }
}

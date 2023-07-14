<?php

namespace Nemorize\Fexpr;

use JsonSerializable;

class Token implements TokenInterface, JsonSerializable
{
    public function __construct (
        private readonly string $type,
        private readonly string $literal,
    ) { }

    public function getType (): string
    {
        return $this->type;
    }

    public function getLiteral (): string
    {
        return $this->literal;
    }

    public function jsonSerialize (): array
    {
        return [
            'type' => $this->getType(),
            'literal' => $this->getLiteral(),
        ];
    }
}
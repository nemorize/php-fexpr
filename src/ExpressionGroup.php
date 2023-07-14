<?php

namespace Nemorize\Fexpr;

use JsonSerializable;

class ExpressionGroup implements ExpressionGroupInterface, JsonSerializable
{
    public function __construct (
        private readonly TokenInterface $operation,
        private readonly array|ExpressionInterface $item,
    ) { }

    public function getOperation (): TokenInterface
    {
        return $this->operation;
    }

    public function getItem (): array|ExpressionInterface
    {
        return $this->item;
    }

    public function jsonSerialize (): array
    {
        return [
            'operation' => $this->getOperation(),
            'item' => $this->getItem(),
        ];
    }
}
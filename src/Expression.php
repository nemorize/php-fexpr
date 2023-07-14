<?php

namespace Nemorize\Fexpr;

use JsonSerializable;

class Expression implements ExpressionInterface, JsonSerializable
{
    public function __construct (
        private readonly TokenInterface $left,
        private readonly TokenInterface $operation,
        private readonly TokenInterface $right
    ) { }

    public function getLeft (): TokenInterface
    {
        return $this->left;
    }

    public function getOperation (): TokenInterface
    {
        return $this->operation;
    }

    public function getRight (): TokenInterface
    {
        return $this->right;
    }

    public function jsonSerialize (): array
    {
        return [
            'left' => $this->getLeft(),
            'operation' => $this->getOperation(),
            'right' => $this->getRight(),
        ];
    }
}
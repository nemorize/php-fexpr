<?php

namespace Nemorize\Fexpr;

interface ExpressionGroupInterface
{
    /**
     * Get the join token.
     *
     * @return TokenInterface
     */
    public function getOperation (): TokenInterface;

    /**
     * Get the item.
     *
     * @return array<ExpressionGroupInterface>|ExpressionInterface
     */
    public function getItem (): array|ExpressionInterface;
}
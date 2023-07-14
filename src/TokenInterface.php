<?php

namespace Nemorize\Fexpr;

interface TokenInterface
{
    /**
     * @return string
     */
    public function getType (): string;

    /**
     * @return string
     */
    public function getLiteral (): string;
}
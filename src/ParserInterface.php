<?php

namespace Nemorize\Fexpr;

interface ParserInterface
{
    /**
     * Parse the given text and return its processed AST.
     *
     * @param string $text
     * @return array<TokenInterface>
     */
    public function parse (string $text): array;
}
<?php

namespace Nemorize\Fexpr;

interface ScannerInterface
{
    /**
     * Scan the input string and return an array of tokens.
     *
     * @param string $text
     * @return array<TokenInterface>
     */
    public function scan (string $text): array;
}

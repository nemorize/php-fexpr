<?php

namespace Nemorize\Fexpr;

interface ExpressionInterface
{
    public function getLeft (): TokenInterface;

    public function getOperation (): TokenInterface;

    public function getRight (): TokenInterface;
}
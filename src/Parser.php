<?php

namespace Nemorize\Fexpr;

use Exception;

class Parser implements ParserInterface
{
    /**
     * @throws Exception
     */
    public function parse (string $text): array
    {
        $result = [];
        $step = 'BEFORE';
        $join = '&&';
        $expression = [ 'left' => null, 'right' => null, 'operation' => null ];

        $tokens = (new Scanner())->scan($text);
        foreach ($tokens as $token) {
            if ($token->getType() === 'whitespace') {
                continue;
            }

            if ($token->getType() === 'group') {
                $groupResult = self::parse($token->getLiteral());
                if (count($groupResult) > 0) {
                    $result[] = new ExpressionGroup(new Token('join', $join), $groupResult);
                }
                $step = 'JOIN';
                continue;
            }

            if ($step === 'BEFORE') {
                if ($token->getType() !== 'identifier' && $token->getType() !== 'text' && $token->getType() !== 'number') {
                    throw new Exception('Expected left operand (identifier, text or number), got ' . $token->getLiteral() . '(' . $token->getType() . ')');
                }
                $expression['left'] = $token;
                $step = 'SIGN';
            }
            else if ($step === 'SIGN') {
                if ($token->getType() !== 'sign') {
                    throw new Exception('Expected sign, got ' . $token->getLiteral() . '(' . $token->getType() . ')');
                }
                $expression['operation'] = $token;
                $step = 'AFTER';
            }
            else if ($step === 'AFTER') {
                if ($token->getType() !== 'identifier' && $token->getType() !== 'text' && $token->getType() !== 'number') {
                    throw new Exception('Expected right operand (identifier, text or number), got ' . $token->getLiteral() . '(' . $token->getType() . ')');
                }
                $expression['right'] = $token;
                $result[] = new ExpressionGroup(new Token('join', $join), new Expression(...$expression));
                $expression = [ 'left' => null, 'right' => null, 'operation' => null ];
                $step = 'JOIN';
            }
            else if ($step === 'JOIN') {
                if ($token->getType() !== 'join') {
                    throw new Exception('Expected join, got ' . $token->getLiteral() . '(' . $token->getType() . ')');
                }
                $join = $token->getLiteral();
                $step = 'BEFORE';
            }
        }

        if ($step !== 'JOIN') {
            throw new Exception('Unexpected end of expression');
        }

        return $result;
    }
}
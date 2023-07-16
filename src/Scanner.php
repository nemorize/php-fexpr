<?php

namespace Nemorize\Fexpr;

use Exception;

class Scanner implements ScannerInterface
{
    private array $characters = [];
    private int $position = 0;
    
    /**
     * @throws Exception
     */
    public function scan (string $text): array
    {
        $this->characters = str_split($text);
        $this->position = 0;

        $buff = [];
        while ($token = $this->scanToken()) {
            $buff[] = $token;
        }

        return $buff;
    }

    /**
     * Scan token from the input stream.
     *
     * @throws Exception
     */
    private function scanToken (): ?TokenInterface
    {
        if (($ch = $this->read()) === false) {
            return null;
        }

        if ($this->isWhitespaceToken($ch)) {
            $this->unread();
            return $this->scanWhitespaceToken();
        }
        if ($this->isGroupStartToken($ch)) {
            $this->unread();
            return $this->scanGroupToken();
        }
        if ($this->isIdentifierStartToken($ch)) {
            $this->unread();
            return $this->scanIdentifierToken();
        }
        if ($this->isNumberStartToken($ch)) {
            $this->unread();
            return $this->scanNumberToken();
        }
        if ($this->isTextStartToken($ch)) {
            $this->unread();
            return $this->scanTextToken();
        }
        if ($this->isSignStartToken($ch)) {
            $this->unread();
            return $this->scanSignToken();
        }
        if ($this->isJoinStartToken($ch)) {
            $this->unread();
            return $this->scanJoinToken();
        }

        throw new Exception('Unexpected character: ' . $ch);
    }

    /**
     * Scan incoming whitespaces from the input stream.
     *
     * @return TokenInterface
     */
    private function scanWhitespaceToken (): TokenInterface
    {
        $buff = [];
        while (($ch = $this->read()) !== false) {
            if (!$this->isWhitespaceToken($ch)) {
                $this->unread();
                break;
            }
            $buff[] = $ch;
        }

        return new Token('whitespace', implode('', $buff));
    }

    /**
     * Scan incoming group from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanGroupToken (): TokenInterface
    {
        $buff = [];
        $openGroups = 1;
        $firstCh = $this->read();

        while (($ch = $this->read()) !== false) {
            if ($this->isGroupStartToken($ch)) {
                $openGroups++;
            }
            else if ($this->isTextStartToken($ch)) {
                $this->unread();
                $text = $this->scanTextToken();
                $buff[] = '"' . $text->getLiteral() . '"';
                continue;
            }
            else if ($ch === ')') {
                $openGroups--;
                if ($openGroups <= 0) {
                    break;
                }
            }
            $buff[] = $ch;
        }

        if ($openGroups > 0) {
            throw new Exception('Invalid group: ' . $firstCh . implode('', $buff));
        }
        return new Token('group', implode('', $buff));
    }

    /**
     * Scan incoming identifier from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanIdentifierToken (): TokenInterface
    {
        $buff = [];
        while (($ch = $this->read()) !== false) {
            if (!$this->isIdentifierStartToken($ch) && !$this->isDigitToken($ch) && $ch !== '.' && $ch !== ':') {
                $this->unread();
                break;
            }
            $buff[] = $ch;
        }

        $buff = implode('', $buff);
        if (!$this->isIdentifier($buff)) {
            throw new Exception('Invalid identifier: ' . $buff);
        }

        return new Token('identifier', $buff);
    }

    /**
     * Scan incoming number from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanNumberToken (): TokenInterface
    {
        $buff = [];
        while (($ch = $this->read()) !== false) {
            if (!$this->isDigitToken($ch) && $ch !== '.') {
                $this->unread();
                break;
            }
            $buff[] = $ch;
        }

        $buff = implode('', $buff);
        if (!is_numeric($buff)) {
            throw new Exception('Invalid number: ' . $buff);
        }
        return new Token('number', $buff);
    }

    /**
     * Scan incoming text from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanTextToken (): TokenInterface
    {
        $buff = [];
        $prevCh = '';
        $hasMatchingQuotes = false;

        $firstCh = $this->read();
        $buff[] = $firstCh;

        while (($ch = $this->read()) !== false) {
            $buff[] = $ch;
            if ($ch === $firstCh && $prevCh !== '\\') {
                $hasMatchingQuotes = true;
                break;
            }
            $prevCh = $ch;
        }

        if (!$hasMatchingQuotes) {
            throw new Exception('Invalid quoted text: ' . implode('', $buff));
        }

        $buff = implode('', $buff);
        $buff = substr($buff, 1, strlen($buff) - 2);
        $buff = str_replace('\\' . $firstCh, $firstCh, $buff);

        return new Token('text', $buff);
    }

    /**
     * Scan incoming sign from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanSignToken (): TokenInterface
    {
        $buff = [];

        while (($ch = $this->read()) !== false) {
            if (!$this->isSignStartToken($ch)) {
                $this->unread();
                break;
            }
            $buff[] = $ch;
        }

        $buff = implode('', $buff);
        if (!$this->isSignOperator($buff)) {
            throw new Exception('Invalid sign: ' . $buff);
        }

        return new Token('sign', $buff);
    }

    /**
     * Scan incoming join from the input stream.
     *
     * @return TokenInterface
     * @throws Exception
     */
    private function scanJoinToken (): TokenInterface
    {
        $buff = [];

        while (($ch = $this->read()) !== false) {
            if (!$this->isJoinStartToken($ch)) {
                $this->unread();
                break;
            }
            $buff[] = $ch;
        }

        $buff = implode('', $buff);
        if (!$this->isJoinOperator($buff)) {
            throw new Exception('Invalid join: ' . $buff);
        }

        return new Token('join', $buff);
    }

    private function isWhitespaceToken (string $ch): bool
    {
        return $ch === ' ' || $ch === "\t" || $ch === "\n";
    }

    private function isLetterToken (string $ch): bool
    {
        return preg_match('/[a-zA-Z]/', $ch) === 1;
    }

    private function isDigitToken (string $ch): bool
    {
        return preg_match('/[0-9]/', $ch) === 1;
    }

    private function isIdentifierStartToken (string $ch): bool
    {
        return $this->isLetterToken($ch) || $ch === '_' || $ch === '@' || $ch === '#';
    }

    private function isTextStartToken (string $ch): bool
    {
        return $ch === '"' || $ch === "'";
    }

    private function isNumberStartToken (string $ch): bool
    {
        return $ch === '-' || $this->isDigitToken($ch);
    }

    private function isSignStartToken (string $ch): bool
    {
        return $ch === '=' || $ch === '?' || $ch === '!' || $ch === '>' || $ch === '<' || $ch === '~';
    }

    private function isJoinStartToken (string $ch): bool
    {
        return $ch === '&' || $ch === '|';
    }

    private function isGroupStartToken (string $ch): bool
    {
        return $ch === '(';
    }

    private function isIdentifier (string $text): bool
    {
        return preg_match('/^[@#_]?[\w.:]*\w+$/', $text) === 1;
    }

    private function isSignOperator (string $text): bool
    {
        return in_array($text, [
            '=', '!=', '~', '!~', '<', '<=', '>', '>=',
            '?=', '?!=', '?~', '?!~', '?<', '?<=', '?>', '?>=',
        ]);
    }

    private function isJoinOperator (string $text): bool
    {
        return $text === '&&' || $text === '||';
    }

    private function read (): string|false
    {
        return $this->characters[$this->position++] ?? false;
    }

    private function unread (): void
    {
        $this->position--;
    }
}
<?php

declare(strict_types=1);

namespace DaveKok\LALR1;

class Type
{
    public readonly string $key;
    public readonly string $name;
    public readonly int $precedence;

    public function __construct(
        string $name,
        int $precedence = 0
    ) {
        $this->name = $name;
        $this->precedence = $precedence;
    }

    public function setKey(int $number): void
    {
        $this->key = Key::numberToKey($number);
    }
}

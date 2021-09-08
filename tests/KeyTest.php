<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\Key;
use PHPUnit\Framework\TestCase;
use LogicException;

class KeyTest extends TestCase
{
    public function testNoNegativeNumbers(): void
    {
        $this->expectException(LogicException::class, "Negative numbers are not supported.");
        Key::numberToKey(-1);
    }

    public function testNotTooLarge(): void
    {
        $this->expectException(
            LogicException::class,
            "Numbers larger then 1,114,111 are not supported."
        );
        Key::numberToKey(1_114_112);
    }

    public function testKey(): void
    {
        $n = -1;
        for ($i = 0b00000000; $i <= 0b01111111; ++$i) {
            self::assertSame(chr($i), Key::numberToKey(++$n));
        }
        self::assertSame(0x007F, $n);
        for ($i = 0b11000010; $i <= 0b11011111; ++$i) {
            for ($j = 0b10000000; $j <= 0b10111111; ++$j) {
                self::assertSame(chr($i).chr($j), Key::numberToKey(++$n));
            }
        }
        self::assertSame(0x07FF, $n);
        $i = 0b11100000;
        for ($j = 0b10100000; $j <= 0b10111111; ++$j) {
            for ($k = 0b10000000; $k <= 0b10111111; ++$k) {
                self::assertSame(chr($i).chr($j).chr($k), Key::numberToKey(++$n));
            }
        }
        for ($i = 0b11100001; $i <= 0b11101111; ++$i) {
            for ($j = 0b10000000; $j <= 0b10111111; ++$j) {
                for ($k = 0b10000000; $k <= 0b10111111; ++$k) {
                    self::assertSame(chr($i).chr($j).chr($k), Key::numberToKey(++$n));
                }
            }
        }
        self::assertSame(0xFFFF, $n);
        $i = 0b11110000;
        for ($j = 0b10010000; $j <= 0b10111111; ++$j) {
            for ($k = 0b10000000; $k <= 0b10111111; ++$k) {
                for ($l = 0b10000000; $l <= 0b10111111; ++$l) {
                    self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::numberToKey(++$n));
                }
            }
        }
        for ($i = 0b11110001; $i <= 0b11110011; ++$i) {
            for ($j = 0b10000000; $j <= 0b10111111; ++$j) {
                for ($k = 0b10000000; $k <= 0b10111111; ++$k) {
                    for ($l = 0b10000000; $l <= 0b10111111; ++$l) {
                        self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::numberToKey(++$n));
                    }
                }
            }
        }
        $i = 0b11110100;
        for ($j = 0b10000000; $j <= 0b10001111; ++$j) {
            for ($k = 0b10000000; $k <= 0b10111111; ++$k) {
                for ($l = 0b10000000; $l <= 0b10111111; ++$l) {
                    self::assertSame(chr($i).chr($j).chr($k).chr($l), Key::numberToKey(++$n));
                }
            }
        }
        self::assertSame(0x10FFFF, $n);
    }
}

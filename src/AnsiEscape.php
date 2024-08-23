<?php

declare(strict_types=1);

namespace RobertWesner\AWDY;

use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * https://gist.github.com/fnky/458719343aabd01cfb17a3a4f7296797
 *
 */
#[CodeCoverageIgnore]
final class AnsiEscape
{
    private const SEQUENCE = "\33[";

    public static function clear(): string
    {
        return self::SEQUENCE . '2J';
    }

    public static function moveToBeginning(): string
    {
        return self::SEQUENCE . 'H';
    }

    public static function moveTo(int $x, int $y): string
    {
        return self::SEQUENCE . $y . ';' . $x . 'f';
    }

    public static function resetColor(): string
    {
        return self::SEQUENCE . '0m';
    }

    public static function fg(int $id): string
    {
        return self::SEQUENCE . '38;5;' . $id . 'm';
    }

    public static function bg(int $id): string
    {
        return self::SEQUENCE . '48;5;' . $id . 'm';
    }
}

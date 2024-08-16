<?php

namespace RobertWesner\AWDY;

/**
 * https://gist.github.com/fnky/458719343aabd01cfb17a3a4f7296797
 */
final class AnsiEscape
{
    private const SEQUENCE = "\33[";

    public static function moveToBeginning(): string
    {
        return self::SEQUENCE . 'H';
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
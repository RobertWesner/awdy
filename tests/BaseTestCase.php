<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Tests;

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function stripAnsiEscapes(string $value): string
    {
        return preg_replace('/\\033\[[^A-Za-z]*[A-Za-z]/', '', $value);
    }

    protected function renderAwdyOutput(string $value): string
    {
        $segments = preg_split('/(?=\\x1b\[\d+;\d+f)/', $value);

        $result = '';
        foreach ($segments as &$segment) {
            if (
                str_starts_with($segment, "\033")
                && preg_match(
                    '/\\x1b\[(?<y>\d+);(?<x>\d+)f(?<text>.*)/',
                    $segment,
                    $matches,
                )
            ) {
                $segment = [
                    'x' => (int)$matches['x'] - 1,
                    'y' => (int)$matches['y'] - 1,
                    'text' => $this->stripAnsiEscapes($matches['text']),
                ];
            } else {
                $segment = [
                    'x' => 0,
                    'y' => 0,
                    'text' => $this->stripAnsiEscapes($segment),
                ];
            }
        }

        foreach ($segments as ['x' => $x, 'y' => $y, 'text' => $text]) {
            $explodedResult = explode(PHP_EOL, $result);

            foreach (explode(PHP_EOL, $text) as $i => $line) {
                $explodedResult[$y + $i] = substr_replace(
                    $explodedResult[$y + $i] ?? str_repeat(' ', $x),
                    $line,
                    $x,
                    strlen($line),
                );
            }

            $result = implode(PHP_EOL, $explodedResult);
        }

        return $result;
    }
}

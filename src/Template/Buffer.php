<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template;

use RobertWesner\AWDY\AnsiEscape;

final class Buffer
{
    use AbsoluteCoordinateTrait;

    private string $buffer;
    private array $ansiEscapes = [];

    public function __construct(
        private readonly int $width,
        private readonly int $height,
    ) {
        $this->buffer = substr(str_repeat(str_repeat(' ', $width) . PHP_EOL, $height), 0, -1);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    private function drawMultiline(
        int $x,
        int $y,
        string $text,
        ?string $ansiEscape = null,
        ?string $transparency = null,
    ): void {
        foreach (explode(PHP_EOL, $text) as $i => $line) {
            $this->draw($x, $y + $i, $line, $ansiEscape, $transparency);
        }
    }

    /**
     * Draw a string of characters to the Buffer.
     */
    public function draw(int $x, int $y, string $text, ?string $ansiEscape = null, ?string $transparency = null): void
    {
        // TODO: clip overflow! right now it breaks everything

        $x = $this->absoluteCoordinate($x, $this->width);
        $y = $this->absoluteCoordinate($y, $this->height);

        if (strpos($text, PHP_EOL)) {
            $this->drawMultiline($x, $y, $text, $ansiEscape, $transparency);

            return;
        }

        if (!isset($this->ansiEscapes[$y])) {
            $this->ansiEscapes[$y] = [];
        }

        if ($transparency === null) {
            // Simple and efficient. Just replacing Text.
            if ($ansiEscape !== null) {
                $this->ansiEscapes[$y][$x] = AnsiEscape::resetColor() . $ansiEscape;
                $this->ansiEscapes[$y][$x + strlen($text)] = AnsiEscape::resetColor();
            }

            $this->buffer = substr_replace(
                $this->buffer,
                $text,
                $y * ($this->width + 1) + $x,
                strlen($text),
            );
        } else {
            // Character by character, excluding the transparency
            $i = 0;
            foreach (str_split($text) as $character) {
                if ($character === $transparency) {
                    $i++;

                    continue;
                }

                if ($ansiEscape !== null) {
                    $this->ansiEscapes[$y][$x + $i] = AnsiEscape::resetColor() . $ansiEscape;
                }

                $this->buffer = substr_replace(
                    $this->buffer,
                    $character,
                    $y * ($this->width + 1) + $x + $i,
                    1,
                );

                $i++;
            }

            $xPos = $x + $i + 1;
            $this->ansiEscapes[$y][$xPos] = AnsiEscape::resetColor() . ($this->ansiEscapes[$y][$xPos] ?? '');
        }
    }

    public function debug(): void
    {
        $this->buffer = str_replace(' ', '#', $this->buffer);
    }

    public function __toString()
    {
        $buffer = $this->buffer;
        $escapes = $this->ansiEscapes;

        krsort($escapes);

        foreach ($escapes as $y => $escapeLine) {
            krsort($escapeLine);

            foreach ($escapeLine as $x => $escape) {
                $buffer = substr_replace($buffer, $escape, $y * ($this->width + 1) + $x, 0);
            }
        }

        return $buffer;
    }
}

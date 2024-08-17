<?php

namespace RobertWesner\AWDY\Template;

use RobertWesner\AWDY\AnsiEscape;

final class Buffer
{
    use AbsoluteCoordinateTrait;

    private string $buffer;
    private array $ansiEscapes = [];

    public function __construct(
        private int $width,
        private int $height,
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

    public function draw(int $x, int $y, string $text, ?string $ansiEscape = null): void
    {
        if (!isset($this->ansiEscapes[$y])) {
            $this->ansiEscapes[$y] = [];
        }
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

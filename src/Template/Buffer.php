<?php

namespace RobertWesner\AWDY\Template;

final class Buffer
{
    use AbsoluteCoordinateTrait;

    // TODO: $buffer as 2d array with each character being ['someansiescapesequence', 'X']
    private string $buffer;

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

    public function draw(int $x, int $y, string $text): void
    {
        $this->buffer = substr_replace(
            $this->buffer,
            $text,
            $y * ($this->width + 1) + $x,
            strlen($text),
        );
    }

    public function __toString()
    {
        return $this->buffer;
    }
}

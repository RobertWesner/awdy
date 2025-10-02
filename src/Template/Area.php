<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template;

use RobertWesner\AWDY\AnsiEscape;

class Area
{
    use AbsoluteCoordinateTrait;

    /**
     * @param callable|callable-string|array $onRender
     */
    public static function create(int $x1, int $y1, int $x2, int $y2, callable|string|array $onRender): static
    {
        return new static($x1, $y1, $x2, $y2, $onRender);
    }

    private bool $dirty = true;

    private function __construct(
        private readonly int $x1,
        private readonly int $y1,
        private readonly int $x2,
        private readonly int $y2,
        private $onRender,
    ) {
    }

    public function render(int $screenWidth, int $screenHeight): void
    {
        if (!$this->dirty) {
            return;
        }

        $this->dirty = false;

        $width = $this->absoluteCoordinate($this->x2, $screenWidth)
            - $this->absoluteCoordinate($this->x1, $screenWidth) + 1;
        $height = $this->absoluteCoordinate($this->y2, $screenHeight)
            - $this->absoluteCoordinate($this->y1, $screenHeight) + 1;

        $buffer = new Buffer($width, $height);
        ($this->onRender)($buffer);

        $y = $this->y1;
        if ($y < 0) {
            $y += $screenHeight;
        }

        $x = $this->x1;
        if ($x < 0) {
            $x += $screenWidth;
        }

        foreach (explode(PHP_EOL, (string)$buffer) as $line) {
            echo AnsiEscape::moveTo($x, $y), $line;

            $y++;
        }
    }

    /**
     * Call if Area needs to be re-rendered.
     */
    public function dirty(): void
    {
        $this->dirty = true;
    }
}

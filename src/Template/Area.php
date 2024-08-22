<?php

namespace RobertWesner\AWDY\Template;

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

    private Buffer $buffer;

    private bool $dirty = true;

    private function __construct(
        private int $x1,
        private int $y1,
        private int $x2,
        private int $y2,
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

        // TODO: huh?
        if ($height === 0) {
            $height = 1;
        }

        $buffer = new Buffer($width, $height);
        ($this->onRender)($buffer);

        $y = $this->y1 + 1;
        if ($y < 0) {
            $y = $screenHeight + $y;
        }
        $x = $this->x1 + 1;

        foreach (explode(PHP_EOL, $buffer) as $line) {
            // TODO: use AnsiEscape
            echo "\033[{$y};{$x}f", $line;

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

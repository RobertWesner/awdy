<?php

namespace RobertWesner\AWDY\Template;

class Connection
{
    public const TYPE_HORIZONTAL = 'horizontal';
    public const TYPE_VERTICAL = 'vertical';

    public int $beginX;
    public int $beginY;
    public string $beginFacing;
    public int $endX;
    public int $endY;
    public string $endFacing;

    private function __construct(
        public string $type,
    ) {
    }

    public static function horizontal(): static
    {
        return new static(static::TYPE_HORIZONTAL);
    }

    public static function vertical(): static
    {
        return new static(static::TYPE_VERTICAL);
    }

    public function begin(int $x, int $y, string $facing): static
    {
        $this->beginX = $x;
        $this->beginY = $y;
        $this->beginFacing = $facing;

        return $this;
    }

    public function end(int $x, int $y, string $facing): static
    {
        $this->endX = $x;
        $this->endY = $y;
        $this->endFacing = $facing;

        return $this;
    }
}

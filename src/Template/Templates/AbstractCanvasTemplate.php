<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\TemplateInterface;

abstract class AbstractCanvasTemplate implements TemplateInterface
{
    protected Area $canvas;

    protected float $progress = 0;

    /**
     * return [$x1, $y1, $x2, $y2];
     *
     * @return array<int, int, int, int>
     */
    abstract protected function getCanvasDimensions(): array;

    abstract public function renderCanvas(Buffer $buffer): void;

    public function __construct()
    {
        [$x1, $y1, $x2, $y2] = $this->getCanvasDimensions();

        $this->canvas = Area::create($x1, $y1, $x2, $y2, [$this, 'renderCanvas']);
    }

    public function getAreas(): array
    {
        return [
            $this->canvas,
        ];
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->canvas->dirty();
    }
}

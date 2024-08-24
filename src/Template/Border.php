<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template;

// TODO: allow ansi escapes for border parts

class Border
{
    use AbsoluteCoordinateTrait;

    private string $horizontal = ' ';
    private string $vertical = ' ';

    private string $cornerTopLeft = ' ';
    private string $cornerTopRight = ' ';
    private string $cornerBottomLeft = ' ';
    private string $cornerBottomRight = ' ';

    private string $connectFacingLeft = ' ';
    private string $connectFacingRight = ' ';
    private string $connectFacingTop = ' ';
    private string $connectFacingBottom = ' ';
    private string $connectFacingAll = ' ';

    /**
     * @var Connection[]
     */
    private array $connections = [];

    public static function create(): static
    {
        return new static();
    }

    private function __construct()
    {
    }

    private function mapFacing(string $facing): string
    {
        return match ($facing) {
            Facing::RIGHT => $this->connectFacingRight,
            Facing::LEFT => $this->connectFacingLeft,
            Facing::TOP => $this->connectFacingTop,
            Facing::BOTTOM => $this->connectFacingBottom,
            default => $this->connectFacingAll,
        };
    }

    private function drawConnectionNode(
        int $x,
        int $y,
        string $connect,
        Buffer $buffer,
        int $bufferWidth,
        int $bufferHeight,
    ): void {
        $x = $this->absoluteCoordinate($x, $bufferWidth);
        $y = $this->absoluteCoordinate($y, $bufferHeight);

        foreach (explode(PHP_EOL, $connect) as $i => $line) {
            $buffer->draw($x, $y + $i, $line);
        }
    }

    private function drawHorizontal(int $x, int $y, int $width, Buffer $buffer): void
    {
        $horizontalLines = explode(PHP_EOL, $this->horizontal);
        $horizontalLinesWidth = strlen($horizontalLines[0]);
        for ($i = 0; $i < $width; $i += $horizontalLinesWidth) {
            $trimTo = null;
            if ($i + $horizontalLinesWidth > $width) {
                $trimTo = $width - $i - $horizontalLinesWidth;
            }

            foreach ($horizontalLines as $lineY => $line) {
                if ($trimTo !== null) {
                    $line = substr($line, 0, $trimTo);
                }

                $buffer->draw($i + $x, $y + $lineY, $line);
            }
        }
    }

    private function drawVertical(int $x, int $y, int $height, Buffer $buffer): void
    {
        $verticalLines = explode(PHP_EOL, $this->vertical);
        $verticalLinesCount = count($verticalLines);
        for ($i = 0; $i < $height; $i++) {
            $line = $verticalLines[$i % $verticalLinesCount];

            $buffer->draw($x, $i + $y, $line);
        }
    }

    private function getFirstLineWidth(string $string): int
    {
        return strlen(explode(PHP_EOL, $string, 2)[0]);
    }

    public function horizontal(string $horizontal): static
    {
        $this->horizontal = $horizontal;

        return $this;
    }

    public function vertical(string $vertical): static
    {
        $this->vertical = $vertical;

        return $this;
    }

    public function corners(string $topLeft, string $topRight, string $bottomLeft, string $bottomRight): static
    {
        $this->cornerTopLeft = $topLeft;
        $this->cornerTopRight = $topRight;
        $this->cornerBottomLeft = $bottomLeft;
        $this->cornerBottomRight = $bottomRight;

        return $this;
    }

    public function connectFacing(string $left, string $right, string $top, string $bottom, string $all): static
    {
        $this->connectFacingLeft = $left;
        $this->connectFacingRight = $right;
        $this->connectFacingTop = $top;
        $this->connectFacingBottom = $bottom;
        $this->connectFacingAll = $all;

        return $this;
    }

    /**
     * @param Connection[] $connections
     *
     * @codeCoverageIgnore
     */
    public function connections(array $connections): static
    {
        $this->connections = $connections;

        return $this;
    }

    public function getBuffer(int $width, int $height): Buffer
    {
        $buffer = new Buffer($width, $height);

        $lines = explode(PHP_EOL, $this->cornerTopLeft);
        $cornerHeightTop = count($lines);
        $cornerWidth = strlen($lines[0]);
        foreach ($lines as $i => $line) {
            $buffer->draw(0, $i, $line);
        }

        foreach (explode(PHP_EOL, $this->cornerTopRight) as $i => $line) {
            $buffer->draw($width - strlen($line), $i, $line);
        }

        $lines = explode(PHP_EOL, $this->cornerBottomLeft);
        $linesCount = count($lines);
        foreach ($lines as $i => $line) {
            $buffer->draw(0, $height - $linesCount + $i, $line);
        }

        $lines = explode(PHP_EOL, $this->cornerBottomRight);
        $cornerHeightBottom = $linesCount = count($lines);
        foreach ($lines as $i => $line) {
            $buffer->draw($width - strlen($line), $height - $linesCount + $i, $line);
        }

        // Left bar
        $this->drawVertical(
            0,
            $cornerHeightTop,
            $height - $cornerHeightTop - $cornerHeightBottom,
            $buffer,
        );

        // Right bar
        $this->drawVertical(
            $width - $this->getFirstLineWidth($this->vertical),
            $cornerHeightTop,
            $height - $cornerHeightTop - $cornerHeightBottom,
            $buffer,
        );

        // Top bar
        $this->drawHorizontal($cornerWidth, 0, $width - $cornerWidth * 2, $buffer);

        // Bottom bar
        $this->drawHorizontal(
            $cornerWidth,
            $height - substr_count($this->horizontal, PHP_EOL) - 1,
            $width - $cornerWidth * 2,
            $buffer,
        );

        foreach ($this->connections as $connection) {
            $beginX = $this->absoluteCoordinate($connection->beginX, $width);
            $beginY = $this->absoluteCoordinate($connection->beginY, $height);
            $beginConnection = $this->mapFacing($connection->beginFacing);
            $endX = $this->absoluteCoordinate($connection->endX, $width);
            $endY = $this->absoluteCoordinate($connection->endY, $height);
            $endConnection = $this->mapFacing($connection->endFacing);

            $this->drawConnectionNode(
                $beginX,
                $beginY,
                $beginConnection,
                $buffer,
                $width,
                $height,
            );

            $this->drawConnectionNode(
                $endX,
                $endY,
                $endConnection,
                $buffer,
                $width,
                $height,
            );

            if ($connection->type === Connection::TYPE_HORIZONTAL) {
                $firstLineWidth = $this->getFirstLineWidth($beginConnection);
                $this->drawHorizontal(
                    $beginX + $firstLineWidth,
                    $beginY,
                    $endX - $beginX - $firstLineWidth,
                    $buffer,
                );
            } elseif ($connection->type === Connection::TYPE_VERTICAL) {
                $this->drawVertical(
                    $beginX,
                    $beginY + substr_count($beginConnection, PHP_EOL) + 1,
                    $endY - $beginY - substr_count($beginConnection, PHP_EOL) - 1,
                    $buffer,
                );
            }
        }

        return $buffer;
    }
}
